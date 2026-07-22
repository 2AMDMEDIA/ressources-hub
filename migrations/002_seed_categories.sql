-- =============================================================================
-- Seed des 10 catégories du menu RESSOURCES (CDC §3).
-- Idempotent : INSERT IGNORE sur le slug (UNIQUE). Rejouable sans doublon.
-- Les UUID sont fixes pour garder des identifiants stables entre environnements.
-- =============================================================================

INSERT IGNORE INTO `categories` (`id`, `parent_id`, `slug`, `name`, `position`) VALUES
    ('a1000000-0000-4000-8000-000000000001', NULL, 'accueil',            'Accueil',              0),
    ('a1000000-0000-4000-8000-000000000002', NULL, 'vente',              'Vente',                1),
    ('a1000000-0000-4000-8000-000000000003', NULL, 'marketing',          'Marketing',            2),
    ('a1000000-0000-4000-8000-000000000004', NULL, 'fidelisation',       'Fidélisation',         3),
    ('a1000000-0000-4000-8000-000000000005', NULL, 'offre-services',     'Offre & Services',     4),
    ('a1000000-0000-4000-8000-000000000006', NULL, 'ressources-humaines','Ressources Humaines',  5),
    ('a1000000-0000-4000-8000-000000000007', NULL, 'pilotage-kpi',       'Pilotage & KPI',       6),
    ('a1000000-0000-4000-8000-000000000008', NULL, 'anticiper-demain',   'Anticiper Demain',     7),
    ('a1000000-0000-4000-8000-000000000009', NULL, 'creation-lancement', 'Création & Lancement', 8),
    ('a1000000-0000-4000-8000-00000000000a', NULL, 'masterclasses-lives','Masterclasses & Lives',9);
