-- =============================================================================
-- Ordre d'affichage manuel des ressources (drag-and-drop dans le back-office).
-- Colonne `position` : plus petit = affiché en premier. Initialisée par catégorie
-- selon l'ordre d'affichage actuel (spotlight puis date de publication/création).
-- =============================================================================

SET NAMES utf8mb4;

ALTER TABLE `resources`
    ADD COLUMN `position` INT NOT NULL DEFAULT 0 AFTER `is_spotlight`,
    ADD KEY `idx_resources_position` (`category_id`, `position`);

UPDATE `resources` r
JOIN (
    SELECT `id`,
           ROW_NUMBER() OVER (
               PARTITION BY `category_id`
               ORDER BY `is_spotlight` DESC, COALESCE(`published_at`, `created_at`) DESC
           ) - 1 AS rn
    FROM `resources`
) t ON t.`id` = r.`id`
SET r.`position` = t.rn;
