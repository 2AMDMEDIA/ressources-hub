-- =============================================================================
-- Page « Nos experts » administrable.
-- Une seule table `experts` avec un type (`kind`) :
--   - 'founder' : les fondateurs (bloc du haut, titre au-dessus de la vignette)
--   - 'team'    : l'équipe / les consultants (grille de cartes)
-- Mêmes champs pour les deux. Photo uploadée optionnelle (sinon initiales).
-- Seed : reprend les données actuellement en dur (Bertrand Lataste + 3 profils).
-- =============================================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `experts` (
    `id`         CHAR(36)      NOT NULL,
    `kind`       ENUM('founder','team') NOT NULL DEFAULT 'team',
    `title`      VARCHAR(255)  NULL,          -- titre affiché au-dessus de la vignette
    `name`       VARCHAR(255)  NOT NULL,
    `role`       VARCHAR(255)  NULL,
    `bio`        TEXT          NULL,
    `photo_url`  VARCHAR(1000) NULL,          -- photo uploadée ; sinon avatar initiales
    `phone`      VARCHAR(50)   NULL,
    `email`      VARCHAR(255)  NULL,
    `accent`     VARCHAR(20)   NULL,          -- couleur de l'avatar de repli (steel/navy/orange)
    `position`   INT           NOT NULL DEFAULT 0,
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_experts_kind_pos` (`kind`, `position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `experts` (`id`, `kind`, `title`, `name`, `role`, `bio`, `phone`, `email`, `accent`, `position`) VALUES
('11111111-1111-4111-8111-111111111111', 'founder', 'Fondateur',
 'Bertrand Lataste', 'Fondateur & référent RESSOURCES — Fitness Challenges',
 'Plus de 12 ans d''accompagnement des professionnels du fitness en France, Belgique et Suisse. Point de contact privilégié de votre accompagnement.',
 '06 76 20 95 12', 'ressources@fitness-challenges.com', 'navy', 0),

('22222222-2222-4222-8222-222222222222', 'team', NULL,
 'Camille Roussel', 'Experte Vente & Développement commercial',
 'Ancienne directrice de réseau, elle structure les process de vente et la montée en compétence des équipes terrain.',
 NULL, NULL, 'steel', 0),

('33333333-3333-4333-8333-333333333333', 'team', NULL,
 'Thomas Bianchi', 'Expert Marketing & Acquisition',
 'Spécialiste de la communication locale et de l''acquisition, il aide les clubs à remplir durablement leur pipeline de prospects.',
 NULL, NULL, 'navy', 1),

('44444444-4444-4444-8444-444444444444', 'team', NULL,
 'Sarah Mendes', 'Experte Fidélisation & Expérience membre',
 'Elle conçoit les parcours d''onboarding et de rétention pour réduire les résiliations et améliorer le NPS.',
 NULL, NULL, 'orange', 2);
