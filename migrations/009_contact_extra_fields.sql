-- =============================================================================
-- Champs supplémentaires du formulaire de contact :
--   first_name   = prénom du manager
--   club_address = adresse du club
-- (name = nom du manager, club = nom du club, déjà présents)
-- =============================================================================

ALTER TABLE `contact_messages`
    ADD COLUMN `first_name`   VARCHAR(255) NULL AFTER `name`,
    ADD COLUMN `club_address` VARCHAR(500) NULL AFTER `club`;
