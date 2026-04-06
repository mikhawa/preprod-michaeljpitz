import { Controller } from '@hotwired/stimulus';

/**
 * Contrôleur Stimulus : exécution de PHP dans le navigateur via php-wasm (WebAssembly).
 *
 * Usage dans un article (Twig / contenu Suneditor) :
 *
 *   <div data-controller="php-runner">
 *     <textarea data-php-runner-target="code"><?php echo "Bonjour !"; ?></textarea>
 *     <button data-action="click->php-runner#run" data-php-runner-target="run">
 *       Exécuter
 *     </button>
 *     <pre data-php-runner-target="output"></pre>
 *   </div>
 *
 * Le fichier .wasm (~16 Mo) est chargé depuis le CDN jsDelivr uniquement
 * au premier clic sur "Exécuter". Le navigateur le met ensuite en cache.
 */
export default class extends Controller {
    static targets = ['code', 'output', 'run', 'status'];

    connect() {
        this._php = null;
        this._running = false;
    }

    disconnect() {
        this._php = null;
    }

    async run() {
        if (this._running) {
            return;
        }

        this._running = true;
        this._setRunning(true);
        this._clearOutput();

        try {
            await this._ensurePhp();
            await this._execute();
        } catch (err) {
            this._showError('Erreur : ' + err.message);
        } finally {
            this._running = false;
            this._setRunning(false);
        }
    }

    // ── Privé ────────────────────────────────────────────────────────────────

    /**
     * Charge PhpWeb depuis le CDN (une seule fois par instance de contrôleur).
     * Le .wasm est mis en cache par le navigateur après le premier chargement.
     */
    async _ensurePhp() {
        if (this._php) {
            return;
        }

        this._setStatus('Chargement de PHP (première utilisation, ~16 Mo)…');

        const { PhpWeb } = await import(
            'https://cdn.jsdelivr.net/npm/php-wasm/PhpWeb.mjs'
        );

        this._php = new PhpWeb();

        await new Promise((resolve, reject) => {
            const onReady = () => {
                this._php.removeEventListener('error', onError);
                resolve();
            };
            const onError = (e) => {
                this._php.removeEventListener('ready', onReady);
                reject(new Error(e.detail ?? 'Impossible de charger PHP-WASM.'));
            };
            this._php.addEventListener('ready', onReady, { once: true });
            this._php.addEventListener('error', onError, { once: true });
        });

        this._setStatus('');
    }

    async _execute() {
        this._setStatus('Exécution…');

        let stdout = '';
        let stderr = '';

        const onOutput = ({ detail }) => {
            if (Array.isArray(detail)) {
                stdout += detail.join('');
            } else {
                stdout += String(detail);
            }
        };

        const onError = ({ detail }) => {
            if (Array.isArray(detail)) {
                stderr += detail.join('');
            } else {
                stderr += String(detail);
            }
        };

        this._php.addEventListener('output', onOutput);
        this._php.addEventListener('error', onError);

        try {
            const code = this.hasCodeTarget ? this.codeTarget.value : '';
            await this._php.run(code);
        } finally {
            this._php.removeEventListener('output', onOutput);
            this._php.removeEventListener('error', onError);
        }

        if (stderr) {
            this._showError(stderr);
        } else {
            this._showOutput(stdout || '(aucune sortie)');
        }

        this._setStatus('');
    }

    // ── DOM helpers ───────────────────────────────────────────────────────────

    _clearOutput() {
        if (this.hasOutputTarget) {
            this.outputTarget.textContent = '';
            this.outputTarget.classList.remove('text-red-500');
        }
    }

    _showOutput(text) {
        if (this.hasOutputTarget) {
            this.outputTarget.textContent = text;
            this.outputTarget.classList.remove('text-red-500');
        }
    }

    _showError(text) {
        if (this.hasOutputTarget) {
            this.outputTarget.textContent = text;
            this.outputTarget.classList.add('text-red-500');
        }
    }

    _setStatus(message) {
        if (this.hasStatusTarget) {
            this.statusTarget.textContent = message;
        }
    }

    _setRunning(running) {
        if (this.hasRunTarget) {
            this.runTarget.disabled = running;
            this.runTarget.textContent = running ? 'Exécution…' : 'Exécuter';
        }
    }
}
