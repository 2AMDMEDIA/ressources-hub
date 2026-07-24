-- =============================================================================
-- DONNÉES DE DÉMO (fictives) — 3 clubs + 3 managers + employés.
-- Managers : mot de passe « demo1234 » (déjà défini, connexion immédiate).
-- À SUPPRIMER avant la mise en production réelle (voir cleanup en bas, commenté).
-- Idempotent : UUID fixes + INSERT IGNORE.
-- =============================================================================

-- Clubs -----------------------------------------------------------------------
INSERT IGNORE INTO `clubs`
    (`id`, `name`, `siret`, `address`, `postal_code`, `city`, `country`, `area_sqm`, `opening_year`, `status`)
VALUES
    ('d0000000-0000-4000-8000-00000000c001', 'Alpha Fitness',   '82012345600017', '15 rue de la République', '13001', 'Marseille', 'France', 850,  2018, 'active'),
    ('d0000000-0000-4000-8000-00000000c002', 'Beta Forme',      '82012345600025', '4 avenue Jean Jaurès',    '69007', 'Lyon',      'France', 1200, 2015, 'active'),
    ('d0000000-0000-4000-8000-00000000c003', 'Gamma Move Club', '82012345600033', '28 boulevard Voltaire',   '75011', 'Paris',     'France', 600,  2021, 'active');

-- Managers (comptes de connexion, role club_owner) ----------------------------
-- password_hash = bcrypt de « demo1234 »
INSERT IGNORE INTO `users`
    (`id`, `club_id`, `email`, `password_hash`, `full_name`, `first_name`, `last_name`, `job_title`, `role`, `is_super_admin`, `status`, `needs_password_setup`)
VALUES
    ('d0000000-0000-4000-8000-00000000d001', 'd0000000-0000-4000-8000-00000000c001', 'sophie.martin@club-alpha.demo', '$2y$12$jIJ9tSzdcTkP3ALTnRN/ducuvnnT4g55wGTFeOqA49tCnrAGwRcmG', 'Sophie Martin', 'Sophie', 'Martin', 'Directrice',  'club_owner', 0, 'active', 0),
    ('d0000000-0000-4000-8000-00000000d002', 'd0000000-0000-4000-8000-00000000c002', 'karim.haddad@club-beta.demo',   '$2y$12$jIJ9tSzdcTkP3ALTnRN/ducuvnnT4g55wGTFeOqA49tCnrAGwRcmG', 'Karim Haddad',  'Karim',  'Haddad', 'Gérant',      'club_owner', 0, 'active', 0),
    ('d0000000-0000-4000-8000-00000000d003', 'd0000000-0000-4000-8000-00000000c003', 'emma.leroy@club-gamma.demo',    '$2y$12$jIJ9tSzdcTkP3ALTnRN/ducuvnnT4g55wGTFeOqA49tCnrAGwRcmG', 'Emma Leroy',    'Emma',   'Leroy',  'Responsable', 'club_owner', 0, 'active', 0);

-- Liaison club ⇄ manager ------------------------------------------------------
INSERT IGNORE INTO `club_managers` (`id`, `club_id`, `user_id`) VALUES
    ('d0000000-0000-4000-8000-00000000e001', 'd0000000-0000-4000-8000-00000000c001', 'd0000000-0000-4000-8000-00000000d001'),
    ('d0000000-0000-4000-8000-00000000e002', 'd0000000-0000-4000-8000-00000000c002', 'd0000000-0000-4000-8000-00000000d002'),
    ('d0000000-0000-4000-8000-00000000e003', 'd0000000-0000-4000-8000-00000000c003', 'd0000000-0000-4000-8000-00000000d003');

-- Employés (équipe des clubs) -------------------------------------------------
INSERT IGNORE INTO `employees` (`id`, `club_id`, `first_name`, `last_name`, `email`, `job_title`) VALUES
    ('d0000000-0000-4000-8000-00000000f001', 'd0000000-0000-4000-8000-00000000c001', 'Lucas',  'Bernard', 'lucas.bernard@club-alpha.demo', 'Coach'),
    ('d0000000-0000-4000-8000-00000000f002', 'd0000000-0000-4000-8000-00000000c001', 'Nadia',  'Cherif',  'nadia.cherif@club-alpha.demo',  'Accueil'),
    ('d0000000-0000-4000-8000-00000000f003', 'd0000000-0000-4000-8000-00000000c002', 'Thomas', 'Petit',   'thomas.petit@club-beta.demo',   'Coach sportif'),
    ('d0000000-0000-4000-8000-00000000f004', 'd0000000-0000-4000-8000-00000000c002', 'Julie',  'Roche',   'julie.roche@club-beta.demo',    'Conseillère commerciale'),
    ('d0000000-0000-4000-8000-00000000f005', 'd0000000-0000-4000-8000-00000000c003', 'Marc',   'Dubois',  'marc.dubois@club-gamma.demo',   'Coach'),
    ('d0000000-0000-4000-8000-00000000f006', 'd0000000-0000-4000-8000-00000000c003', 'Léa',    'Moreau',  'lea.moreau@club-gamma.demo',    'Accueil'),
    ('d0000000-0000-4000-8000-00000000f007', 'd0000000-0000-4000-8000-00000000c003', 'Paul',   'Girard',  'paul.girard@club-gamma.demo',   'Maintenance');

-- =============================================================================
-- CLEANUP (à exécuter manuellement dans phpMyAdmin pour retirer la démo) :
--   DELETE FROM employees     WHERE id LIKE 'd0000000-0000-4000-8000-0000000%';
--   DELETE FROM club_managers WHERE id LIKE 'd0000000-0000-4000-8000-0000000%';
--   DELETE FROM users         WHERE id LIKE 'd0000000-0000-4000-8000-0000000%';
--   DELETE FROM clubs         WHERE id LIKE 'd0000000-0000-4000-8000-0000000%';
-- =============================================================================
