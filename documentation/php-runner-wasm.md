# PHP Runner WASM — Exécution de PHP dans le navigateur

## Objectif

Permettre aux visiteurs d'un article de **voir et exécuter du code PHP directement dans leur navigateur**, sans serveur, via [php-wasm](https://github.com/seanmorris/php-wasm) (PHP compilé en WebAssembly).

## Architecture

- Le fichier `.wasm` (~16 Mo) est chargé **depuis le CDN jsDelivr** à la demande (premier clic sur "Exécuter").
- PHP s'exécute **entièrement dans le sandbox du navigateur** : aucun risque de sécurité côté serveur.
- Le contrôleur Stimulus `php-runner` est attaché au `<div>` du contenu de l'article dans `show.html.twig`. Il scanne les `<pre class="php-runner">` et les remplace par le widget interactif (textarea + bouton + sortie).
- Le contenu stocké en base est uniquement `<pre class="php-runner">...</pre>` : pas de data-*, pas de textarea, pas de bouton.

## Décisions techniques

| Alternative | Raison du rejet |
|-------------|-----------------|
| `wasm/wasm` (Composer) | Incompatible PHP 8.3 sans extension C native |
| Endpoint serveur + proc_open | Risque sécurité, complexité sandboxing |
| `<div data-controller>` dans le contenu | Suneditor supprime textarea/button, sanitizer supprime data-* |
| `<pre class="php-runner">` → JS transforme | Retenu : compatible Suneditor et sanitizer |

## Utilisation dans un article

Dans Suneditor (mode WYSIWYG normal, pas besoin de vue code), taper :

```
[php]echo "PHP " . PHP_VERSION;[/php]
```

**Règles :**
- Pas de `<?php` ni `?>` — le contrôleur les ajoute automatiquement pour le visiteur
- Le marqueur peut être sur une ou plusieurs lignes
- Suneditor le préserve tel quel (c'est du texte brut, pas du HTML)
- Le filtre Twig `php_runner` le convertit en widget côté serveur

**Pourquoi ce format et pas du HTML dans Suneditor ?**
Suneditor parse son contenu via `innerHTML`. La spec HTML5 traite `<?php ?>` comme un "bogus comment" (`<!-- ?php ... ? -->`). Les attributs `data-*` et éléments de formulaire (`<textarea>`, `<button>`) sont supprimés par le parser WYSIWYG. Le marqueur texte `[php]...[/php]` est la seule approche fiable.

## Comportement au rendu

1. Le contrôleur `php-runner` se connecte sur le `<div class="prose">` du template.
2. Il trouve chaque `<pre class="php-runner">` et le **remplace** par un widget :
   - Un `<textarea>` pré-rempli avec le code (éditable par le visiteur)
   - Un bouton **Exécuter**
   - Une zone `<pre>` pour la sortie stdout (rouge si erreur PHP)
3. Au premier clic : chargement de `PhpWeb.mjs` + `php-web.mjs.wasm` (~16 Mo, mis en cache).
4. Chaque bloc a sa propre instance php-wasm (état isolé entre blocs).

## Limites de php-wasm

- PHP pur uniquement : pas de PDO, pas d'accès réseau depuis PHP, pas de GD
- Premier chargement : ~16 Mo (mis en cache navigateur ensuite)
- PHP 8.x (version incluse dans le .wasm du package)

## Fichiers modifiés

| Fichier | Modification |
|---------|-------------|
| `assets/controllers/php_runner_controller.js` | Contrôleur Stimulus : scan + transformation des `<pre.php-runner>` |
| `config/packages/html_sanitizer.yaml` | `pre: ['class']` ajouté à `article_sanitizer` |
| `templates/article/show.html.twig` | `php-runner` ajouté au `data-controller` du div de contenu |
| `src/EventSubscriber/SecurityHeadersSubscriber.php` | CSP : `cdn.jsdelivr.net` (script-src, connect-src) + `worker-src blob:` |

## CSP ajoutée

```
script-src  … https://cdn.jsdelivr.net
connect-src … https://cdn.jsdelivr.net
worker-src  blob: 'self'
```

Le `worker-src blob:` est requis car php-wasm instancie un Web Worker via `URL.createObjectURL()`.
