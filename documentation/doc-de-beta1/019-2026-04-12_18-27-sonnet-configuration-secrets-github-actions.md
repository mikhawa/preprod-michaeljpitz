# Configuration des secrets GitHub Actions pour le déploiement SSH

**Date :** 2026-04-12  
**Modèle :** Claude Sonnet 4.6  
**Contexte :** Mise en place des secrets nécessaires au déploiement automatique via SSH sur les branches `dev/main` et `preprod/main`.

---

## Problème rencontré

Le workflow de déploiement échouait avec l'erreur suivante :

```
dial tcp 92.113.25.248:***: i/o timeout
```

Cause : les secrets SSH n'étaient pas configurés sur le dépôt GitHub, rendant la connexion SSH impossible depuis le runner GitHub Actions.

---

## Secrets requis

Les deux workflows (`deploy-dev.yml` et `deploy-preprod.yml`) utilisent les mêmes 3 secrets :

| Nom du secret    | Description                              |
|------------------|------------------------------------------|
| `SSH_HOST`       | IP ou domaine du VPS cible               |
| `SSH_PORT`       | Port SSH du serveur (généralement `22`)  |
| `SSH_PRIVATE_KEY`| Clé privée SSH au format OpenSSH complet |

---

## Marche à suivre

### Étape 1 — Générer une paire de clés SSH dédiée

Sur ta machine locale (ou directement sur le serveur) :

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_deploy_key -N ""
```

Cela crée deux fichiers :
- `~/.ssh/github_deploy_key` → **clé privée** (à coller dans GitHub)
- `~/.ssh/github_deploy_key.pub` → **clé publique** (à installer sur le serveur)

---

### Étape 2 — Autoriser la clé publique sur le serveur

Se connecter au VPS et ajouter la clé publique :

```bash
cat ~/.ssh/github_deploy_key.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

Vérifier que la connexion fonctionne depuis ta machine locale :

```bash
ssh -i ~/.ssh/github_deploy_key -p <PORT> root@<IP_DU_SERVEUR>
```

---

### Étape 3 — Ajouter les secrets sur GitHub

Aller sur : `https://github.com/mikhawa/preprod-michaeljpitz/settings/secrets/actions`

Cliquer sur **New repository secret** pour chacun des 3 secrets :

#### `SSH_HOST`
- **Name :** `SSH_HOST`
- **Secret :** l'IP ou le domaine du VPS (ex : `92.113.25.248`)

#### `SSH_PORT`
- **Name :** `SSH_PORT`
- **Secret :** le port SSH (ex : `22`)

#### `SSH_PRIVATE_KEY`
- **Name :** `SSH_PRIVATE_KEY`
- **Secret :** le contenu complet de la clé privée, obtenu avec :

```bash
cat ~/.ssh/github_deploy_key
```

Copier tout le bloc, de `-----BEGIN OPENSSH PRIVATE KEY-----` jusqu'à `-----END OPENSSH PRIVATE KEY-----` inclus (avec les sauts de ligne).

---

### Étape 4 — Vérifier le pare-feu du serveur

S'assurer que le port SSH est accessible depuis l'extérieur :

```bash
# Vérifier l'état du pare-feu
sudo ufw status

# Autoriser le port SSH si nécessaire
sudo ufw allow <PORT>/tcp
```

Les runners GitHub Actions utilisent des plages d'IPs variables. Il faut donc autoriser les connexions entrantes sur le port SSH depuis n'importe quelle source, et s'appuyer sur l'authentification par clé pour la sécurité.

---

## Workflows concernés

| Workflow | Branche déclencheur | Répertoire cible sur le serveur |
|---|---|---|
| `deploy-dev.yml` | `dev/main` | `/home/michaeljpitz.com/dev.michaeljpitz.com/` |
| `deploy-preprod.yml` | `preprod/main` | `/home/michaeljpitz.com/preprod.michaeljpitz.com/` |

Les deux utilisent `username: root` et les mêmes 3 secrets.
