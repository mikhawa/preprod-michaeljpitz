---
description: Créer un test unitaire ou fonctionnel pour un fichier source PHP
argument-hint: "<chemin/du/fichier/source.php>"
allowed-tools: Bash, Read, Write, Glob, Grep
---

# Créer un test pour un fichier source

Argument fourni : $ARGUMENTS

1. **Lis le fichier source** `$ARGUMENTS` pour comprendre sa structure.

2. **Détermine le type de test** :
   - `src/Entity/` → Test unitaire dans `tests/Unit/Entity/` (étend `TestCase`)
   - `src/Service/` → Test unitaire dans `tests/Unit/Service/` (étend `TestCase`)
   - `src/EventSubscriber/` → Test unitaire dans `tests/Unit/EventSubscriber/` (étend `TestCase`)
   - `src/Security/` → Test unitaire dans `tests/Unit/Security/` (étend `TestCase`)
   - `src/Controller/` → Test fonctionnel dans `tests/Functional/` (étend `WebTestCase`, utilise `TestDatabaseTrait`)
   - `src/Repository/` → Test fonctionnel dans `tests/Functional/Repository/` (étend `KernelTestCase`, utilise `TestDatabaseTrait`)
   - `src/Command/` → Test fonctionnel dans `tests/Functional/` (étend `KernelTestCase`, utilise `TestDatabaseTrait`)

3. **Conventions à respecter** :
   - Le fichier commence par `declare(strict_types=1);`
   - Utiliser `createStub()` au lieu de `createMock()` quand il n'y a pas d'expectations
   - Les tests fonctionnels utilisent les helpers de `TestDatabaseTrait` : `createUser`, `createActiveUser`, `createAdminUser`, `createCategory`, `createArticle`, `createComment`, `createRating`, `cleanDatabase`
   - Les tests fonctionnels appellent `$this->cleanDatabase()` dans `setUp()` ou au début du test
   - Utiliser des named arguments pour les contraintes Symfony (pas de tableaux)
   - Chaque méthode de test commence par `test` et a un nom descriptif en anglais

4. **Lis les tests existants** dans le même dossier pour t'aligner sur le style.

5. **Crée le fichier de test** couvrant les méthodes publiques principales. Pas de tests pour les getters/setters triviaux sauf s'ils contiennent de la logique.

6. **Lance le test** via `docker compose exec php php bin/phpunit <chemin_du_test>` pour vérifier qu'il passe.
