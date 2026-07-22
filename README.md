# RESSOURCES by Fitness Challenges — espace membres

Espace membres sécurisé + bibliothèque de formation + assistant IA (RAG) pour la marque
**RESSOURCES** (MD MEDIA EVENT). Stack **PHP 8.2 / MySQL**, sans framework, déployable sur
mutualisé classique (Amen) en FTP + phpMyAdmin.

> Portage du socle éprouvé de `presta-hub` (auth, routing, migrations, chiffrement, mailer).

## Périmètre par lots

- **Lot 1 — Fondations (ce commit)** : structure, schéma DB complet, auth PHP native
  (login / mot de passe oublié / reset / invitation), et le **paywall** : accès conditionné
  à un *club actif* **ET** un *membre actif*, re-vérifié en base à chaque requête.
- Lot 2 — Bibliothèque + navigation (10 catégories) + lecteur Vimeo protégé.
- Lot 3 — Back-office admin (clubs, membres, contenus, suspension d'accès).
- Lot 4 — Assistant IA RAG (vectorisation en PHP, recherche cosinus, réponses Claude).

## Décisions structurantes

| Sujet | Choix |
|---|---|
| Hébergement | Amen mutualisé — PHP/MySQL, cron via panel, pas de service tiers |
| Comptes | **Club à sièges** : un manager (`club_owner`) invite N collaborateurs (`club_member`) |
| Paiement | **Pas de Stripe en V1** — ouverture/fermeture d'accès manuelle (champ `status` du club) |
| Vidéo | **Vimeo Pro** privé + restriction de domaine (pas de téléchargement) |
| Recherche vectorielle | **PHP pur** — embeddings en BLOB, cosinus en mémoire (pas de pgvector/Pinecone) |
| Migration contenus | Chargement **un par un** via le back-office |

## Installation

```bash
composer install
cp .env.example .env      # renseigner DB_*, MAIL_*, APP_SECRET, INSTALL_TOKEN
```

Générer les secrets :

```bash
php -r "echo bin2hex(random_bytes(32));"   # APP_SECRET
php -r "echo bin2hex(random_bytes(16));"   # INSTALL_TOKEN
```

Puis, au premier déploiement, ouvrir `https://<domaine>/install?token=<INSTALL_TOKEN>` :
la page applique les migrations en attente (via `MigrationRunner`) et crée le compte
super-admin. Un verrou `storage/install.lock` empêche toute relance. Les évolutions de
schéma ultérieures passeront par `/admin/migrations` (lot 3).

> **Note environnement dev :** sur la machine de développement, l'antivirus (Avast)
> met en quarantaine en temps réel tout script PHP CLI de migration (heuristique
> « manipulation DB »). On applique donc les migrations via `/install`, ou en important
> les fichiers `migrations/*.sql` directement dans phpMyAdmin. Sur Amen (sans Avast),
> `/install` est le chemin nominal.

## Structure

```
public/           # seul dossier exposé (front controller + assets)
src/
  Controllers/    # Auth, Home, Dashboard, Install
  Middleware/     # Auth (session) + Membership (paywall)
  Models/         # User, Club
  Repositories/   # User, Club, PasswordToken
  Services/       # Mailer, MigrationRunner
  Helpers/        # Csrf, Encryption (AES-256), Renderer
  Templates/      # layouts + pages (PHP plein, pas de Twig)
config/           # app.php, database.php, routes.php
migrations/       # 001_init.sql (schéma), 002_seed_categories.sql (10 catégories)
storage/          # logs, uploads (hors webroot)
```

## Sécurité (rappels)

- Fichiers PDF/templates stockés **hors** `public/` ; téléchargement via lien signé HMAC (lot 2).
- Vidéos Vimeo privées + restriction de domaine.
- Journalisation des connexions et accès (`activity_logs`, conservation 12 mois — CDC §8).
- CSRF sur tous les POST ; sessions cookies `httponly` + `secure`.
