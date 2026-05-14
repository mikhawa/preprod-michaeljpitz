# 027 — Mot de passe admin via variable d'environnement (PASS_ADMIN)

**Date :** 2026-05-14  
**Modèle :** Claude Sonnet 4.6  
**Branche :** `feature/07-pwd-by-env` → merge dans `dev/main` (PR #41)

---

## Contexte

Le mot de passe de l'administrateur était codé en dur dans `AppFixtures.php`. Cela posait un problème de sécurité (mot de passe visible dans le dépôt git) et rendait impossible la différenciation du mot de passe selon l'environnement (dev vs preprod).

---

## Solution

### 1. Ajout de `PASS_ADMIN` dans `.env`

```dotenv
PASS_ADMIN=123NousIronsAuxBois321
```

La valeur réelle de production ou preprod doit être surchargée dans `.env.local` (non versionné).

### 2. Injection dans `AppFixtures` via `#[Autowire]`

```php
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        #[Autowire(env: 'PASS_ADMIN')]
        private readonly string $mypassword,
    ) {}
```

### 3. Utilisation dans `load()`

```php
->setPassword($this->passwordHasher->hashPassword($admin, $this->mypassword));
```

---

## Avantages

- Le mot de passe n'apparaît plus en clair dans le code source versionné.
- Chaque environnement peut définir sa propre valeur dans `.env.local` ou via les secrets GitHub Actions.
- La variable est résolue au moment de la compilation du conteneur Symfony : aucun appel dynamique à `$_ENV`.

---

## Fichiers modifiés

- `.env` — ajout de `PASS_ADMIN`
- `src/DataFixtures/AppFixtures.php` — injection `#[Autowire(env: 'PASS_ADMIN')]` et remplacement du mot de passe codé en dur

---

## Autres corrections incluses dans la session

- **README.md** : correction du chemin `chown` pour le dossier `uploads` sur le serveur preprod (`/home/michaeljpitz.com/preprod.michaeljpitz.com/public/uploads/`).
- **PHP CS Fixer** : passe de formatage automatique sur `AppFixtures.php`.
- **Suppression** du fichier `Récupération` (fichier vide créé par erreur).

---

## Sécurité

- La valeur de `PASS_ADMIN` dans `.env` est un mot de passe de démonstration. Elle doit être remplacée dans `.env.local` ou via les variables d'environnement du serveur.
- `.env.local` est dans `.gitignore` — ne jamais commiter les mots de passe réels.