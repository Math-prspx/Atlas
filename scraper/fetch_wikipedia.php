<?php
// ─────────────────────────────────────────────────────────────────
// Atlas du Graphisme — Scraper Wikipedia FR
// Récupère : extrait long (description_longue) + image thumbnail
//
// Usage CLI : php fetch_wikipedia.php
// Usage web : http://localhost/scraper/fetch_wikipedia.php
// ─────────────────────────────────────────────────────────────────

require_once __DIR__ . '/config.php';

$pdo = db();

$sql_update = <<<SQL
UPDATE courants SET
  description_longue = COALESCE(:extract, description_longue),
  image_wikipedia    = COALESCE(:img, image_wikipedia),
  fetched_at         = datetime('now')
WHERE slug = :slug
SQL;

$stmt = $pdo->prepare($sql_update);

foreach (COURANTS_CONFIG as $courant) {
    $slug    = $courant['slug'];
    $titre   = $courant['wikipedia_en'];

    if ($titre === null) {
        echo "[Wikipedia] $slug — no English Wikipedia title configured, skipped.\n";
        continue;
    }

    // ── Wikipedia REST API (English) ──────────────────────────────
    $url = 'https://en.wikipedia.org/api/rest_v1/page/summary/' . rawurlencode($titre);
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: AtlasGraphisme/1.0 (contact@example.com)\r\n",
            'timeout' => 15,
        ],
    ];

    $json = @file_get_contents($url, false, stream_context_create($opts));

    if ($json === false) {
        echo "[Wikipedia] $slug — ✗ Impossible de contacter l'API.\n";
        continue;
    }

    $data = json_decode($json, true);

    if (isset($data['type']) && $data['type'] === 'https://mediawiki.org/wiki/HyperSwitch/errors/not_found') {
        echo "[Wikipedia] $slug — ✗ Article introuvable : $titre\n";
        continue;
    }

    // ── Extraire les champs utiles ────────────────────────────────

    // Texte long : extract (peut contenir du HTML)
    $extract = $data['extract'] ?? null;

    // Image : thumbnail ou originalimage
    $img = $data['originalimage']['source']
        ?? $data['thumbnail']['source']
        ?? null;

    // ── Upsert en base ────────────────────────────────────────────
    $stmt->execute([
        ':slug'    => $slug,
        ':extract' => $extract,
        ':img'     => $img,
    ]);

    $has_img     = $img     ? "[img ✓]"  : "[img ✗]";
    $has_extract = $extract ? "[txt ✓]"  : "[txt ✗]";
    echo "[Wikipedia] $slug — $has_extract $has_img\n";
}

echo "\n✓ Scraping Wikipedia terminé.\n";
