<?php
// ─────────────────────────────────────────────────────────────────
// Atlas du Graphisme — SQLite init (22 styles, EN data)
// Drops + recreates all data. Safe to re-run.
// Usage : php scraper/init_sqlite.php
// ─────────────────────────────────────────────────────────────────

require_once __DIR__ . '/config.php';

// Delete existing DB so we start fresh
if (file_exists(SQLITE_PATH)) {
    unlink(SQLITE_PATH);
    echo "Dropped existing DB.\n";
}

$pdo = db();
echo "SQLite: " . SQLITE_PATH . "\n\n";

// ─────────────────────────────────────────────────────────────────
// 1. Tables
// ─────────────────────────────────────────────────────────────────

$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS courants (
  id                  INTEGER PRIMARY KEY AUTOINCREMENT,
  wikidata_id         TEXT    UNIQUE,
  wikipedia_titre     TEXT,
  slug                TEXT    NOT NULL UNIQUE,
  nom                 TEXT    NOT NULL,
  description_courte  TEXT,
  description_longue  TEXT,
  periode_debut       INTEGER,
  periode_fin         INTEGER,
  image_wikidata      TEXT,
  image_wikipedia     TEXT,
  couleur_accent      TEXT    DEFAULT '#888888',
  typographie         TEXT    DEFAULT '"Helvetica Neue", sans-serif',
  mots_cles           TEXT,
  principes_visuels   TEXT,
  pos_x               REAL    DEFAULT 0,
  pos_y               REAL    DEFAULT 0,
  pos_z               REAL    DEFAULT 0,
  niveau              INTEGER DEFAULT 1,
  created_at          TEXT    DEFAULT (datetime('now')),
  updated_at          TEXT    DEFAULT (datetime('now')),
  fetched_at          TEXT
);
CREATE TABLE IF NOT EXISTS artistes (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  wikidata_id TEXT    UNIQUE,
  nom         TEXT    NOT NULL,
  slug        TEXT    NOT NULL UNIQUE,
  naissance   INTEGER,
  deces       INTEGER,
  nationalite TEXT,
  bio_courte  TEXT,
  image       TEXT,
  created_at  TEXT    DEFAULT (datetime('now'))
);
CREATE TABLE IF NOT EXISTS objets_visuels (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  courant_id  INTEGER NOT NULL REFERENCES courants(id) ON DELETE CASCADE,
  titre       TEXT,
  type        TEXT    DEFAULT 'autre',
  artiste_id  INTEGER REFERENCES artistes(id) ON DELETE SET NULL,
  annee       INTEGER,
  source      TEXT,
  image       TEXT,
  legende     TEXT
);
CREATE TABLE IF NOT EXISTS courant_relations (
  id              INTEGER PRIMARY KEY AUTOINCREMENT,
  source_id       INTEGER NOT NULL REFERENCES courants(id) ON DELETE CASCADE,
  cible_id        INTEGER NOT NULL REFERENCES courants(id) ON DELETE CASCADE,
  type_relation   TEXT    DEFAULT 'influence',
  label           TEXT,
  UNIQUE (source_id, cible_id, type_relation)
);
CREATE TABLE IF NOT EXISTS courant_artistes (
  courant_id  INTEGER NOT NULL REFERENCES courants(id) ON DELETE CASCADE,
  artiste_id  INTEGER NOT NULL REFERENCES artistes(id) ON DELETE CASCADE,
  PRIMARY KEY (courant_id, artiste_id)
);
SQL);
echo "✓ Tables created.\n";

// ─────────────────────────────────────────────────────────────────
// 2. Styles
// Position: Z = temporal axis using formula z = 8 - (year - 1880) * 0.2
// X / Y = editorial scatter for constellation feel
// Format: [wikidata_id, slug, nom, couleur, typo, debut, fin, px, py, pz]
// ─────────────────────────────────────────────────────────────────

$courants = [
  // slug                  nom                        couleur    typographie                          debut  fin   px     py     pz
  ['arts-crafts',    'Q330369', 'Arts & Crafts',              '#c9a84c', '"Georgia", serif',                   1880, 1910,  -3.0,  0.5,   8.0],
  ['art-nouveau',    'Q34636',  'Art Nouveau',                '#7aab6e', '"Palatino Linotype", serif',          1890, 1910,   3.5,  1.2,   6.0],
  ['futurisme',      'Q38054',  'Futurism',                   '#ff6b35', '"Impact", sans-serif',                1909, 1944,  -5.0,  2.0,   2.2],
  ['constructivisme','Q80930',  'Constructivism',             '#cc3333', '"Arial", sans-serif',                 1915, 1935,  -2.0, -1.5,   1.0],
  ['de-stijl',       'Q160830', 'De Stijl',                   '#f7b500', '"Helvetica Neue", sans-serif',        1917, 1931,   2.5,  1.8,   0.6],
  ['bauhaus',        'Q124354', 'Bauhaus',                    '#e63946', '"Helvetica Neue", sans-serif',        1919, 1933,   0.0, -0.5,   0.2],
  ['art-deco',       'Q48584',  'Art Deco',                   '#d4af37', '"Times New Roman", serif',            1920, 1940,   5.0,  0.8,  -0.4],
  ['surrealisme',    'Q35922',  'Surrealism',                 '#6a0dad', '"Georgia", serif',                    1924, 1966,  -4.5,  1.8,  -1.0],
  ['style-suisse',   'Q2140911','Swiss Style',                '#1d6fa4', '"Helvetica Neue", sans-serif',        1950, 1972,  -2.5, -1.0,  -7.0],
  ['pop-art',        'Q134147', 'Pop Art',                    '#ffb703', '"Impact", sans-serif',                1955, 1972,   4.5,  1.2,  -7.5],
  ['vernacular',     null,      'Vernacular / Push Pin',      '#e07b39', '"Trebuchet MS", sans-serif',          1950, 1980,   6.5,  2.5,  -6.0],
  ['psychedelique',  'Q696160', 'Psychedelic',                '#9b5de5', '"Trebuchet MS", cursive',             1965, 1975,   5.5,  2.8, -10.0],
  ['new-wave-typo',  null,      'New Wave / Swiss Punk',      '#3a86ff', '"Courier New", monospace',            1970, 1988,  -5.0, -0.8, -10.5],
  ['pixel-art',      null,      'Pixel Art',                  '#06d6a0', '"Courier New", monospace',            1975, 9999,  -6.0,  1.5, -11.0],
  ['postmodernisme', 'Q3318563','Postmodernism',               '#f72585', '"Times New Roman", serif',            1975, 1995,   2.0, -1.5, -12.0],
  ['memphis',        'Q1755576','Memphis Group',               '#ff6b9d', '"Futura", sans-serif',                1981, 1988,   5.5, -0.8, -12.5],
  ['grunge',         null,      'Grunge Typography',           '#c49a6c', '"Courier New", monospace',            1988, 2000,  -3.5, -2.5, -14.0],
  ['y2k',            null,      'Y2K Aesthetic',               '#00f5d4', '"Arial", sans-serif',                 1995, 2005,   3.5,  1.5, -15.0],
  ['skeuomorphisme', 'Q899542', 'Skeuomorphism',               '#8ecae6', '"Helvetica Neue", sans-serif',        2000, 2013,   0.5,  1.0, -16.0],
  ['flat-design',    'Q15266360','Flat Design',                '#00b4d8', '"Segoe UI", sans-serif',              2010, 2020,  -1.0, -0.5, -18.0],
  ['vaporwave',      'Q15869946','Vaporwave',                  '#ff71ce', '"Courier New", monospace',            2010, 9999,   4.5,  2.0, -18.5],
  ['brutalisme-web', null,      'Web Brutalism',               '#333333', '"Courier New", monospace',            2015, 9999,  -4.0,  0.8, -19.5],
];

$stmt = $pdo->prepare(<<<SQL
  INSERT OR IGNORE INTO courants
    (wikidata_id, slug, nom, couleur_accent, typographie, periode_debut, periode_fin, pos_x, pos_y, pos_z)
  VALUES
    (:qid, :slug, :nom, :couleur, :typo, :debut, :fin, :px, :py, :pz)
SQL);

foreach ($courants as [$slug, $qid, $nom, $couleur, $typo, $debut, $fin, $px, $py, $pz]) {
    $stmt->execute([
        ':qid'    => $qid,
        ':slug'   => $slug,
        ':nom'    => $nom,
        ':couleur'=> $couleur,
        ':typo'   => $typo,
        ':debut'  => $debut === 9999 ? null : $debut,
        ':fin'    => $fin  === 9999 ? null : $fin,
        ':px'     => $px,
        ':py'     => $py,
        ':pz'     => $pz,
    ]);
}
echo "✓ " . count($courants) . " styles inserted.\n";

// ─────────────────────────────────────────────────────────────────
// 3. Relations — historically grounded
// ─────────────────────────────────────────────────────────────────

$ids = [];
foreach ($pdo->query("SELECT id, slug FROM courants") as $r) {
    $ids[$r['slug']] = (int)$r['id'];
}

// [source, target, type]
// types: influence | derivation | opposition | contemporain
$relations = [
    // Arts & Crafts era
    ['arts-crafts',    'art-nouveau',   'influence'],
    ['arts-crafts',    'bauhaus',       'influence'],

    // Avant-gardes feeding each other
    ['art-nouveau',    'art-deco',      'influence'],
    ['art-nouveau',    'bauhaus',       'influence'],
    ['futurisme',      'constructivisme','influence'],
    ['futurisme',      'art-deco',      'influence'],
    ['constructivisme','bauhaus',       'influence'],
    ['constructivisme','de-stijl',      'contemporain'],
    ['de-stijl',       'bauhaus',       'influence'],
    ['de-stijl',       'style-suisse',  'influence'],
    ['surrealisme',    'pop-art',       'influence'],
    ['surrealisme',    'psychedelique', 'influence'],
    ['surrealisme',    'postmodernisme','influence'],

    // Bauhaus lineage
    ['bauhaus',        'style-suisse',  'derivation'],
    ['bauhaus',        'pop-art',       'influence'],
    ['bauhaus',        'constructivisme','contemporain'],

    // Swiss Style and reactions
    ['style-suisse',   'new-wave-typo', 'opposition'],
    ['style-suisse',   'flat-design',   'derivation'],
    ['style-suisse',   'postmodernisme','opposition'],

    // Pop Art era
    ['pop-art',        'psychedelique', 'contemporain'],
    ['pop-art',        'postmodernisme','influence'],
    ['pop-art',        'memphis',       'influence'],
    ['vernacular',     'postmodernisme','influence'],

    // Counter-culture into postmodernism
    ['psychedelique',  'grunge',        'influence'],
    ['new-wave-typo',  'postmodernisme','influence'],
    ['new-wave-typo',  'grunge',        'influence'],

    // Postmodern era
    ['postmodernisme', 'memphis',       'contemporain'],
    ['postmodernisme', 'grunge',        'influence'],
    ['postmodernisme', 'brutalisme-web','influence'],
    ['memphis',        'y2k',           'influence'],
    ['memphis',        'flat-design',   'opposition'],
    ['art-deco',       'memphis',       'influence'],

    // Digital transitions
    ['grunge',         'flat-design',   'opposition'],
    ['pixel-art',      'y2k',           'contemporain'],
    ['pixel-art',      'vaporwave',     'influence'],
    ['y2k',            'vaporwave',     'influence'],
    ['skeuomorphisme', 'flat-design',   'opposition'],
    ['flat-design',    'brutalisme-web','opposition'],
    ['vaporwave',      'brutalisme-web','contemporain'],

    // Pixel art cross-era
    ['pixel-art',      'skeuomorphisme','contemporain'],
];

$rel_stmt = $pdo->prepare(<<<SQL
  INSERT OR IGNORE INTO courant_relations (source_id, cible_id, type_relation)
  VALUES (:src, :cib, :type)
SQL);

$count = 0;
foreach ($relations as [$src, $cib, $type]) {
    if (!isset($ids[$src], $ids[$cib])) {
        echo "  [warn] unknown slug: $src or $cib\n";
        continue;
    }
    $rel_stmt->execute([':src' => $ids[$src], ':cib' => $ids[$cib], ':type' => $type]);
    $count += $rel_stmt->rowCount();
}

echo "✓ $count relations inserted.\n\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Next:\n";
echo "  php scraper/fetch_wikidata.php\n";
echo "  php scraper/fetch_wikipedia.php\n";
echo "  http://localhost:8080/api/courants.php\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
