-- =============================================================================
-- Enrichissement des catégories (arborescence illimitée déjà permise par parent_id).
-- Propriétés : titre (name, existant), description courte, description longue,
-- image miniature, vidéo d'introduction (ID Vimeo).
-- =============================================================================

ALTER TABLE `categories`
    ADD COLUMN `short_description` VARCHAR(500)  NULL AFTER `name`,
    ADD COLUMN `long_description`  TEXT          NULL AFTER `short_description`,
    ADD COLUMN `thumbnail_url`     VARCHAR(1000) NULL AFTER `long_description`,
    ADD COLUMN `intro_video_id`    VARCHAR(64)   NULL AFTER `thumbnail_url`;
