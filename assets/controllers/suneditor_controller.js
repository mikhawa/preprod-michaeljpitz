import { Controller } from '@hotwired/stimulus';
import suneditor from 'suneditor';
import plugins from 'suneditor/src/plugins';
import 'suneditor/dist/css/suneditor.min.css';

export default class extends Controller {
    connect() {
        const textarea = this.element.querySelector('textarea');
        if (!textarea) {
            return;
        }

        // Sauvegarder le name et créer un input hidden fiable pour la soumission
        // SunEditor peut modifier/détacher le textarea original
        const fieldName = textarea.name;
        this._hiddenInput = document.createElement('input');
        this._hiddenInput.type = 'hidden';
        this._hiddenInput.name = fieldName;
        this._hiddenInput.value = textarea.value;
        this.element.appendChild(this._hiddenInput);

        // Retirer name et required du textarea original :
        // - name : évite un doublon dans le FormData (le hidden input le porte)
        // - required : évite que le navigateur bloque le submit sur un champ caché
        textarea.removeAttribute('name');
        textarea.removeAttribute('required');

        this.editor = suneditor.create(textarea, {
            plugins: plugins,
            lang: this._frenchLang(),
            height: '400px',
            // font et fontSize retirés : ils génèrent des spans inline qui
            // écrasent les styles CSS du frontend (h1/h2/h3 perdent leur taille)
            buttonList: [
                ['undo', 'redo'],
                ['formatBlock'],
                ['bold', 'underline', 'italic', 'strike', 'subscript', 'superscript'],
                ['removeFormat'],
                ['fontColor', 'hiliteColor'],
                ['outdent', 'indent'],
                ['align', 'horizontalRule', 'list', 'table'],
                ['link', 'image', 'video'],
                ['fullScreen', 'showBlocks', 'codeView'],
            ],
            defaultStyle: 'font-family: Arial, sans-serif; font-size: 16px;',
            imageAccept: '.jpeg,.jpg,.png,.webp,.gif',
        });

        // Bouton PHP injecté directement dans la toolbar (sans passer par l'API plugin)
        this._injectPhpButton();

        // Accessibilité : attributs sur la zone éditable
        const editable = this.element.querySelector('.se-wrapper-wysiwyg');
        if (editable) {
            editable.setAttribute('aria-label', 'Contenu');
            editable.setAttribute('role', 'textbox');
            editable.setAttribute('aria-multiline', 'true');
        }

        // Upload d'image personnalisé via notre endpoint
        this.editor.onImageUploadBefore = (files, info, core, uploadHandler) => {
            const formData = new FormData();
            formData.append('file', files[0]);

            fetch('/admin/editor/upload', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            })
                .then((response) => {
                    if (!response.ok) {
                        return response.json().then((data) => {
                            throw new Error(data.error || "Erreur lors de l'upload.");
                        });
                    }
                    return response.json();
                })
                .then((data) => {
                    const response = {
                        result: [
                            {
                                url: data.url,
                                name: data.filename || files[0].name,
                                size: files[0].size,
                            },
                        ],
                    };
                    uploadHandler(response);
                })
                .catch((error) => {
                    console.error("Erreur upload image :", error.message);
                    uploadHandler("Erreur lors de l'upload : " + error.message);
                });

            return undefined;
        };

        // Synchroniser à chaque modification dans l'éditeur
        this.editor.onChange = () => {
            this._syncToHiddenInput();
        };

        // Synchroniser aussi après insertion d'image (onChange ne se déclenche pas toujours)
        this.editor.onImageUpload = () => {
            this._syncToHiddenInput();
        };

        // Synchroniser avant la soumission du formulaire (filet de sécurité)
        this._form = this.element.closest('form');
        this._handleSubmit = () => {
            this._syncToHiddenInput();
        };
        if (this._form) {
            this._form.addEventListener('submit', this._handleSubmit);
        }
    }

    disconnect() {
        if (this._form && this._handleSubmit) {
            this._form.removeEventListener('submit', this._handleSubmit);
        }
        if (this.editor) {
            this.editor.destroy();
            this.editor = null;
        }
        if (this._hiddenInput) {
            this._hiddenInput.remove();
        }
    }

    /**
     * Écrit le contenu HTML de l'éditeur dans le hidden input
     * qui porte le name="" attendu par Symfony.
     */
    _syncToHiddenInput() {
        if (!this.editor || !this._hiddenInput) {
            return;
        }
        this._hiddenInput.value = this.editor.getContents(false);
    }

    /**
     * Injecte un bouton "PHP" dans la toolbar Suneditor après sa création.
     * Bypasse l'API plugin interne pour utiliser l'API publique editor.insertHTML.
     */
    _injectPhpButton() {
        // Trouver le conteneur réel des groupes de boutons (parent du premier groupe)
        const firstGroup = this.element.querySelector('.se-btn-module');
        if (!firstGroup) return;
        const toolbar = firstGroup.parentNode;

        const group = document.createElement('div');
        group.className = 'se-btn-module se-btn-module-border';

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.title = 'Insérer un bloc PHP exécutable';
        btn.className = 'se-btn';
        btn.setAttribute('aria-label', 'Insérer un bloc PHP exécutable');
        btn.innerHTML =
            '<b style="font-family:monospace;font-size:11px;line-height:1.2">PHP</b>';

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this._openPhpDialog((code) => {
                const encoded = code
                    .split('\n')
                    .map((line) =>
                        line
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;')
                    )
                    .join('<br>');
                // API publique Suneditor — insère au curseur ou en fin de contenu
                this.editor.insertHTML(`<p>[php]${encoded}[/php]</p>`);
                this._syncToHiddenInput();
            });
        });

        group.appendChild(btn);
        toolbar.appendChild(group);
    }

    /**
     * Ouvre une modale native <dialog> avec un textarea pour saisir du code PHP.
     * Tab insère 4 espaces (pas de HTML, pas de perte d'indentation).
     */
    _openPhpDialog(onSave) {
        // Injecter le style ::backdrop une seule fois
        if (!document.querySelector('#php-dialog-style')) {
            const style = document.createElement('style');
            style.id = 'php-dialog-style';
            style.textContent =
                '.php-code-dialog::backdrop{background:rgba(0,0,0,.55)}' +
                '.php-code-dialog textarea{tab-size:4;-moz-tab-size:4}';
            document.head.appendChild(style);
        }

        const dialog = document.createElement('dialog');
        dialog.className =
            'php-code-dialog rounded-lg p-0 shadow-2xl border w-full max-w-2xl';
        dialog.style.cssText =
            'background:var(--bg-primary);color:var(--text-primary);border-color:var(--border)';

        dialog.innerHTML = `
            <form method="dialog" class="flex flex-col gap-0">
                <div class="px-5 py-4 border-b" style="border-color:var(--border)">
                    <h3 class="text-base font-semibold">Bloc PHP exécutable</h3>
                    <p class="mt-1 text-sm" style="color:var(--text-secondary)">
                        Écrivez le code sans <code>&lt;?php</code> — utilisez
                        <kbd class="rounded px-1 py-0.5 text-xs font-mono border"
                            style="background:var(--bg-secondary);border-color:var(--border)">Tab</kbd>
                        pour indenter (4 espaces).
                    </p>
                </div>
                <div class="p-4">
                    <textarea
                        name="code"
                        rows="14"
                        spellcheck="false"
                        autocomplete="off"
                        placeholder="echo PHP_VERSION;"
                        class="w-full rounded border p-3 font-mono text-sm resize-y focus:outline-none focus:ring-2"
                        style="background:var(--bg-secondary);color:var(--text-primary);border-color:var(--border);focus-ring-color:var(--accent)"
                    ></textarea>
                </div>
                <div class="flex justify-end gap-3 px-5 py-3 border-t" style="border-color:var(--border)">
                    <button
                        type="button"
                        data-action="cancel"
                        class="rounded px-4 py-1.5 text-sm border hover:opacity-80 transition-opacity"
                        style="border-color:var(--border);color:var(--text-secondary)"
                    >Annuler</button>
                    <button
                        type="submit"
                        class="rounded px-4 py-1.5 text-sm font-medium text-white transition-colors hover:opacity-90"
                        style="background:var(--accent)"
                    >Insérer</button>
                </div>
            </form>
        `;

        document.body.appendChild(dialog);

        const codeTextarea = dialog.querySelector('textarea');

        // Tab = 4 espaces (pas de sortie de champ)
        codeTextarea.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                e.preventDefault();
                const s = e.target.selectionStart;
                const end = e.target.selectionEnd;
                e.target.value =
                    e.target.value.slice(0, s) + '    ' + e.target.value.slice(end);
                e.target.selectionStart = e.target.selectionEnd = s + 4;
            }
        });

        dialog.querySelector('[data-action="cancel"]').addEventListener('click', () => {
            dialog.close();
            dialog.remove();
        });

        dialog.querySelector('form').addEventListener('submit', (e) => {
            e.preventDefault();
            const code = codeTextarea.value;
            if (code.trim()) {
                onSave(code);
            }
            dialog.close();
            dialog.remove();
        });

        dialog.addEventListener('close', () => dialog.remove());

        dialog.showModal();
        codeTextarea.focus();
    }

    _frenchLang() {
        return {
            code: 'fr',
            toolbar: {
                default: 'Par défaut',
                save: 'Sauvegarder',
                font: 'Police',
                formats: 'Format',
                fontSize: 'Taille',
                bold: 'Gras',
                underline: 'Souligné',
                italic: 'Italique',
                strike: 'Barré',
                subscript: 'Indice',
                superscript: 'Exposant',
                removeFormat: 'Effacer le format',
                fontColor: 'Couleur du texte',
                hiliteColor: 'Couleur de fond',
                indent: 'Augmenter le retrait',
                outdent: 'Diminuer le retrait',
                align: 'Alignement',
                alignLeft: 'Aligner à gauche',
                alignRight: 'Aligner à droite',
                alignCenter: 'Centrer',
                alignJustify: 'Justifier',
                list: 'Liste',
                orderList: 'Liste ordonnée',
                unorderList: 'Liste non ordonnée',
                horizontalRule: 'Ligne horizontale',
                hr_solid: 'Solide',
                hr_dotted: 'Points',
                hr_dashed: 'Tirets',
                table: 'Tableau',
                link: 'Lien',
                math: 'Math',
                image: 'Image',
                video: 'Vidéo',
                audio: 'Audio',
                fullScreen: 'Plein écran',
                showBlocks: 'Afficher les blocs',
                codeView: 'Code source',
                undo: 'Annuler',
                redo: 'Rétablir',
                preview: 'Aperçu',
                print: 'Imprimer',
                tag_p: 'Paragraphe',
                tag_div: 'Normal (DIV)',
                tag_h: 'En-tête',
                tag_blockquote: 'Citation',
                tag_pre: 'Code',
                template: 'Modèle',
                lineHeight: 'Hauteur de ligne',
                paragraphStyle: 'Style de paragraphe',
                textStyle: 'Style de texte',
                imageGallery: 'Galerie',
                dir_ltr: 'Gauche à droite',
                dir_rtl: 'Droite à gauche',
                mention: 'Mention',
            },
            dialogBox: {
                linkBox: {
                    title: 'Insérer un lien',
                    url: 'Adresse URL',
                    text: 'Texte à afficher',
                    newWindowCheck: 'Ouvrir dans une nouvelle fenêtre',
                    downloadLinkCheck: 'Lien de téléchargement',
                },
                imageBox: {
                    title: 'Insérer une image',
                    file: 'Sélectionner un fichier',
                    url: 'Adresse URL de l\'image',
                    altText: 'Texte alternatif',
                },
                videoBox: {
                    title: 'Insérer une vidéo',
                    file: 'Sélectionner un fichier',
                    url: 'URL d\'intégration (YouTube, etc.)',
                },
                audioBox: {
                    title: 'Insérer un audio',
                    file: 'Sélectionner un fichier',
                    url: 'Adresse URL de l\'audio',
                },
                browser: {
                    tags: 'Tags',
                    search: 'Rechercher',
                },
                caption: 'Légende',
                close: 'Fermer',
                submitButton: 'Valider',
                revertButton: 'Annuler',
                proportion: 'Proportions',
                basic: 'Basique',
                left: 'Gauche',
                right: 'Droite',
                center: 'Centré',
                width: 'Largeur',
                height: 'Hauteur',
                size: 'Taille',
                ratio: 'Ratio',
            },
            controller: {
                edit: 'Modifier',
                unlink: 'Supprimer le lien',
                remove: 'Supprimer',
                insertRowAbove: 'Insérer une ligne au-dessus',
                insertRowBelow: 'Insérer une ligne en dessous',
                deleteRow: 'Supprimer la ligne',
                insertColumnBefore: 'Insérer une colonne avant',
                insertColumnAfter: 'Insérer une colonne après',
                deleteColumn: 'Supprimer la colonne',
                fixedColumnWidth: 'Largeur de colonne fixe',
                resize100: '100%',
                resize75: '75%',
                resize50: '50%',
                resize25: '25%',
                autoSize: 'Taille auto',
                mirrorHorizontal: 'Miroir horizontal',
                mirrorVertical: 'Miroir vertical',
                rotateLeft: 'Rotation gauche',
                rotateRight: 'Rotation droite',
                maxSize: 'Taille max',
                minSize: 'Taille min',
                tableHeader: 'En-tête du tableau',
                mergeCells: 'Fusionner les cellules',
                splitCells: 'Diviser les cellules',
                HorizontalSplit: 'Division horizontale',
                VerticalSplit: 'Division verticale',
            },
            menu: {
                spaced: 'Espacement',
                bordered: 'Bordure',
                neon: 'Néon',
                translucent: 'Translucide',
                shadow: 'Ombre',
            },
        };
    }
}
