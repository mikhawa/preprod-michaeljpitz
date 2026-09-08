# Suppression de CONTACT_FALLBACK_EMAIL dans ContactController

## Problème
`CONTACT_FALLBACK_EMAIL` était injecté comme fallback pour l'email admin,
mais `ADMIN_EMAIL` remplit désormais ce rôle directement. La variable locale
`$adminEmail` (issue de `getAdminEmail()`) était calculée mais jamais utilisée.

## Solution
Nettoyage de `ContactController` :
- Suppression de `$fallbackEmail` et de son `#[Autowire(CONTACT_FALLBACK_EMAIL)]`
- Suppression de `$userRepository` et de son import
- Suppression de la ligne `$adminEmail = $this->getAdminEmail() ?? $this->fallbackEmail`
- Suppression de la méthode privée `getAdminEmail()`

## Fichier modifié
`src/Controller/ContactController.php`

## Vérification
`php bin/console lint:container` — OK

## Modèle utilisé
Claude Sonnet 4.6
