# 021 — Fix WASM/Suneditor : sauts de ligne parasites et indentation

**Date :** 2026-04-18  
**Modèle :** Claude Sonnet 4.6  
**Branche :** `fix/03-Wasm-into-suneditor-fixe-space-and-tab`

---

## Problèmes corrigés

### 1. Sauts de ligne en excès dans `buildWidget`

Suneditor stocke le code multiligne en plusieurs `<p>...</p>` séparés par `\n`.
L'ancienne version ne traitait que les `<br>` → après `strip_tags`, chaque paire
`</p>\n<p>` laissait 2–3 `\n` parasites. Une ligne vide voulue devenait 3–4 lignes vides.

**Cause précise :**  
`strip_tags("</p>\n<p>echo 'a';</p>\n<p><br></p>\n<p>echo 'b';</p>")` produisait
`\necho 'a';\n\n\n\necho 'b';\n` (4 sauts pour 1 ligne vide souhaitée).

### 2. Indentation impossible en mode WYSIWYG

Appuyer sur Tab dans Suneditor en mode WYSIWYG insère du CSS inline
(`<p style="padding-left:40px">`) ou des `<span>` de padding.
`strip_tags` supprimait ces balises → l'indentation disparaissait totalement.
Si l'utilisateur insérait des `&nbsp;`, le WASM recevait des caractères U+00A0
(espaces insécables) non reconnus comme délimiteurs PHP.

---

## Corrections apportées

### `src/Twig/Extension/PhpRunnerExtension.php` — `buildWidget()`

Pipeline de nettoyage revu dans cet ordre :

```
1. html_entity_decode          → &amp; &lt; &quot; → caractères réels
2. preg_replace </p>\s*<p>     → \n  (fusion des paragraphes Suneditor)
3. preg_replace <p> et </p>    → suppression des balises bloc résiduelles
4. preg_replace <br>           → \n
5. strip_tags                  → suppression des spans d'indentation CSS
6. str_replace \u{00A0}        → espace normal (NBSP → ASCII 32)
7. preg_replace \n{3,}         → \n\n max (une seule ligne vide entre blocs)
8. trim
```

### `assets/controllers/suneditor_controller.js` — Bouton PHP

Nouveau bouton **PHP** injecté directement dans la toolbar Suneditor via DOM
(pas via l'API plugin interne, dont le `action()` échouait silencieusement).

**Pourquoi pas l'API plugin :**  
Dans `action()`, `this` est le core interne Suneditor, pas l'API publique.
`core.insertHTML()` avait un comportement imprévisible. L'injection DOM directe
utilise `editor.insertHTML()` (API publique), robuste et documentée.

**Fonctionnement du bouton :**
1. Clic → `_injectPhpButton()` appelle `_openPhpDialog(callback)`
2. Une `<dialog>` native s'ouvre avec un `<textarea>` monospace
3. Tab → 4 espaces insérés en JS (pas de sortie de champ, pas de HTML)
4. Valider → chaque ligne HTML-encodée (`&amp;`, `&lt;`, `&gt;`, `&quot;`),
   jointure avec `<br>`, insertion via `editor.insertHTML('<p>[php]...[/php]</p>')`

Le contenu inséré est **opaque à Suneditor** : ni spans, ni CSS padding.
L'indentation (espaces) survit à travers `html_entity_decode` + `strip_tags`.

---

## Fichiers modifiés

| Fichier | Nature |
|---------|--------|
| `src/Twig/Extension/PhpRunnerExtension.php` | Fix pipeline `buildWidget` |
| `assets/controllers/suneditor_controller.js` | Bouton PHP injecté en DOM |
| `documentation/php-runner-wasm.md` | Mise à jour utilisation |
| `documentation/journal-decisions.md` | Entrée décision technique |

---

## Compatibilité ascendante

Le nouveau `buildWidget` gère correctement les deux formats :
- **Ancien** (code tapé en WYSIWYG avec `<p>` multiples) : `</p><p>` → `\n`
- **Nouveau** (via bouton PHP, `<br>` dans un seul `<p>`) : `<br>` → `\n`
