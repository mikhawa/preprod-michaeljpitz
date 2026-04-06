import { Controller } from '@hotwired/stimulus';

/**
 * Contrôleur Stimulus : transforme les blocs <pre class="php-runner"> en
 * widgets interactifs d'exécution PHP via php-wasm (WebAssembly, CDN jsDelivr).
 *
 * L'auteur insère dans Suneditor (vue Code source) :
 *
 *   <pre class="php-runner"><?php echo PHP_VERSION; ?></pre>
 *
 * Le contrôleur détecte ces blocs à la connexion, crée l'UI (textarea +
 * bouton + zone de sortie) et charge php-wasm à la demande (premier clic).
 *
 * Attacher sur le div contenant le contenu de l'article :
 *   data-controller="external-link lightbox php-runner"
 */
export default class extends Controller {
    connect() {
        const blocs = this.element.querySelectorAll('pre.php-runner');
        blocs.forEach((pre) => this._transformer(pre));
    }

    // ── Transformation du <pre> en widget ────────────────────────────────────

    _transformer(pre) {
        const codeInitial = pre.textContent.trim();

        // Wrapper principal
        const wrapper = document.createElement('div');
        wrapper.className =
            'php-runner-widget my-6 rounded-lg border border-[var(--border)] bg-[var(--bg-secondary)] overflow-hidden';

        // Textarea (code éditable)
        const textarea = document.createElement('textarea');
        textarea.value = codeInitial;
        textarea.rows = Math.max(3, codeInitial.split('\n').length + 1);
        textarea.spellcheck = false;
        textarea.className =
            'w-full resize-y bg-[var(--bg-primary)] p-4 font-mono text-sm text-[var(--text-primary)] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[var(--accent)] border-b border-[var(--border)]';
        textarea.setAttribute('aria-label', 'Code PHP à exécuter');

        // Barre de contrôles
        const barre = document.createElement('div');
        barre.className = 'flex items-center gap-3 px-4 py-2 border-b border-[var(--border)]';

        const bouton = document.createElement('button');
        bouton.type = 'button';
        bouton.textContent = 'Exécuter';
        bouton.className =
            'rounded bg-[var(--accent)] px-4 py-1.5 text-sm font-medium text-white hover:bg-[var(--accent-hover)] transition-colors disabled:opacity-50';

        const statut = document.createElement('span');
        statut.className = 'text-sm text-[var(--text-secondary)] italic';

        barre.appendChild(bouton);
        barre.appendChild(statut);

        // Zone de sortie
        const sortie = document.createElement('pre');
        sortie.className =
            'hidden m-0 whitespace-pre-wrap p-4 font-mono text-sm text-[var(--text-primary)] bg-[var(--bg-primary)]';
        sortie.setAttribute('aria-live', 'polite');

        wrapper.appendChild(textarea);
        wrapper.appendChild(barre);
        wrapper.appendChild(sortie);

        // Remplacer le <pre> d'origine par le widget
        pre.replaceWith(wrapper);

        // État local à ce bloc (php-wasm chargé une seule fois par bloc)
        let phpInstance = null;
        let enCours = false;

        bouton.addEventListener('click', async () => {
            if (enCours) {
                return;
            }
            enCours = true;
            bouton.disabled = true;
            bouton.textContent = 'Exécution…';
            sortie.classList.remove('hidden', 'text-red-500');
            sortie.textContent = '';

            try {
                if (!phpInstance) {
                    statut.textContent = 'Chargement de PHP (~16 Mo, mis en cache)…';
                    phpInstance = await this._chargerPhp();
                }
                statut.textContent = 'Exécution…';
                const { stdout, stderr } = await this._executer(phpInstance, textarea.value);
                sortie.textContent = stdout || stderr || '(aucune sortie)';
                if (stderr && !stdout) {
                    sortie.classList.add('text-red-500');
                }
            } catch (err) {
                sortie.textContent = 'Erreur : ' + err.message;
                sortie.classList.add('text-red-500');
                phpInstance = null; // réinitialiser si échec du chargement
            } finally {
                statut.textContent = '';
                bouton.disabled = false;
                bouton.textContent = 'Exécuter';
                enCours = false;
            }
        });
    }

    // ── Chargement de php-wasm ────────────────────────────────────────────────

    async _chargerPhp() {
        const { PhpWeb } = await import(
            'https://cdn.jsdelivr.net/npm/php-wasm/PhpWeb.mjs'
        );

        const php = new PhpWeb();

        await new Promise((resolve, reject) => {
            php.addEventListener('ready', resolve, { once: true });
            php.addEventListener('error', (e) => {
                reject(new Error(e.detail ?? 'Impossible de charger PHP-WASM.'));
            }, { once: true });
        });

        return php;
    }

    // ── Exécution du code PHP ─────────────────────────────────────────────────

    async _executer(php, code) {
        let stdout = '';
        let stderr = '';

        const onOutput = ({ detail }) => {
            stdout += Array.isArray(detail) ? detail.join('') : String(detail);
        };
        const onError = ({ detail }) => {
            stderr += Array.isArray(detail) ? detail.join('') : String(detail);
        };

        php.addEventListener('output', onOutput);
        php.addEventListener('error', onError);

        try {
            await php.run(code);
        } finally {
            php.removeEventListener('output', onOutput);
            php.removeEventListener('error', onError);
        }

        return { stdout, stderr };
    }
}
