-- =============================================================================
-- Accès de connexion pour les employés.
-- Un employé peut être relié à un compte membre (users, role club_member) qui
-- lui donne accès à l'espace membre (ressources uniquement pour l'instant).
-- =============================================================================

ALTER TABLE `employees`
    ADD COLUMN `user_id` CHAR(36) NULL AFTER `club_id`,
    ADD KEY `idx_employees_user` (`user_id`);
