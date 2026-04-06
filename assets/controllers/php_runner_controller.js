import { Controller } from '@hotwired/stimulus';

/**
 * Contrôleur Stimulus : widget d'exécution PHP via php-wasm (WebAssembly).
 *
 * Activé automatiquement sur les <div> générés par le filtre Twig `php_runner`
 * (voir src/Twig/Extension/PhpRunnerExtension.php).
 *
 * L'auteur écrit dans Suneditor :  [php]echo PHP_VERSION;[/php]
 * Le filtre génère :  <div data-controller="php-runner" data-php-runner-code-value="echo PHP_VERSION;"></div>
 * Ce contrôleur crée alors l'UI interactive (textarea, bouton, sortie).
 */
export default class extends Controller {
    static values = {
        code: String,
    };

    connect() {
        this._buildWidget(this.codeValue || '');
    }

    disconnect() {
        this._php = null;
    }

    // ── Construction du widget ────────────────────────────────────────────────

    _buildWidget(codeInitial) {
        // Afficher <?php si absent, pour que le visiteur voie du code complet
        const codeAffiche = /^\s*<\?/.test(codeInitial)
            ? codeInitial
            : `<?php\n${codeInitial}`;

        this.element.innerHTML = '';
        this.element.className =
            'php-runner-widget my-6 rounded-lg border border-[var(--border)] bg-[var(--bg-secondary)] overflow-hidden not-prose';

        // Textarea (code éditable)
        const textarea = document.createElement('textarea');
        textarea.value = codeAffiche;
        textarea.rows = Math.max(3, codeAffiche.split('\n').length + 1);
        textarea.spellcheck = false;
        textarea.setAttribute('aria-label', 'Code PHP à exécuter');
        textarea.className =
            'w-full resize-y bg-[var(--bg-primary)] p-4 font-mono text-sm text-[var(--text-primary)] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[var(--accent)] border-b border-[var(--border)]';

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

        this.element.appendChild(textarea);
        this.element.appendChild(barre);
        this.element.appendChild(sortie);

        // Instance php-wasm locale à ce widget
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
                    statut.textContent = 'Chargement de PHP (~16 Mo, mis en cache ensuite)…';
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
                phpInstance = null;
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
                reject(new Error(String(e.detail ?? 'Impossible de charger PHP-WASM.')));
            }, { once: true });
        });

        return php;
    }

    // ── Exécution du code PHP ─────────────────────────────────────────────────

    async _executer(php, code) {
        let stdout = '';
        let stderr = '';

        // S'assurer que <?php est présent
        const codeComplet = /^\s*<\?/.test(code) ? code : `<?php\n${code}`;

        const onOutput = ({ detail }) => {
            stdout += Array.isArray(detail) ? detail.join('') : String(detail);
        };
        const onError = ({ detail }) => {
            stderr += Array.isArray(detail) ? detail.join('') : String(detail);
        };

        php.addEventListener('output', onOutput);
        php.addEventListener('error', onError);

        try {
            await php.run(codeComplet);
        } finally {
            php.removeEventListener('output', onOutput);
            php.removeEventListener('error', onError);
        }

        return { stdout, stderr };
    }
}
