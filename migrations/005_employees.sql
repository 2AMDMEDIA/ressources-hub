-- =============================================================================
-- Employés d'un club (équipe). Un club peut avoir plusieurs employés.
-- Distinct du manager (compte de connexion unique) : un employé est une fiche
-- de l'équipe, sans compte ni connexion.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `employees` (
    `id`         CHAR(36)     NOT NULL,
    `club_id`    CHAR(36)     NOT NULL,
    `first_name` VARCHAR(255) NOT NULL,
    `last_name`  VARCHAR(255) NOT NULL,
    `email`      VARCHAR(255) NULL,
    `job_title`  VARCHAR(255) NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_employees_club` (`club_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
