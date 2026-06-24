<?php
// ─────────────────────────────────────────────────────────────────
// Atlas du Graphisme — Scraper Cooper Hewitt (Smithsonian Design Museum)
// API GraphQL publique : https://api.cooperhewitt.org/
// Remplit : image_3, image_4 (créées automatiquement si absentes)
//
// Usage CLI : php fetch_cooperhewitt.php
// Usage web : http://localhost/scraper/fetch_cooperhewitt.php
// ─────────────────────────────────────────────────────────────────

require_once __DIR__ . '/config.php';

if (PHP_SAPI !== 'cli' && ($_GET['token'] ?? '') !== SCRAPER_TOKEN) {
    http_response_code(403);
    exit('Accès refusé.');
}

$pdo = db();

// ── Migration silencieuse ────────────────────────────────────────
// Crée les colonnes image_3/image_4 si elles n'existent pas encore
foreach (['image_3', 'image_4'] as $col) {
    try {
        $pdo->exec("ALTER TABLE courants ADD COLUMN $col VARCHAR(500) DEFAULT NULL");
    } catch (\Exception $e) {
        // Colonne déjà présente — silencieux
    }
}

// ── Termes de recherche par slug ─────────────────────────────────
// Valeur par défaut = wikipedia_en. Surcharge ici si besoin.
const CH_OVERRIDES = [
    'psychedelic-poster'  => 'psychedelic poster',
    'new-typography'      => 'new typography modernist',
    'streamline'          => 'streamline moderne',
    'op-art'              => 'op art optical',
    'deconstruction-typo' => 'deconstruction typography',
    'flat-design'         => null,  // trop récent pour CH → skip
    'lo-fi-aesthetic'     => null,
    'synthwave'           => null,
    'material-design'     => null,
    'vaporwave'           => null,
    'brutalisme-web'      => null,
    'y2k'                 => null,
    'skeuomorphisme'      => null,
    'grunge'              => 'grunge graphic design',
];

// ── Requête GraphQL ──────────────────────────────────────────────
function ch_search(string $q, int $limit = 2): array {
    $gql = '{ object(general: "' . addslashes($q) . '", hasImages: true, size: ' . ($limit * 3) . ') {
        title classification media { large preview }
    }}';

    $ch = curl_init('https://api.cooperhewitt.org/');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => APP_ENV === 'local' ? false : true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['query' => $gql]),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'User-Agent: AtlasGraphisme/1.0',
        ],
    ]);
    $body = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http !== 200 || !$body) return [];

    $data = json_decode($body, true);
    if (isset($data['errors'])) return [];

    $items = $data['data']['object'] ?? [];

    // Préférer les objets classifiés graphic design
    usort($items, function ($a, $b) {
        $cls_a = strtolower($a['classification'][0]['summary']['title'] ?? '');
        $cls_b = strtolower($b['classification'][0]['summary']['title'] ?? '');
        $is_gd_a = str_contains($cls_a, 'graphic') || str_contains($cls_a, 'poster') || str_contains($cls_a, 'print') ? 0 : 1;
        $is_gd_b = str_contains($cls_b, 'graphic') || str_contains($cls_b, 'poster') || str_contains($cls_b, 'print') ? 0 : 1;
        return $is_gd_a - $is_gd_b;
    });

    $urls = [];
    foreach (array_slice($items, 0, $limit) as $obj) {
        $large   = $obj['media'][0]['large']   ?? null;
        $preview = $obj['media'][0]['preview'] ?? null;
        $url = (is_array($large)   ? $large['url']   : null)
            ?? (is_array($preview) ? $preview['url'] : null);
        if ($url) $urls[] = $url;
    }
    return $urls;
}

// ── UPDATE statement ─────────────────────────────────────────────
$stmt = $pdo->prepare(
    "UPDATE courants
     SET image_3 = COALESCE(:img3, image_3),
         image_4 = COALESCE(:img4, image_4),
         fetched_at = :now
     WHERE slug = :slug"
);

// ── Boucle principale ─────────────────────────────────────────────
foreach (COURANTS_CONFIG as $courant) {
    $slug = $courant['slug'];

    // Terme de recherche : override > wikipedia_en > skip
    if (array_key_exists($slug, CH_OVERRIDES)) {
        $q = CH_OVERRIDES[$slug]; // peut être null
    } else {
        $q = $courant['wikipedia_en'] ?? null;
    }

    if ($q === null) {
        echo "[CooperHewitt] $slug — skip (no search term)\n";
        continue;
    }

    $urls = ch_search($q, 2);

    $img3 = $urls[0] ?? null;
    $img4 = $urls[1] ?? null;

    $stmt->execute([':slug' => $slug, ':img3' => $img3, ':img4' => $img4, ':now' => db_now()]);

    $log = $img3 ? "[img3 ✓]" : "[img3 ✗]";
    $log .= $img4 ? " [img4 ✓]" : " [img4 ✗]";
    echo "[CooperHewitt] $slug — $log\n";

    sleep(2); // Respecter le rate limit
}

echo "\n✓ Scraping Cooper Hewitt terminé.\n";
