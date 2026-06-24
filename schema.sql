-- ─────────────────────────────────────────────────────────────────
-- Atlas du Graphisme — Schéma MySQL
-- ─────────────────────────────────────────────────────────────────

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ── Table principale ─────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `courants` (
  `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `wikidata_id`       VARCHAR(20)  NOT NULL UNIQUE COMMENT 'QID Wikidata ex: Q124354',
  `wikipedia_titre`   VARCHAR(200) DEFAULT NULL   COMMENT 'Titre article Wikipédia FR',
  `slug`              VARCHAR(120) NOT NULL UNIQUE,
  `nom`               VARCHAR(150) NOT NULL,

  -- Contenu texte
  `description_courte` TEXT        DEFAULT NULL COMMENT 'Courte description Wikidata (fr)',
  `description_longue` LONGTEXT    DEFAULT NULL COMMENT 'Extrait Wikipedia (fr)',

  -- Dates
  `periode_debut`     SMALLINT     DEFAULT NULL,
  `periode_fin`       SMALLINT     DEFAULT NULL,

  -- Images
  `image_wikidata`    VARCHAR(500) DEFAULT NULL COMMENT 'URL image Wikimedia Commons (P18)',
  `image_wikipedia`   VARCHAR(500) DEFAULT NULL COMMENT 'URL thumbnail article Wikipedia',

  -- Données éditoriales
  `artistes`          TEXT         DEFAULT NULL COMMENT 'JSON array des artistes représentatifs',
  `key_points`        TEXT         DEFAULT NULL COMMENT 'Description des principes visuels clés',

  -- DA éditorial (valeurs manuelles)
  `couleur_accent`    VARCHAR(7)   DEFAULT '#888888' COMMENT 'Hex color ex: #e63946',
  `typographie`       VARCHAR(100) DEFAULT '"Helvetica Neue", sans-serif',
  `mots_cles`         TEXT         DEFAULT NULL COMMENT 'JSON array de mots-clés',
  `principes_visuels` TEXT         DEFAULT NULL,

  -- Position dans la scène 3D (éditorial)
  `pos_x`             FLOAT        DEFAULT 0,
  `pos_y`             FLOAT        DEFAULT 0,
  `pos_z`             FLOAT        DEFAULT 0,
  `niveau`            TINYINT      DEFAULT 1 COMMENT '1=principal 2=branche 3=feuille',

  -- Métadonnées
  `created_at`        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fetched_at`        TIMESTAMP    DEFAULT NULL COMMENT 'Dernière synchro API'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── Artistes ─────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `artistes` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `wikidata_id`     VARCHAR(20)  DEFAULT NULL UNIQUE,
  `nom`             VARCHAR(150) NOT NULL,
  `slug`            VARCHAR(120) NOT NULL UNIQUE,
  `naissance`       SMALLINT     DEFAULT NULL,
  `deces`           SMALLINT     DEFAULT NULL,
  `nationalite`     VARCHAR(80)  DEFAULT NULL,
  `bio_courte`      TEXT         DEFAULT NULL,
  `image`           VARCHAR(500) DEFAULT NULL,
  `created_at`      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── Objets visuels ───────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `objets_visuels` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `courant_id`  INT UNSIGNED NOT NULL,
  `titre`       VARCHAR(200) DEFAULT NULL,
  `type`        ENUM('affiche','couverture','logo','typographie','photo','objet','autre') DEFAULT 'autre',
  `artiste_id`  INT UNSIGNED DEFAULT NULL,
  `annee`       SMALLINT     DEFAULT NULL,
  `source`      VARCHAR(255) DEFAULT NULL COMMENT 'URL source originale',
  `image`       VARCHAR(500) DEFAULT NULL,
  `legende`     TEXT         DEFAULT NULL,
  FOREIGN KEY (`courant_id`) REFERENCES `courants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`artiste_id`) REFERENCES `artistes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── Relations entre courants ─────────────────────────────────────

CREATE TABLE IF NOT EXISTS `courant_relations` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `source_id`      INT UNSIGNED NOT NULL,
  `cible_id`       INT UNSIGNED NOT NULL,
  `type_relation`  ENUM('influence','opposition','derivation','contemporain') DEFAULT 'influence',
  `label`          VARCHAR(100) DEFAULT NULL COMMENT 'Libellé optionnel ex: "réaction contre"',
  UNIQUE KEY `unique_relation` (`source_id`, `cible_id`, `type_relation`),
  FOREIGN KEY (`source_id`) REFERENCES `courants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`cible_id`)  REFERENCES `courants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ── Lien courants ↔ artistes ─────────────────────────────────────

CREATE TABLE IF NOT EXISTS `courant_artistes` (
  `courant_id` INT UNSIGNED NOT NULL,
  `artiste_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`courant_id`, `artiste_id`),
  FOREIGN KEY (`courant_id`) REFERENCES `courants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`artiste_id`) REFERENCES `artistes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────────────────────────
-- Données de départ — les 10 courants du prototype
-- (positions 3D et valeurs éditoriales pré-remplies)
-- ─────────────────────────────────────────────────────────────────

INSERT IGNORE INTO `courants`
  (`wikidata_id`, `wikipedia_titre`, `slug`, `nom`, `couleur_accent`, `typographie`, `pos_x`, `pos_y`, `pos_z`, `niveau`)
VALUES
  ('Q330369', 'Arts and Crafts',                 'arts-crafts',      'Arts & Crafts',          '#c9a84c', '"Georgia", serif',                          -3,   0.5,   2,  1),
  ('Q34636',  'Art nouveau',                     'art-nouveau',      'Art Nouveau',             '#7aab6e', '"Palatino Linotype", "Palatino", serif',     3,    1,     0,  1),
  ('Q124354', 'Bauhaus',                         'bauhaus',          'Bauhaus',                 '#e63946', '"Helvetica Neue", "Arial", sans-serif',      0,    0,    -4,  1),
  ('Q2140911','Style typographique international','style-suisse',     'Style International',     '#1d6fa4', '"Helvetica Neue", "Helvetica", sans-serif', -2,  -0.5,  -8,  1),
  ('Q134147', 'Pop art',                         'pop-art',          'Pop Art',                 '#ffb703', '"Impact", "Arial Black", sans-serif',        4,    1,    -7,  1),
  ('Q696160', 'Art psychédélique',               'psychedelique',    'Psychédélique',           '#9b5de5', '"Trebuchet MS", cursive',                    5,    2,   -10,  1),
  ('Q3318563','Art postmoderne',                 'postmodernisme',   'Postmodernisme',           '#f72585', '"Times New Roman", serif',                   2,   -1,   -13,  1),
  (NULL,       NULL,                             'grunge',           'Grunge Graphique',         '#c49a6c', '"Courier New", monospace',                  -3,  -1.5, -16,  1),
  ('Q15266360','Design plat',                    'flat-design',      'Flat Design',             '#00b4d8', '"Segoe UI", "Roboto", sans-serif',            0,    0,   -20,  1),
  ('Q811179', 'Pixel art',                       'pixel-art',        'Pixel Art',               '#06d6a0', '"Courier New", monospace',                  -5,   1.5, -11,  1);

-- ── Sous-mouvements (niveau 2) ────────────────────────────────────
INSERT IGNORE INTO `courants`
  (`wikidata_id`, `wikipedia_titre`, `slug`, `nom`, `couleur_accent`, `typographie`, `pos_x`, `pos_y`, `pos_z`, `niveau`)
VALUES
  ('Q1050297', 'Aesthetic movement',         'aesthetic-movement',  'Aesthetic Movement',        '#b8860b', '"Palatino Linotype", serif',          -4.5,  1.5,   7.5, 2),
  ('Q34627',   'Jugendstil',                 'jugendstil',          'Jugendstil',                '#8fbc8f', '"Palatino Linotype", serif',           5.0,  2.2,   5.5, 2),
  ('Q193699',  'Vorticism',                  'vorticism',           'Vorticism',                 '#ff4500', '"Impact", sans-serif',                -6.5,  3.0,   1.8, 2),
  ('Q744239',  'Neoplasticism',              'neoplasticisme',      'Néoplasticisme',             '#f7c500', '"Helvetica Neue", sans-serif',         4.0,  2.8,   0.2, 2),
  (NULL,        NULL,                        'new-typography',      'New Typography',             '#d62828', '"Helvetica Neue", sans-serif',         1.5,  0.5,  -0.5, 2),
  ('Q680909',  'Ulm School of Design',       'ulm-school',          'École d''Ulm',              '#457b9d', '"Helvetica Neue", sans-serif',        -1.5,  0.8,  -1.2, 2),
  ('Q185023',  'Streamline Moderne',         'streamline',          'Streamline Moderne',         '#cd9b1d', '"Times New Roman", serif',             6.5,  1.8,  -1.0, 2),
  ('Q80113',   'Op art',                     'op-art',              'Op Art',                    '#2d6a4f', '"Helvetica Neue", sans-serif',        -4.0,  0.0,  -7.5, 2),
  ('Q200654',  'Lettrism',                   'lettrisme',           'Lettrisme',                 '#7b2d8b', '"Courier New", monospace',            -6.0,  2.8,  -1.5, 2),
  (NULL,        NULL,                        'psychedelic-poster',  'Affiches Psychédéliques',    '#c77dff', '"Trebuchet MS", cursive',              7.0,  3.8, -10.5, 2),
  (NULL,        NULL,                        'deconstruction-typo', 'Déconstruction Typo',        '#e056a0', '"Times New Roman", serif',             3.5, -2.5, -12.5, 2),
  (NULL,        NULL,                        'neo-pop',             'Neo-Pop',                   '#ffc300', '"Impact", sans-serif',                 6.0,  2.2,  -8.0, 2),
  ('Q17030296','Material Design',            'material-design',     'Material Design',            '#03a9f4', '"Roboto", sans-serif',                 0.5,  0.5, -18.5, 2),
  ('Q27611341','Synthwave',                  'synthwave',           'Synthwave',                 '#f72585', '"Courier New", monospace',              6.0,  3.0, -19.0, 2),
  (NULL,        NULL,                        'lo-fi-aesthetic',     'Lo-fi Aesthetic',            '#b392ac', '"Courier New", monospace',              3.0,  3.5, -19.5, 2);

-- ── Relations parent → sous-mouvements ───────────────────────────
INSERT IGNORE INTO `courant_relations` (`source_id`, `cible_id`, `type_relation`)
SELECT s.id, c.id, 'derivation'
FROM `courants` s JOIN `courants` c ON 1=1
WHERE (s.slug = 'arts-crafts'    AND c.slug = 'aesthetic-movement')
   OR (s.slug = 'art-nouveau'    AND c.slug = 'jugendstil')
   OR (s.slug = 'futurisme'      AND c.slug = 'vorticism')
   OR (s.slug = 'de-stijl'       AND c.slug = 'neoplasticisme')
   OR (s.slug = 'bauhaus'        AND c.slug = 'new-typography')
   OR (s.slug = 'bauhaus'        AND c.slug = 'ulm-school')
   OR (s.slug = 'art-deco'       AND c.slug = 'streamline')
   OR (s.slug = 'style-suisse'   AND c.slug = 'op-art')
   OR (s.slug = 'surrealisme'    AND c.slug = 'lettrisme')
   OR (s.slug = 'psychedelique'  AND c.slug = 'psychedelic-poster')
   OR (s.slug = 'postmodernisme' AND c.slug = 'deconstruction-typo')
   OR (s.slug = 'pop-art'        AND c.slug = 'neo-pop')
   OR (s.slug = 'flat-design'    AND c.slug = 'material-design')
   OR (s.slug = 'vaporwave'      AND c.slug = 'synthwave')
   OR (s.slug = 'vaporwave'      AND c.slug = 'lo-fi-aesthetic');

