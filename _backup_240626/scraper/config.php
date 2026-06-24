<?php
// ─────────────────────────────────────────────────────────────────
// Atlas du Graphisme — Configuration PROD (OVH)
// ─────────────────────────────────────────────────────────────────

define('APP_ENV', getenv('APP_ENV') ?: 'prod');

// ── MySQL OVH ─────────────────────────────────────────────────────
define('DB_HOST',    'nothumanatlas.mysql.db');
define('DB_NAME',    'nothumanatlas');
define('DB_USER',    'nothumanatlas');
define('DB_PASS',    'GraphAtlas1234');
define('DB_CHARSET', 'utf8mb4');

// ── SQLite (non utilisé en prod, requis pour éviter une erreur) ───
define('SQLITE_PATH', __DIR__ . '/../database/atlas.db');

// ── Sécurité scrapers ─────────────────────────────────────────────
define('SCRAPER_TOKEN', 'AtlasRun2024');

// Retourne la date courante comme paramètre PHP — identique SQLite et MySQL
function db_now(): string { return date('Y-m-d H:i:s'); }

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

// ── Liste des courants ────────────────────────────────────────────
define('COURANTS_CONFIG', [
    ['slug' => 'arts-crafts',    'wikidata_id' => 'Q330369',   'wikipedia_en' => 'Arts and Crafts movement'],
    ['slug' => 'art-nouveau',    'wikidata_id' => 'Q34636',    'wikipedia_en' => 'Art Nouveau'],
    ['slug' => 'futurisme',      'wikidata_id' => null,         'wikipedia_en' => 'Futurism'],
    ['slug' => 'constructivisme','wikidata_id' => null,         'wikipedia_en' => 'Constructivism (art)'],
    ['slug' => 'de-stijl',       'wikidata_id' => null,         'wikipedia_en' => 'De Stijl'],
    ['slug' => 'bauhaus',        'wikidata_id' => 'Q124354',   'wikipedia_en' => 'Bauhaus'],
    ['slug' => 'art-deco',       'wikidata_id' => null,         'wikipedia_en' => 'Art Deco'],
    ['slug' => 'surrealisme',    'wikidata_id' => null,         'wikipedia_en' => 'Surrealism'],
    ['slug' => 'style-suisse',   'wikidata_id' => 'Q2140911',  'wikipedia_en' => 'International Typographic Style'],
    ['slug' => 'pop-art',        'wikidata_id' => 'Q134147',   'wikipedia_en' => 'Pop art'],
    ['slug' => 'vernacular',     'wikidata_id' => null,         'wikipedia_en' => 'Push Pin Studios'],
    ['slug' => 'psychedelique',  'wikidata_id' => 'Q696160',   'wikipedia_en' => 'Psychedelic art'],
    ['slug' => 'new-wave-typo',  'wikidata_id' => null,         'wikipedia_en' => 'Wolfgang Weingart'],
    ['slug' => 'pixel-art',      'wikidata_id' => null,         'wikipedia_en' => 'Pixel art'],
    ['slug' => 'postmodernisme', 'wikidata_id' => 'Q3318563',   'wikipedia_en' => 'Postmodern art'],
    ['slug' => 'memphis',        'wikidata_id' => null,         'wikipedia_en' => 'Memphis Group'],
    ['slug' => 'grunge',         'wikidata_id' => null,         'wikipedia_en' => 'Grunge typography'],
    ['slug' => 'y2k',            'wikidata_id' => null,         'wikipedia_en' => 'Y2K aesthetic'],
    ['slug' => 'skeuomorphisme', 'wikidata_id' => null,         'wikipedia_en' => 'Skeuomorph'],
    ['slug' => 'flat-design',    'wikidata_id' => 'Q15266360',  'wikipedia_en' => 'Flat design'],
    ['slug' => 'vaporwave',      'wikidata_id' => null,         'wikipedia_en' => 'Vaporwave'],
    ['slug' => 'brutalisme-web', 'wikidata_id' => null,         'wikipedia_en' => 'Brutalist web design'],

    // ── Sous-mouvements (niveau 2) ────────────────────────────────
    ['slug' => 'aesthetic-movement',  'wikidata_id' => 'Q1050297',  'wikipedia_en' => 'Aesthetic movement'],
    ['slug' => 'jugendstil',          'wikidata_id' => 'Q34627',    'wikipedia_en' => 'Jugendstil'],
    ['slug' => 'vorticism',           'wikidata_id' => 'Q193699',   'wikipedia_en' => 'Vorticism'],
    ['slug' => 'neoplasticisme',      'wikidata_id' => 'Q744239',   'wikipedia_en' => 'Neoplasticism'],
    ['slug' => 'new-typography',      'wikidata_id' => null,         'wikipedia_en' => 'Die neue Typographie'],
    ['slug' => 'ulm-school',          'wikidata_id' => 'Q680909',   'wikipedia_en' => 'Ulm School of Design'],
    ['slug' => 'streamline',          'wikidata_id' => 'Q185023',   'wikipedia_en' => 'Streamline Moderne'],
    ['slug' => 'op-art',              'wikidata_id' => 'Q80113',    'wikipedia_en' => 'Op art'],
    ['slug' => 'lettrisme',           'wikidata_id' => 'Q200654',   'wikipedia_en' => 'Lettrism'],
    ['slug' => 'psychedelic-poster',  'wikidata_id' => null,         'wikipedia_en' => 'Concert poster'],
    ['slug' => 'deconstruction-typo', 'wikidata_id' => null,         'wikipedia_en' => 'Deconstructivism'],
    ['slug' => 'neo-pop',             'wikidata_id' => null,         'wikipedia_en' => 'Neo-pop art'],
    ['slug' => 'material-design',     'wikidata_id' => 'Q17030296', 'wikipedia_en' => 'Material Design'],
    ['slug' => 'synthwave',           'wikidata_id' => 'Q27611341', 'wikipedia_en' => 'Synthwave'],
    ['slug' => 'lo-fi-aesthetic',     'wikidata_id' => null,         'wikipedia_en' => 'Lo-fi aesthetic'],
]);
