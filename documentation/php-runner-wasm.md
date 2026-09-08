# PHP Runner WASM — Exécution de PHP dans le navigateur

## Objectif

Permettre aux visiteurs d'un article de **voir et exécuter du code PHP directement dans leur navigateur**, sans serveur, via [php-wasm](https://github.com/seanmorris/php-wasm) (PHP compilé en WebAssembly).

## Utilisation dans un article

### Méthode recommandée : bouton **PHP** dans la toolbar

1. Placer le curseur à l'endroit souhaité dans l'éditeur
2. Cliquer sur le bouton **PHP** (à droite dans la toolbar Suneditor)
3. Écrire le code PHP **sans `<?php`** dans la fenêtre qui s'ouvre
4. Utiliser **Tab** pour indenter (4 espaces, jamais de HTML injecté)
5. Cliquer **Insérer**

Le bloc `[php]...[/php]` est inséré avec le code correctement encodé.

### Règles générales
- Pas de `<?php` ni `?>` — ajoutés automatiquement à l'exécution
- **Ne pas modifier un bloc `[php]...[/php]` directement en WYSIWYG** :
  Tab et le bouton Indent Suneditor injectent du CSS qui casse l'indentation.
  Pour corriger, passer en vue **Code source** et éditer le texte brut.
- Le visiteur peut modifier le code dans le widget et le ré-exécuter

---

## Architecture finale

```
Auteur (Suneditor WYSIWYG)
  → stocke : <p>[php]echo PHP_VERSION;[/php]</p>

Twig (article/show.html.twig)
  → filtre php_runner traite le contenu
  → génère : <div data-controller="php-runner" data-php-runner-code-value="echo PHP_VERSION;"></div>

Stimulus (php_runner_controller.js)
  → lit data-php-runner-code-value
  → crée l'UI : textarea + bouton Exécuter + zone de sortie
  → au clic : charge php-wasm depuis jsDelivr (~16 Mo, mis en cache)
  → exécute le code PHP dans le navigateur
```

---

## Pourquoi ce format et pas du HTML dans Suneditor

Toutes les approches basées sur du HTML personnalisé dans le contenu Suneditor échouent :

| Approche testée | Cause de l'échec |
|-----------------|-----------------|
| `<pre class="php-runner"><?php...` | Spec HTML5 : `<?...?>` → bogus comment `<!-- ?php ... ? -->` |
| `<pre class="php-runner">echo...` | Suneditor supprime `class` sur certains éléments |
| `<div data-controller="php-runner">` | Suneditor supprime les attributs `data-*` non standards |
| `<textarea>` dans le contenu | Suneditor supprime les éléments de formulaire |

**Le marqueur texte `[php]...[/php]` est la seule approche fiable** : c'est du texte brut que Suneditor préserve sans le modifier.

---

## Fichiers du projet

### `src/Twig/Extension/PhpRunnerExtension.php` (nouveau)

Filtre Twig `php_runner`. Cherche les marqueurs `[php]...[/php]` dans le contenu
(y compris enveloppés dans `<p>...</p>` par Suneditor) et les remplace par :

```html
<div data-controller="php-runner" data-php-runner-code-value="CODE_ENCODÉ"></div>
```

Gère également (pipeline `buildWidget`) :
- Le décodage des entités HTML (`&lt;` → `<`, `&quot;` → `"`)
- La fusion des paires `</p><p>` Suneditor en `\n` unique (évite les sauts parasites)
- La conversion des `<br>` en sauts de ligne (`\n`)
- La suppression des balises HTML résiduelles via `strip_tags()`
- La conversion des espaces insécables NBSP (U+00A0) en espaces normaux
- La normalisation des sauts multiples (max 2 consécutifs = 1 ligne vide)

### `assets/controllers/php_runner_controller.js` (nouveau)

Contrôleur Stimulus avec l'API `values` (`static values = { code: String }`).

À la connexion (`connect()`), construit dynamiquement le widget :
- `<textarea>` pré-rempli avec `<?php\n{code}` (éditable par le visiteur)
- Bouton **Exécuter** (désactivé pendant l'exécution)
- Message de statut (chargement, exécution…)
- `<pre>` de sortie (rouge si erreur PHP)

Au clic sur Exécuter :
1. Import dynamique de `PhpWeb.mjs` depuis `cdn.jsdelivr.net/npm/php-wasm/`
2. Attente de l'événement `ready`
3. Exécution via `php.run(code)`
4. Capture des événements `output` et `error`
5. Affichage du résultat

Chaque widget a sa propre instance php-wasm (état isolé).

### `templates/article/show.html.twig` (modifié)

```twig
{# Avant #}
{{ article.content|raw }}

{# Après #}
{{ article.content|php_runner|raw }}
```

### `src/EventSubscriber/SecurityHeadersSubscriber.php` (modifié)

CSP élargie pour autoriser le chargement de php-wasm depuis jsDelivr :

```
script-src  … https://cdn.jsdelivr.net
connect-src … https://cdn.jsdelivr.net
worker-src  blob: 'self'
```

`worker-src blob:` requis car php-wasm instancie un Web Worker via `URL.createObjectURL()`.

### `config/packages/html_sanitizer.yaml` (modifié, sans effet réel)

`pre: ['class']` ajouté à `article_sanitizer` — cette modification est sans effet car
le sanitizer n'est **pas appliqué** au contenu des articles (seulement aux commentaires).
Elle peut être conservée pour cohérence ou revertée.

---

## Limites de php-wasm

- PHP pur uniquement : pas de PDO, pas d'extensions natives (GD, curl…)
- Premier chargement : ~16 Mo (fichier `.wasm`, mis en cache navigateur ensuite)
- Performances inférieures à PHP natif (overhead WebAssembly)
- Version PHP fixée par le package npm (PHP 8.x)
