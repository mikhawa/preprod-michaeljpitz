# 016 - Correction du test de redirection après connexion

**Date** : 2026-03-15
**Modèle** : Claude Sonnet
**Branche** : `dev/create-mail-system-for-dev`

---

## Problème identifié

### Échec du test `testLoginWithValidCredentials` en CI

**Symptôme** : Le workflow GitHub Actions échouait sur `SecurityControllerTest::testLoginWithValidCredentials`.
**Cause** : Le test attendait `assertResponseRedirects('/')` mais `security.yaml` configure `default_target_path: app_profile`, ce qui redirige vers `/profil` après connexion. Le test ne reflétait pas le comportement réel de l'application.
**Correction** : Ligne 40 de `tests/Functional/SecurityControllerTest.php` — remplacement de `assertResponseRedirects('/')` par `assertResponseRedirects('/profil')`.

---

## Fichiers modifiés

| Fichier | Modification |
|---------|-------------|
| `tests/Functional/SecurityControllerTest.php` | `assertResponseRedirects('/profil')` au lieu de `'/'` |
