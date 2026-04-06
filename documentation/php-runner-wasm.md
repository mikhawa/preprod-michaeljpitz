# PHP Runner WASM — Exécution de PHP dans le navigateur

## Objectif

Permettre aux visiteurs d'un article de **voir et exécuter du code PHP directement dans leur navigateur**, sans serveur, via [php-wasm](https://github.com/seanmorris/php-wasm) (PHP compilé en WebAssembly).

## Architecture

- Le fichier `.wasm` (~16 Mo) est chargé **depuis le CDN jsDelivr** à la demande (premier clic sur "Exécuter").
- PHP s'exécute **entièrement dans le sandbox du navigateur** : aucun accès au serveur, aucun risque de sécurité côté serveur.
- Le contrôleur Stimulus `php-runner` gère le cycle de vie (chargement, exécution, affichage).

## Décision technique

| Alternative | Raison du rejet |
|-------------|-----------------|
| `wasm/wasm` (Composer) | Incompatible PHP 8.3 sans extension C native |
| Endpoint serveur + proc_open | Risque sécurité, complexité sandboxing |
| Iframe 3v4l.org | Moins intégré, dépendance externe non maîtrisée |

**Choix retenu :** php-wasm côté client via Stimulus + import dynamique.

## Utilisation dans un article

Dans l'éditeur Suneditor, passer en mode "Code source" (icône `</>`) et insérer le bloc suivant :

```html
<div data-controller="php-runner" class="my-6 rounded-lg border border-[var(--border)] bg-[var(--bg-secondary)] p-4">
  <textarea
    data-php-runner-target="code"
    class="w-full rounded border border-[var(--border)] bg-[var(--bg-primary)] p-3 font-mono text-sm text-[var(--text-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--accent)]"
    rows="6"
  ><?php
echo "Bonjour depuis PHP " . PHP_VERSION . " !";
?></textarea>

  <div class="mt-3 flex items-center gap-3">
    <button
      data-action="click->php-runner#run"
      data-php-runner-target="run"
      class="rounded bg-[var(--accent)] px-4 py-2 text-sm font-medium text-white hover:bg-[var(--accent-hover)] transition-colors disabled:opacity-50"
    >
      Exécuter
    </button>
    <span data-php-runner-target="status" class="text-sm text-[var(--text-secondary)] italic"></span>
  </div>

  <pre
    data-php-runner-target="output"
    class="mt-4 min-h-[2rem] whitespace-pre-wrap rounded border border-[var(--border)] bg-[var(--bg-primary)] p-3 font-mono text-sm text-[var(--text-primary)]"
  ></pre>
</div>
```

## Comportement

| Cible Stimulus | Rôle |
|----------------|------|
| `code` | `<textarea>` contenant le code PHP (éditable par le visiteur) |
| `run` | Bouton "Exécuter" (désactivé pendant l'exécution) |
| `status` | Message d'état (chargement, exécution…) |
| `output` | Zone d'affichage de la sortie (rouge en cas d'erreur PHP) |

## Limites de php-wasm

- PHP pur uniquement : pas de PDO, pas de GD, pas d'accès réseau depuis PHP
- Premier chargement : ~16 Mo (mis en cache ensuite par le navigateur)
- Performances inférieures à un PHP natif (WASM overhead)

## Fichiers modifiés

| Fichier | Modification |
|---------|-------------|
| `assets/controllers/php_runner_controller.js` | Nouveau contrôleur Stimulus |
| `src/EventSubscriber/SecurityHeadersSubscriber.php` | CSP élargie : `cdn.jsdelivr.net` (script-src, connect-src) + `worker-src blob:` |

## CSP ajoutée

```
script-src  … https://cdn.jsdelivr.net
connect-src … https://cdn.jsdelivr.net
worker-src  blob: 'self'
```

Le `worker-src blob:` est requis car php-wasm instancie un Web Worker via `URL.createObjectURL()`.
