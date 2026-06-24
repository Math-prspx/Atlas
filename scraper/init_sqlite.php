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
  image_3             TEXT,
  image_4             TEXT,
  image_5             TEXT,
  artistes            TEXT,
  key_points          TEXT,
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
  ['arts-crafts',    'Q330369', 'Arts & Crafts',              '#c9a84c', '"Georgia", serif',                   1880, 1910,  -3.0,  0.5,   8.0, 1],
  ['art-nouveau',    'Q34636',  'Art Nouveau',                '#7aab6e', '"Palatino Linotype", serif',          1890, 1910,   3.5,  1.2,   6.0, 1],
  ['futurisme',      'Q38054',  'Futurism',                   '#ff6b35', '"Impact", sans-serif',                1909, 1944,  -5.0,  2.0,   2.2, 1],
  ['constructivisme','Q80930',  'Constructivism',             '#cc3333', '"Arial", sans-serif',                 1915, 1935,  -2.0, -1.5,   1.0, 1],
  ['de-stijl',       'Q160830', 'De Stijl',                   '#f7b500', '"Helvetica Neue", sans-serif',        1917, 1931,   2.5,  1.8,   0.6, 1],
  ['bauhaus',        'Q124354', 'Bauhaus',                    '#e63946', '"Helvetica Neue", sans-serif',        1919, 1933,   0.0, -0.5,   0.2, 1],
  ['art-deco',       'Q48584',  'Art Deco',                   '#d4af37', '"Times New Roman", serif',            1920, 1940,   5.0,  0.8,  -0.4, 1],
  ['surrealisme',    'Q35922',  'Surrealism',                 '#6a0dad', '"Georgia", serif',                    1924, 1966,  -4.5,  1.8,  -1.0, 1],
  ['style-suisse',   'Q2140911','Swiss Style',                '#1d6fa4', '"Helvetica Neue", sans-serif',        1950, 1972,  -2.5, -1.0,  -7.0, 1],
  ['pop-art',        'Q134147', 'Pop Art',                    '#ffb703', '"Impact", sans-serif',                1955, 1972,   4.5,  1.2,  -7.5, 1],
  ['vernacular',     null,      'Vernacular / Push Pin',      '#e07b39', '"Trebuchet MS", sans-serif',          1950, 1980,   6.5,  2.5,  -6.0, 1],
  ['psychedelique',  'Q696160', 'Psychedelic',                '#9b5de5', '"Trebuchet MS", cursive',             1965, 1975,   5.5,  2.8, -10.0, 1],
  ['new-wave-typo',  null,      'New Wave / Swiss Punk',      '#3a86ff', '"Courier New", monospace',            1970, 1988,  -5.0, -0.8, -10.5, 1],
  ['pixel-art',      null,      'Pixel Art',                  '#06d6a0', '"Courier New", monospace',            1975, 9999,  -6.0,  1.5, -11.0, 1],
  ['postmodernisme', 'Q3318563','Postmodernism',               '#f72585', '"Times New Roman", serif',            1975, 1995,   2.0, -1.5, -12.0, 1],
  ['memphis',        'Q1755576','Memphis Group',               '#ff6b9d', '"Futura", sans-serif',                1981, 1988,   5.5, -0.8, -12.5, 1],
  ['grunge',         null,      'Grunge Typography',           '#c49a6c', '"Courier New", monospace',            1988, 2000,  -3.5, -2.5, -14.0, 1],
  ['y2k',            null,      'Y2K Aesthetic',               '#00f5d4', '"Arial", sans-serif',                 1995, 2005,   3.5,  1.5, -15.0, 1],
  ['skeuomorphisme', 'Q899542', 'Skeuomorphism',               '#8ecae6', '"Helvetica Neue", sans-serif',        2000, 2013,   0.5,  1.0, -16.0, 1],
  ['flat-design',    'Q15266360','Flat Design',                '#00b4d8', '"Segoe UI", sans-serif',              2010, 2020,  -1.0, -0.5, -18.0, 1],
  ['vaporwave',      'Q15869946','Vaporwave',                  '#ff71ce', '"Courier New", monospace',            2010, 9999,   4.5,  2.0, -18.5, 1],
  ['brutalisme-web', null,      'Web Brutalism',               '#333333', '"Courier New", monospace',            2015, 9999,  -4.0,  0.8, -19.5, 1],

  // ── Sous-mouvements (niveau 2) ─────────────────────────────────
  // Z corrigé : même formule que niveau 1 → z = 8 - (debut - 1880) * 0.2
  ['aesthetic-movement',  'Q1050297',  'Aesthetic Movement',         '#b8860b', '"Palatino Linotype", serif',          1868, 1901, -4.5,  1.5, 10.4, 2],
  ['jugendstil',          'Q34627',    'Jugendstil',                 '#8fbc8f', '"Palatino Linotype", serif',          1896, 1910,  5.0,  2.2,  4.8, 2],
  ['vorticism',           'Q193699',   'Vorticism',                  '#ff4500', '"Impact", sans-serif',                1914, 1920, -6.5,  3.0,  1.2, 2],
  ['neoplasticisme',      'Q744239',   'Néoplasticisme',              '#f7c500', '"Helvetica Neue", sans-serif',        1917, 1931,  4.0,  2.8,  0.6, 2],
  ['new-typography',      null,        'New Typography',              '#d62828', '"Helvetica Neue", sans-serif',        1928, 1950,  1.5,  0.5, -1.6, 2],
  ['ulm-school',          'Q680909',   'École d\'Ulm',               '#457b9d', '"Helvetica Neue", sans-serif',        1953, 1968, -1.5,  0.8, -6.6, 2],
  ['streamline',          'Q185023',   'Streamline Moderne',          '#cd9b1d', '"Times New Roman", serif',            1930, 1950,  6.5,  1.8, -2.0, 2],
  ['op-art',              'Q80113',    'Op Art',                      '#2d6a4f', '"Helvetica Neue", sans-serif',        1964, 1975, -4.0,  0.0, -8.8, 2],
  ['lettrisme',           'Q200654',   'Lettrisme',                   '#7b2d8b', '"Courier New", monospace',            1945, 1975, -6.0,  2.8, -5.0, 2],
  ['psychedelic-poster',  null,        'Affiches Psychédéliques',     '#c77dff', '"Trebuchet MS", cursive',             1966, 1972,  7.0,  3.8, -9.2, 2],
  ['deconstruction-typo', null,        'Déconstruction Typo',         '#e056a0', '"Times New Roman", serif',            1988, 1998,  3.5, -2.5,-13.6, 2],
  ['neo-pop',             null,        'Neo-Pop',                     '#ffc300', '"Impact", sans-serif',                1980, 1995,  6.0,  2.2,-12.0, 2],
  ['material-design',     'Q17030296', 'Material Design',             '#03a9f4', '"Roboto", sans-serif',                2014, 9999,  0.5,  0.5,-18.8, 2],
  ['synthwave',           'Q27611341', 'Synthwave',                   '#f72585', '"Courier New", monospace',            2012, 9999,  6.0,  3.0,-18.4, 2],
  ['lo-fi-aesthetic',     null,        'Lo-fi Aesthetic',             '#b392ac', '"Courier New", monospace',            2015, 9999,  3.0,  3.5,-19.0, 2],
];

$stmt = $pdo->prepare(<<<SQL
  INSERT OR IGNORE INTO courants
    (wikidata_id, slug, nom, couleur_accent, typographie, periode_debut, periode_fin, pos_x, pos_y, pos_z, niveau)
  VALUES
    (:qid, :slug, :nom, :couleur, :typo, :debut, :fin, :px, :py, :pz, :niveau)
SQL);

foreach ($courants as [$slug, $qid, $nom, $couleur, $typo, $debut, $fin, $px, $py, $pz, $niveau]) {
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
        ':niveau' => $niveau,
    ]);
}
echo "✓ " . count($courants) . " styles inserted.\n";

// ─────────────────────────────────────────────────────────────────
// 3. Artistes + Key points — données éditoriales par courant
// artistes : JSON array  |  key_points : texte descriptif
// ─────────────────────────────────────────────────────────────────

$editorial = [
  'arts-crafts'          => [['William Morris','Walter Crane','Charles Voysey'],            'Organic motifs, medieval inspiration, decorative typography, rejection of industrial production.'],
  'art-nouveau'          => [['Alphonse Mucha','Hector Guimard','Gustav Klimt'],             'Sinuous lines, floral motifs, soft colours, ornate typography, unity of form and ornament.'],
  'futurisme'            => [['Umberto Boccioni','Giacomo Balla','Filippo Marinetti'],       'Dynamic diagonals, speed lines, fragmented forms, aggressive typography, glorification of machines.'],
  'constructivisme'      => [['El Lissitzky','Alexander Rodchenko','Varvara Stepanova'],     'Red & black, geometric forms, photomontage, propaganda function, design as social tool.'],
  'de-stijl'             => [['Piet Mondrian','Theo van Doesburg','Gerrit Rietveld'],        'Primary colours only, horizontal/vertical lines, reduction to essence, total artwork vision.'],
  'bauhaus'              => [['Herbert Bayer','László Moholy-Nagy','Paul Klee','Wassily Kandinsky'], 'Grid, pure geometry, primary colours, sans-serif, form follows function, art meets craft.'],
  'art-deco'             => [['A.M. Cassandre','Paul Colin','Erté'],                         'Geometric luxury, symmetry, bold contrast, streamline shapes, rich materials, speed as elegance.'],
  'surrealisme'          => [['Salvador Dalí','René Magritte','Max Ernst','Man Ray'],        'Dream imagery, unexpected juxtapositions, automatism, subconscious symbolism, uncanny beauty.'],
  'style-suisse'         => [['Josef Müller-Brockmann','Armin Hofmann','Emil Ruder','Max Bill'], 'Mathematical grid, Helvetica, photography, generous whitespace, objective visual communication.'],
  'pop-art'              => [['Andy Warhol','Roy Lichtenstein','Richard Hamilton'],          'Saturated colours, Ben-Day dots, thick outlines, everyday subjects, irony, mass culture celebration.'],
  'vernacular'           => [['Milton Glaser','Seymour Chwast','Push Pin Studios'],          'Eclectic historical references, illustration, humour, emotional directness, anti-Swiss rebellion.'],
  'psychedelique'        => [['Wes Wilson','Victor Moscoso','Rick Griffin'],                 'Acid colours, warped type, biomorphic shapes, extreme saturation, perceptual overload.'],
  'new-wave-typo'        => [['Wolfgang Weingart','April Greiman','Dan Friedman'],           'Type as texture, layering, diagonal compositions, deliberate imperfection, postmodern formalism.'],
  'pixel-art'            => [['Shigeru Miyamoto','Paul Robertson','Mark Ferrari'],           'Pixel grid, limited palette, dithering, intentional aliasing, nostalgia as aesthetic.'],
  'postmodernisme'       => [['Wolfgang Weingart','David Carson','Paula Scher','Neville Brody'], 'Layering, mixed typefaces, grid deconstruction, collage, excess, irony, historical eclecticism.'],
  'memphis'              => [['Ettore Sottsass','Nathalie Du Pasquier','George Sowden'],     'Bold patterns, clashing colours, playful geometry, anti-functionalism, kitsch as culture.'],
  'grunge'               => [['David Carson','Barry Deck','Ed Fella'],                       'Distressed type, raw textures, photocopier aesthetic, apparent chaos, anti-legibility.'],
  'y2k'                  => [['Designers Republic','Joshua Davis'],                          'Chrome, gradients, lens flare, translucency, techno-optimism, millennium fever.'],
  'skeuomorphisme'       => [['Susan Kare','Jonathan Ive'],                                  'Realistic textures, drop shadows, bevels, real-world metaphors, tactile visual language.'],
  'flat-design'          => [['Jonathan Ive','Material Design — Google'],                    'Flat colours, no shadows, geometric icons, bold sans-serif, generous white space, clarity first.'],
  'vaporwave'            => [['Macintosh Plus','Saint Pepsi','James Ferraro'],               'Pastel neons, glitch, Roman busts, retro 3D, nostalgic digital, commodity culture irony.'],
  'brutalisme-web'       => [['Pascal Deville','Hass & Hahn'],                              'Raw HTML feel, visible structure, system fonts, uncomfortable contrast, anti-design as manifesto.'],

  // Sous-mouvements
  'aesthetic-movement'   => [['James Abbott McNeill Whistler','Aubrey Beardsley','Edward Burne-Jones'], 'Art for art\'s sake, japonisme influence, ornamental refinement, beauty as the only moral purpose.'],
  'jugendstil'           => [['Franz von Stuck','Otto Eckmann','Peter Behrens'],             'Organic abstraction, floral patterns, magazine design, German variant of Art Nouveau.'],
  'vorticism'            => [['Wyndham Lewis','Edward Wadsworth','William Roberts'],         'Geometric angularity, harsh diagonals, machine age imagery, anti-sentimentalism, British futurism.'],
  'neoplasticisme'       => [['Piet Mondrian','Theo van Doesburg','Bart van der Leck'],      'Reduction to primary colours + black/white, horizontal/vertical only, pure plastic abstraction.'],
  'new-typography'       => [['Jan Tschichold','Herbert Bayer','László Moholy-Nagy'],        'Asymmetric layout, sans-serif type, hierarchy via weight, white space as active compositional element.'],
  'ulm-school'           => [['Otl Aicher','Max Bill','Tomás Maldonado'],                    'Systematic design, ergonomics, modular grids, design as social responsibility, rigor above expression.'],
  'streamline'           => [['Raymond Loewy','Norman Bel Geddes','Henry Dreyfuss'],         'Aerodynamic curves, chrome, industrial optimism, speed as aesthetic, machine as protagonist.'],
  'lettrisme'            => [['Isidore Isou','Gil J. Wolman','Maurice Lemaître'],            'Letters as pure visual elements, sound poetry, anti-narrative, radical typographic experimentation.'],
  'op-art'               => [['Bridget Riley','Victor Vasarely','Richard Anuszkiewicz'],     'Optical illusions, geometric patterns, vibrating contrasts, perception as subject matter.'],
  'psychedelic-poster'   => [['Wes Wilson','Victor Moscoso','Rick Griffin','Stanley Mouse'], 'Melting type, high-contrast complementaries, hand-lettering, concert poster tradition.'],
  'deconstruction-typo'  => [['David Carson','Ed Fella','Jeffery Keedy'],                    'Illegibility as aesthetic, overlapping type, broken grids, reading as visual experience.'],
  'neo-pop'              => [['Jeff Koons','Keith Haring','Kenny Scharf'],                   'Pop iconography revisited, irony, consumer culture critique, bright palettes, mass appeal.'],
  'material-design'      => [['Matías Duarte','Google Design Team'],                         'Paper metaphor, elevation shadows, motion as feedback, responsive grid, system coherence.'],
  'synthwave'            => [['Kavinsky','FM-84','Trevor Something'],                        'Neon grids, retro-futurist palette, 80s nostalgia, VHS aesthetics, chrome typography.'],
  'lo-fi-aesthetic'      => [['ChilledCow','college','Nujabes'],                             'Vinyl grain, warm tones, anime aesthetic, imperfect nostalgia, study-mood atmosphere.'],
];

$upd = $pdo->prepare("UPDATE courants SET artistes = :a, key_points = :k WHERE slug = :s");
foreach ($editorial as $slug => [$artists, $kp]) {
    $upd->execute([':a' => json_encode($artists, JSON_UNESCAPED_UNICODE), ':k' => $kp, ':s' => $slug]);
}
echo "✓ artistes + key_points seeded for " . count($editorial) . " courants.\n\n";
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

    // ── Relations parent → sous-mouvements (niveau 2) ────────────
    ['arts-crafts',    'aesthetic-movement',  'derivation'],
    ['art-nouveau',    'jugendstil',          'derivation'],
    ['futurisme',      'vorticism',           'derivation'],
    ['de-stijl',       'neoplasticisme',      'derivation'],
    ['bauhaus',        'new-typography',      'derivation'],
    ['bauhaus',        'ulm-school',          'derivation'],
    ['art-deco',       'streamline',          'derivation'],
    ['style-suisse',   'op-art',              'derivation'],
    ['surrealisme',    'lettrisme',           'derivation'],
    ['psychedelique',  'psychedelic-poster',  'derivation'],
    ['postmodernisme', 'deconstruction-typo', 'derivation'],
    ['pop-art',        'neo-pop',             'derivation'],
    ['flat-design',    'material-design',     'derivation'],
    ['vaporwave',      'synthwave',           'derivation'],
    ['vaporwave',      'lo-fi-aesthetic',     'derivation'],
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
