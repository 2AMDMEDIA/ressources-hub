-- =============================================================================
-- RESSOURCES by Fitness Challenges — schéma initial (MVP)
-- MySQL / MariaDB — InnoDB — utf8mb4_unicode_ci
--
-- Choix : pas de contraintes FOREIGN KEY dures (dépendances circulaires
-- clubs <-> users, et robustesse sur mutualisé). L'intégrité est portée par
-- l'application ; les colonnes de liaison sont indexées.
-- =============================================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- -----------------------------------------------------------------------------
-- Clubs (le tenant : un club abonné = un accès)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clubs` (
    `id`            CHAR(36)      NOT NULL,
    `name`          VARCHAR(255)  NOT NULL,
    `owner_user_id` CHAR(36)      NULL,                       -- le manager (compte propriétaire)
    `status`        ENUM('active','suspended','closed') NOT NULL DEFAULT 'active',
    `seats_limit`   INT           NOT NULL DEFAULT 1,         -- nb de collaborateurs autorisés
    `contact_email` VARCHAR(255)  NULL,
    `contract_ref`  VARCHAR(255)  NULL,
    `notes`         TEXT          NULL,
    `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_clubs_status` (`status`),
    KEY `idx_clubs_owner` (`owner_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Utilisateurs (super-admin MD MEDIA / owner manager / membre collaborateur)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`                   CHAR(36)     NOT NULL,
    `club_id`              CHAR(36)     NULL,                 -- NULL pour un super-admin
    `email`                VARCHAR(255) NOT NULL,
    `password_hash`        VARCHAR(255) NULL,                 -- NULL tant que l'invité n'a pas défini son mdp
    `full_name`            VARCHAR(255) NOT NULL DEFAULT '',
    `role`                 ENUM('super_admin','club_owner','club_member') NOT NULL DEFAULT 'club_member',
    `is_super_admin`       TINYINT(1)   NOT NULL DEFAULT 0,
    `status`               ENUM('active','suspended') NOT NULL DEFAULT 'active',
    `needs_password_setup` TINYINT(1)   NOT NULL DEFAULT 0,
    `last_login_at`        DATETIME     NULL,
    `created_at`           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_users_email` (`email`),
    KEY `idx_users_club` (`club_id`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Tokens (reset mot de passe / invitation collaborateur)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_tokens` (
    `id`         CHAR(36)    NOT NULL,
    `user_id`    CHAR(36)    NOT NULL,
    `token`      VARCHAR(64) NOT NULL,
    `type`       ENUM('reset','invitation') NOT NULL,
    `expires_at` DATETIME    NOT NULL,
    `used_at`    DATETIME    NULL,
    `created_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_password_tokens_token` (`token`),
    KEY `idx_password_tokens_user_type` (`user_id`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Catégories du menu (10 domaines + sous-catégories, 2 niveaux max)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
    `id`         CHAR(36)     NOT NULL,
    `parent_id`  CHAR(36)     NULL,
    `slug`       VARCHAR(191) NOT NULL,
    `name`       VARCHAR(255) NOT NULL,
    `position`   INT          NOT NULL DEFAULT 0,
    `icon`       VARCHAR(64)  NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_categories_slug` (`slug`),
    KEY `idx_categories_parent` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Ressources (vidéos, replays, masterclasses, fiches PDF, templates, podcasts)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resources` (
    `id`                    CHAR(36)     NOT NULL,
    `category_id`           CHAR(36)     NULL,
    `title`                 VARCHAR(500) NOT NULL,
    `slug`                  VARCHAR(191) NULL,
    `description`           TEXT         NULL,
    `format`                ENUM('video','replay_live','masterclass','pdf','template','podcast') NOT NULL DEFAULT 'video',
    `level`                 ENUM('fondamentaux','avance') NULL,
    `video_provider`        VARCHAR(32)  NULL,                -- 'vimeo'
    `video_id`              VARCHAR(64)  NULL,                -- id Vimeo
    `video_duration`        INT          NULL,                -- secondes
    `thumbnail_url`         VARCHAR(1000) NULL,
    `file_path`             VARCHAR(1000) NULL,               -- PDF/template stocké HORS webroot
    `file_name`             VARCHAR(255) NULL,
    `transcription`         LONGTEXT     NULL,
    `transcription_status`  ENUM('none','pending','done','error') NOT NULL DEFAULT 'none',
    `status`                ENUM('draft','published') NOT NULL DEFAULT 'draft',
    `is_spotlight`          TINYINT(1)   NOT NULL DEFAULT 0,  -- bandeau "Coup de projecteur"
    `vectorized_at`         DATETIME     NULL,
    `published_at`          DATETIME     NULL,
    `created_by`            CHAR(36)     NULL,
    `created_at`            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_resources_category` (`category_id`),
    KEY `idx_resources_status` (`status`),
    KEY `idx_resources_format` (`format`),
    KEY `idx_resources_spotlight` (`is_spotlight`),
    KEY `idx_resources_published` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Segments vectorisés (RAG) — recherche cosinus en PHP, vecteur packé en BLOB
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resource_segments` (
    `id`              CHAR(36)   NOT NULL,
    `resource_id`     CHAR(36)   NOT NULL,
    `category_id`     CHAR(36)   NULL,                        -- dupliqué pour pré-filtrer par catégorie
    `chunk_index`     INT        NOT NULL DEFAULT 0,
    `chunk_text`      MEDIUMTEXT NOT NULL,
    `token_count`     INT        NOT NULL DEFAULT 0,
    `embedding`       LONGBLOB   NULL,                        -- float32 packés (pack('g*', ...))
    `embedding_model` VARCHAR(64) NULL,
    `embedding_dim`   SMALLINT   NULL,
    `created_at`      DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_segments_resource` (`resource_id`),
    KEY `idx_segments_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Favoris membre
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `favorites` (
    `id`          CHAR(36) NOT NULL,
    `user_id`     CHAR(36) NOT NULL,
    `resource_id` CHAR(36) NOT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_favorites_user_resource` (`user_id`, `resource_id`),
    KEY `idx_favorites_resource` (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Historique de consultation + progression ("reprendre où j'en étais")
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `view_history` (
    `id`               CHAR(36) NOT NULL,
    `user_id`          CHAR(36) NOT NULL,
    `resource_id`      CHAR(36) NOT NULL,
    `progress_seconds` INT      NOT NULL DEFAULT 0,
    `completed`        TINYINT(1) NOT NULL DEFAULT 0,
    `last_viewed_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_view_history_user_resource` (`user_id`, `resource_id`),
    KEY `idx_view_history_user_seen` (`user_id`, `last_viewed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Agenda & replays (masterclasses, lives, visios)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `events` (
    `id`                 CHAR(36)     NOT NULL,
    `title`              VARCHAR(500) NOT NULL,
    `type`               ENUM('masterclass','live','visio') NOT NULL DEFAULT 'live',
    `description`        TEXT         NULL,
    `starts_at`          DATETIME     NOT NULL,
    `ends_at`            DATETIME     NULL,
    `replay_resource_id` CHAR(36)     NULL,                   -- lien vers la ressource une fois le replay dispo
    `created_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_events_starts` (`starts_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Chatbot IA — conversations et messages (logs Q/R, anonymisation à 3 mois)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `chat_conversations` (
    `id`         CHAR(36) NOT NULL,
    `user_id`    CHAR(36) NULL,                               -- NULL après anonymisation
    `club_id`    CHAR(36) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_chat_conv_user` (`user_id`),
    KEY `idx_chat_conv_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_messages` (
    `id`              CHAR(36) NOT NULL,
    `conversation_id` CHAR(36) NOT NULL,
    `role`            ENUM('user','assistant') NOT NULL,
    `content`         MEDIUMTEXT NOT NULL,
    `sources`         JSON     NULL,                          -- ids des ressources citées
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_chat_msg_conversation` (`conversation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Jobs asynchrones (vectorisation, transcription) — dépilés par le cron Amen
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sync_jobs` (
    `id`            CHAR(36) NOT NULL,
    `type`          ENUM('vectorize_resource','transcribe_resource','reindex') NOT NULL,
    `entity_id`     VARCHAR(64) NULL,
    `status`        ENUM('queued','running','completed','error') NOT NULL DEFAULT 'queued',
    `total`         INT      NOT NULL DEFAULT 0,
    `processed`     INT      NOT NULL DEFAULT 0,
    `percent`       TINYINT  NOT NULL DEFAULT 0,
    `message`       VARCHAR(500) NULL,
    `error_message` TEXT     NULL,
    `result`        JSON     NULL,
    `started_at`    DATETIME NULL,
    `finished_at`   DATETIME NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sync_jobs_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Journalisation des accès (RGPD §8 : connexions + accès aux contenus, 12 mois)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id`            CHAR(36) NOT NULL,
    `user_id`       CHAR(36) NULL,
    `club_id`       CHAR(36) NULL,
    `action`        VARCHAR(64) NOT NULL,                     -- 'login','view_resource','download_pdf','chat'…
    `entity_type`   VARCHAR(64) NULL,
    `entity_id`     VARCHAR(64) NULL,
    `details`       JSON     NULL,
    `ip`            VARCHAR(45) NULL,
    `status`        ENUM('success','error','warning') NOT NULL DEFAULT 'success',
    `error_message` TEXT     NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_activity_club_created` (`club_id`, `created_at`),
    KEY `idx_activity_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Consommation IA (coût API du chatbot / embeddings)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ai_usage_logs` (
    `id`                CHAR(36) NOT NULL,
    `club_id`           CHAR(36) NULL,
    `user_id`           CHAR(36) NULL,
    `provider`          VARCHAR(32) NOT NULL,
    `model`             VARCHAR(128) NULL,
    `prompt_tokens`     INT NOT NULL DEFAULT 0,
    `completion_tokens` INT NOT NULL DEFAULT 0,
    `total_tokens`      INT NOT NULL DEFAULT 0,
    `cost_eur`          DECIMAL(10,6) NOT NULL DEFAULT 0,
    `entity_type`       VARCHAR(64) NULL,
    `entity_id`         VARCHAR(64) NULL,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ai_usage_created` (`created_at`),
    KEY `idx_ai_usage_provider` (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Alertes back-office (erreurs de job, seuils…)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_alerts` (
    `id`         CHAR(36) NOT NULL,
    `type`       VARCHAR(64) NOT NULL,
    `message`    TEXT     NOT NULL,
    `read_at`    DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_admin_alerts_read` (`read_at`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;
