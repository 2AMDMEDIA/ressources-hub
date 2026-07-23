-- =============================================================================
-- Refonte modèle Club + Manager (1 club ⇄ 1 manager via table de liaison).
-- - Champs établissement sur `clubs`
-- - Champs identité sur `users` (prénom/nom/fonction)
-- - Table de liaison `club_managers` (source de vérité du lien club↔manager)
-- =============================================================================

ALTER TABLE `clubs`
    ADD COLUMN `siret`        VARCHAR(14)  NULL AFTER `name`,
    ADD COLUMN `address`      VARCHAR(500) NULL AFTER `siret`,
    ADD COLUMN `postal_code`  VARCHAR(20)  NULL AFTER `address`,
    ADD COLUMN `city`         VARCHAR(255) NULL AFTER `postal_code`,
    ADD COLUMN `country`      VARCHAR(100) NULL DEFAULT 'France' AFTER `city`,
    ADD COLUMN `area_sqm`     INT          NULL AFTER `country`,
    ADD COLUMN `opening_year` SMALLINT     NULL AFTER `area_sqm`;

ALTER TABLE `users`
    ADD COLUMN `first_name` VARCHAR(255) NULL AFTER `full_name`,
    ADD COLUMN `last_name`  VARCHAR(255) NULL AFTER `first_name`,
    ADD COLUMN `job_title`  VARCHAR(255) NULL AFTER `last_name`;

CREATE TABLE IF NOT EXISTS `club_managers` (
    `id`         CHAR(36) NOT NULL,
    `club_id`    CHAR(36) NOT NULL,
    `user_id`    CHAR(36) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_club_managers_club` (`club_id`),
    UNIQUE KEY `uniq_club_managers_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration des liens existants (owner_user_id) vers la table de liaison
INSERT IGNORE INTO `club_managers` (`id`, `club_id`, `user_id`)
    SELECT UUID(), `id`, `owner_user_id` FROM `clubs` WHERE `owner_user_id` IS NOT NULL;

-- owner_user_id n'est plus la source de vérité (remplacé par club_managers)
ALTER TABLE `clubs` DROP COLUMN `owner_user_id`;
