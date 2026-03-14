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
