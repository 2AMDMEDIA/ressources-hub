-- =============================================================================
-- Messages du formulaire de contact public (site vitrine).
-- =============================================================================

CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id`         CHAR(36)     NOT NULL,
    `name`       VARCHAR(255) NOT NULL,
    `email`      VARCHAR(255) NOT NULL,
    `phone`      VARCHAR(64)  NULL,
    `club`       VARCHAR(255) NULL,
    `subject`    VARCHAR(255) NULL,
    `message`    TEXT         NOT NULL,
    `status`     ENUM('new','read','archived') NOT NULL DEFAULT 'new',
    `ip`         VARCHAR(45)  NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_contact_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
