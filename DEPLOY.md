# Déploiement sur Amen (FTP) — RESSOURCES

Le projet se déploie automatiquement sur l'hébergement Amen à chaque push sur `master`
via GitHub Actions (`.github/workflows/deploy.yml`). Le workflow fait `composer install`
(prod) puis un `lftp mirror` vers le FTP — **sans jamais toucher au `.env` ni au `storage/`
du serveur**.

## Setup initial (à faire UNE seule fois)

### 1. Ajouter les secrets GitHub

Sur https://github.com/2AMDMEDIA/ressources-hub/settings/secrets/actions, ajouter 3 secrets :

| Nom | Valeur |
|---|---|
| `FTP_HOST` | Le serveur FTP Amen (ex. `ftp.ressources-fitness.com` ou l'hôte fourni par Amen) |
| `FTP_USERNAME` | L'identifiant FTP Amen |
| `FTP_PASSWORD` | Le mot de passe FTP Amen |

> ⚠️ Ces 3 secrets doivent être saisis **par toi** dans l'interface GitHub (Claude ne
> manipule jamais de mots de passe). Tant qu'ils ne sont pas là, l'étape de déploiement
> échoue proprement — le reste du workflow (build) tourne quand même.

> Le workflow utilise le FTP standard (port 21, `ssl-allow no`). Si Amen impose FTPS,
> ajuster `set ftp:ssl-allow` dans le workflow.

### 2. Pointer le sous-domaine sur `/public/`

Dans le manager Amen, créer le sous-domaine (ex. `membres.ressources-fitness.com`) et
configurer son **document root sur `/public/`**. Seul `public/` doit être exposé au web ;
`src/`, `config/`, `.env`, `storage/` restent au-dessus de la racine web.

Si le panel ne permet pas de choisir le docroot, contacter le support Amen pour qu'ils
pointent le sous-domaine sur `public/`.

### 3. Créer la base MySQL chez Amen

Créer une base (ex. `ressources_hub`) et noter : nom, utilisateur, mot de passe, hôte.

### 4. Créer le `.env` sur le serveur (manuellement via FTP)

La GitHub Action **n'upload jamais** le `.env` (elle écraserait la config prod). À déposer
manuellement à la racine du projet (à côté de `composer.json`, **PAS** dans `public/`) —
partir de `.env.example` :

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://membres.ressources-fitness.com
APP_NAME="RESSOURCES"

DB_HOST=mysql.amen.fr        # ce qu'Amen fournit
DB_PORT=3306
DB_NAME=ressources_hub
DB_USER=ton_user
DB_PASS=ton_password
DB_CHARSET=utf8mb4

MAIL_HOST=smtp.amen.fr
MAIL_PORT=587
MAIL_USER=
MAIL_PASS=
MAIL_ENCRYPTION=tls
MAIL_FROM_EMAIL=ressources@fitness-challenges.com
MAIL_FROM_NAME="RESSOURCES"

# Clé AES — à conserver précieusement (si perdue, les clés API chiffrées en DB sont illisibles)
# php -r "echo bin2hex(random_bytes(32));"
APP_SECRET=METS_UNE_VALEUR_GENEREE_ICI

SESSION_NAME=ressources_sess
SESSION_LIFETIME=86400
APP_TLS_VERIFY=true

# Token d'installation one-shot — php -r "echo bin2hex(random_bytes(16));"
INSTALL_TOKEN=ton_token_secret_long_et_unique
```

### 5. Lancer l'installation via `/install`

1. Ouvrir `https://membres.ressources-fitness.com/install?token=<INSTALL_TOKEN>`.
2. La page affiche l'état DB, les migrations à appliquer, et un formulaire super-admin.
3. Remplir (email + mot de passe + nom) → l'installateur applique
   `001_init.sql` puis `002_seed_categories.sql`, crée le super-admin, puis **verrouille**
   l'installateur via `storage/install.lock`.
4. Se connecter ensuite sur `/login`.

> Les migrations futures passeront par `/admin/migrations` (lot 3).
> Aucun script CLI de migration n'est fourni (bloqué par l'antivirus en local — voir README) ;
> en secours, importer les `migrations/*.sql` directement dans phpMyAdmin.

### 6. Fallback manuel (si `/install` inaccessible)

- Importer les `migrations/*.sql` via phpMyAdmin Amen (dans l'ordre).
- Créer le super-admin en SQL avec un hash bcrypt généré localement :
  ```powershell
  php -r "echo password_hash('TonMotDePasse', PASSWORD_BCRYPT, ['cost' => 12]);"
  ```
  ```sql
  INSERT INTO users (id, email, password_hash, full_name, role, is_super_admin, status)
  VALUES (UUID(), 'toi@email.com', 'COLLE_LE_HASH_ICI', 'Ton Nom', 'super_admin', 1, 'active');
  ```

## Déploiements suivants

À chaque `git push origin master` → upload auto (~30 s grâce au diff lftp). Rien à toucher
sur le serveur. Déclenchement manuel possible via l'onglet **Actions → Run workflow**.

## Ce qui est exclu de l'upload

`.env`, `.git/`, `.github/`, `.claude/`, `*.log`, `.gitignore`, `.gitattributes`,
`.editorconfig`, `phpunit.xml*`. Le `storage/` du serveur (logs, uploads, `install.lock`)
est préservé : le mirror n'a pas de `--delete`, il n'efface jamais un fichier côté serveur.
`vendor/` **est** uploadé (généré par `composer install --no-dev` dans l'Action).

## En cas d'échec

Onglet https://github.com/2AMDMEDIA/ressources-hub/actions → run rouge → log détaillé.
Erreurs typiques : `530 Login incorrect` (secrets FTP), `Connection timeout` (FTP_HOST/port),
`553` (droits d'écriture racine FTP), fichiers non synchro (supprimer
`.ftp-deploy-sync-state.json` côté FTP si présent, puis relancer).
