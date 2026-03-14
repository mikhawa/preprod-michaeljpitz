# Règles d'attribution des modèles Claude

Claude devra choisir son modèle le plus adapté en fonction de la complexité de la tâche à accomplir. Voici les règles d'attribution :

## Claude Opus — Tâches complexes
Utiliser pour :
- Conception d'architecture (DDD, bounded contexts)
- Sécurité : design des voters, stratégie d'authentification
- Refactoring majeur avec impact transverse
- Résolution de bugs complexes (race conditions, performances)
- Génération de la documentation d'architecture

Prompt d'amorce : "Tu es un architecte Symfony senior. Contexte : voi Propose une solution pour [problème précis]."

## Claude Sonnet — Tâches intermédiaires
Utiliser pour :
- Génération de controllers avec logique métier
- Couche Service complète
- Tests fonctionnels (PHPUnit, Panther)
- Intégration API externe
- Configuration GitHub Actions

Prompt d'amorce : "Tu es un développeur Symfony 7.4 expérimenté. Stack : PHP 8.3, Doctrine ORM. Propose une solution pour [problème précis]."

## Claude Haiku — Tâches simples/répétitives
Utiliser pour :
- CRUD basique (entité + controller + templates Twig)
- Migrations Doctrine
- Composants Stimulus simples
- Corrections de typos, formatage
- Questions rapides sur la syntaxe

Prompt d'amorce : "Symfony 7.4, PHP 8.3. Génère [tâche précise]."
