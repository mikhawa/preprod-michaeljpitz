# Journal des décisions techniques

> Ce fichier recense les décisions techniques prises au fil du développement, avec leur contexte et justification. à partir du 15 mars 2026.

---

## 2026-03-14

### MEMORY.md versionné dans git
**Décision** : Créer `.claude/MEMORY.md` dans le projet, versionné dans git.
**Raison** : Permettre à Claude Code de retrouver le contexte du projet sur n'importe quelle machine sans reconfiguration.

### Choix autonome du modèle Claude
**Décision** : Claude choisit lui-même le modèle le plus adapté (Opus/Sonnet/Haiku) selon la complexité de la tâche.
**Raison** : Éviter de solliciter l'utilisateur pour chaque changement de modèle. Règles définies dans `.claude/models.md`.

### ADMIN_EMAIL comme destinataire unique des notifications admin
**Décision** : Toutes les notifications email vers l'admin utilisent `ADMIN_EMAIL` (`.env`) via `#[Autowire]`.
**Raison** : Les adresses en dur `contact@alpha1.michaeljpitz.com` étaient des reliquats de l'environnement alpha, non adaptés à la preprod/prod.
**Fichiers** : `ArticleController`, `ContactController`, `RegistrationController`.

### Suppression de CONTACT_FALLBACK_EMAIL
**Décision** : Supprimer `CONTACT_FALLBACK_EMAIL` et la méthode `getAdminEmail()` dans `ContactController`.
**Raison** : Redondant avec `ADMIN_EMAIL`. La variable locale `$adminEmail` était calculée mais jamais utilisée.

