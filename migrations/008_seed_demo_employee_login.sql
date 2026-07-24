-- =============================================================================
-- DONNÉES DE DÉMO — compte employé (club_member) pour tester la connexion.
-- Lucas Bernard (employé du club Alpha Fitness) reçoit un accès.
-- Mot de passe : « demo1234 ». À retirer avant la vraie mise en production.
-- =============================================================================

INSERT IGNORE INTO `users`
    (`id`, `club_id`, `email`, `password_hash`, `full_name`, `first_name`, `last_name`, `job_title`, `role`, `is_super_admin`, `status`, `needs_password_setup`)
VALUES
    ('d0000000-0000-4000-8000-00000000d004', 'd0000000-0000-4000-8000-00000000c001',
     'lucas.bernard@club-alpha.demo', '$2y$12$jIJ9tSzdcTkP3ALTnRN/ducuvnnT4g55wGTFeOqA49tCnrAGwRcmG',
     'Lucas Bernard', 'Lucas', 'Bernard', 'Coach', 'club_member', 0, 'active', 0);

-- Relier la fiche employé Lucas à son compte
UPDATE `employees`
   SET `user_id` = 'd0000000-0000-4000-8000-00000000d004'
 WHERE `id` = 'd0000000-0000-4000-8000-00000000f001';
