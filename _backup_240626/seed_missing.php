<?php
// ─────────────────────────────────────────────────────────────────
// Atlas du Graphisme — Seed des 12 courants manquants + relations
// Exécuter UNE FOIS via navigateur, puis supprimer ce fichier.
// ─────────────────────────────────────────────────────────────────
header('Content-Type: text/plain; charset=utf-8');

define('APP_ENV', 'prod');
define('DB_HOST',    'nothumanatlas.mysql.db');
define('DB_NAME',    'nothumanatlas');
define('DB_USER',    'nothumanatlas');
define('DB_PASS',    'GraphAtlas1234');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✓ Connexion MySQL OK\n\n";
} catch (PDOException $e) {
    die("✗ ERREUR connexion : " . $e->getMessage() . "\n");
}

// ── 1. Autoriser NULL sur wikidata_id (plusieurs courants sans QID) ──
try {
    $pdo->exec("ALTER TABLE courants MODIFY wikidata_id VARCHAR(20) DEFAULT NULL");
    echo "✓ wikidata_id : NULL autorisé\n";
} catch (PDOException $e) {
    echo "  wikidata_id déjà nullable (ok)\n";
}

// ── 2. Courants manquants ──────────────────────────────────────────
// [slug, wikidata_id|null, nom, couleur, typo, debut, fin, px, py, pz]
$manquants = [
    ['futurisme',       'Q38054',    'Futurism',              '#ff6b35', '"Impact", sans-serif',               1909, 1944,  -5.0,  2.0,   2.2],
    ['constructivisme', 'Q80930',    'Constructivism',        '#cc3333', '"Arial", sans-serif',                1915, 1935,  -2.0, -1.5,   1.0],
    ['de-stijl',        'Q160830',   'De Stijl',              '#f7b500', '"Helvetica Neue", sans-serif',       1917, 1931,   2.5,  1.8,   0.6],
    ['art-deco',        'Q48584',    'Art Deco',              '#d4af37', '"Times New Roman", serif',           1920, 1940,   5.0,  0.8,  -0.4],
    ['surrealisme',     'Q35922',    'Surrealism',            '#6a0dad', '"Georgia", serif',                   1924, 1966,  -4.5,  1.8,  -1.0],
    ['vernacular',      null,        'Vernacular / Push Pin', '#e07b39', '"Trebuchet MS", sans-serif',         1950, 1980,   6.5,  2.5,  -6.0],
    ['new-wave-typo',   null,        'New Wave / Swiss Punk', '#3a86ff', '"Courier New", monospace',          1970, 1988,  -5.0, -0.8, -10.5],
    ['memphis',         'Q1755576',  'Memphis Group',         '#ff6b9d', '"Futura", sans-serif',               1981, 1988,   5.5, -0.8, -12.5],
    ['y2k',             null,        'Y2K Aesthetic',         '#00f5d4', '"Arial", sans-serif',                1995, 2005,   3.5,  1.5, -15.0],
    ['skeuomorphisme',  'Q899542',   'Skeuomorphism',         '#8ecae6', '"Helvetica Neue", sans-serif',       2000, 2013,   0.5,  1.0, -16.0],
    ['vaporwave',       'Q15869946', 'Vaporwave',             '#ff71ce', '"Courier New", monospace',           2010,  null,  4.5,  2.0, -18.5],
    ['brutalisme-web',  null,        'Web Brutalism',         '#333333', '"Courier New", monospace',           2015,  null, -4.0,  0.8, -19.5],
];

$stmt = $pdo->prepare("
    INSERT IGNORE INTO courants
        (slug, wikidata_id, nom, couleur_accent, typographie, periode_debut, periode_fin, pos_x, pos_y, pos_z)
    VALUES
        (:slug, :qid, :nom, :couleur, :typo, :debut, :fin, :px, :py, :pz)
");

$inserted = 0;
foreach ($manquants as [$slug, $qid, $nom, $couleur, $typo, $debut, $fin, $px, $py, $pz]) {
    $stmt->execute([
        ':slug'   => $slug,
        ':qid'    => $qid,
        ':nom'    => $nom,
        ':couleur'=> $couleur,
        ':typo'   => $typo,
        ':debut'  => $debut,
        ':fin'    => $fin,
        ':px'     => $px,
        ':py'     => $py,
        ':pz'     => $pz,
    ]);
    if ($stmt->rowCount()) {
        echo "  + $slug\n";
        $inserted++;
    } else {
        echo "  = $slug (déjà présent)\n";
    }
}
echo "✓ $inserted courants insérés\n\n";

// ── 3. Mettre à jour periode_debut/fin des 10 courants existants ──
$updates = [
    ['arts-crafts',    1880, 1910],
    ['art-nouveau',    1890, 1910],
    ['bauhaus',        1919, 1933],
    ['style-suisse',   1950, 1972],
    ['pop-art',        1955, 1972],
    ['psychedelique',  1965, 1975],
    ['postmodernisme', 1975, 1995],
    ['grunge',         1988, 2000],
    ['flat-design',    2010, 2020],
    ['pixel-art',      1975,  null],
];

$upd = $pdo->prepare("UPDATE courants SET periode_debut=:d, periode_fin=:f WHERE slug=:s AND (periode_debut IS NULL OR periode_debut=0)");
foreach ($updates as [$slug, $debut, $fin]) {
    $upd->execute([':s' => $slug, ':d' => $debut, ':f' => $fin]);
}
echo "✓ Dates mises à jour sur les 10 courants existants\n\n";

// ── 4. Relations ──────────────────────────────────────────────────
$ids = [];
foreach ($pdo->query("SELECT id, slug FROM courants") as $r) {
    $ids[$r['slug']] = (int)$r['id'];
}
echo "Courants en base : " . count($ids) . "\n";

$relations = [
    ['arts-crafts',     'art-nouveau',    'influence'],
    ['arts-crafts',     'bauhaus',        'influence'],
    ['art-nouveau',     'art-deco',       'influence'],
    ['art-nouveau',     'bauhaus',        'influence'],
    ['futurisme',       'constructivisme','influence'],
    ['futurisme',       'art-deco',       'influence'],
    ['constructivisme', 'bauhaus',        'influence'],
    ['constructivisme', 'de-stijl',       'contemporain'],
    ['de-stijl',        'bauhaus',        'influence'],
    ['de-stijl',        'style-suisse',   'influence'],
    ['surrealisme',     'pop-art',        'influence'],
    ['surrealisme',     'psychedelique',  'influence'],
    ['surrealisme',     'postmodernisme', 'influence'],
    ['bauhaus',         'style-suisse',   'derivation'],
    ['bauhaus',         'pop-art',        'influence'],
    ['bauhaus',         'constructivisme','contemporain'],
    ['style-suisse',    'new-wave-typo',  'opposition'],
    ['style-suisse',    'flat-design',    'derivation'],
    ['style-suisse',    'postmodernisme', 'opposition'],
    ['pop-art',         'psychedelique',  'contemporain'],
    ['pop-art',         'postmodernisme', 'influence'],
    ['pop-art',         'memphis',        'influence'],
    ['vernacular',      'postmodernisme', 'influence'],
    ['psychedelique',   'grunge',         'influence'],
    ['new-wave-typo',   'postmodernisme', 'influence'],
    ['new-wave-typo',   'grunge',         'influence'],
    ['postmodernisme',  'memphis',        'contemporain'],
    ['postmodernisme',  'grunge',         'influence'],
    ['postmodernisme',  'brutalisme-web', 'influence'],
    ['memphis',         'y2k',            'influence'],
    ['memphis',         'flat-design',    'opposition'],
    ['art-deco',        'memphis',        'influence'],
    ['grunge',          'flat-design',    'opposition'],
    ['pixel-art',       'y2k',            'contemporain'],
    ['pixel-art',       'vaporwave',      'influence'],
    ['y2k',             'vaporwave',      'influence'],
    ['skeuomorphisme',  'flat-design',    'opposition'],
    ['flat-design',     'brutalisme-web', 'opposition'],
    ['vaporwave',       'brutalisme-web', 'contemporain'],
    ['pixel-art',       'skeuomorphisme', 'contemporain'],
];

$rel_stmt = $pdo->prepare("
    INSERT IGNORE INTO courant_relations (source_id, cible_id, type_relation)
    VALUES (:src, :cib, :type)
");

$rel_count = 0;
foreach ($relations as [$src, $cib, $type]) {
    if (!isset($ids[$src], $ids[$cib])) {
        echo "  [warn] slug inconnu : $src ou $cib\n";
        continue;
    }
    $rel_stmt->execute([':src' => $ids[$src], ':cib' => $ids[$cib], ':type' => $type]);
    $rel_count += $rel_stmt->rowCount();
}
echo "✓ $rel_count relations insérées\n\n";

// ── 5. Résumé final ───────────────────────────────────────────────
$total   = $pdo->query("SELECT COUNT(*) FROM courants")->fetchColumn();
$rels    = $pdo->query("SELECT COUNT(*) FROM courant_relations")->fetchColumn();
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Total courants  : $total / 22\n";
echo "Total relations : $rels\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "→ Supprimer ce fichier une fois terminé.\n";
