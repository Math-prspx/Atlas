<?php
// ─────────────────────────────────────────────────────────────────
// Atlas du Graphisme — Scraper Wikidata
// Récupère : nom FR, description FR, dates, image P18, influences
//
// Usage CLI : php fetch_wikidata.php
// Usage web : http://localhost/scraper/fetch_wikidata.php
// ─────────────────────────────────────────────────────────────────

require_once __DIR__ . '/config.php';

// ── 1. Construire la requête SPARQL ──────────────────────────────

$qids = array_filter(array_column(COURANTS_CONFIG, 'wikidata_id'));
$values = implode(' ', array_map(fn($q) => "wd:$q", $qids));

$sparql = <<<SPARQL
SELECT DISTINCT ?mvt ?label ?desc ?inception ?dissolution ?image ?influenceQID ?influenceLabel WHERE {
  VALUES ?mvt { $values }
  OPTIONAL { ?mvt rdfs:label ?label FILTER(LANG(?label) = "en") }
  OPTIONAL { ?mvt schema:description ?desc FILTER(LANG(?desc) = "en") }
  OPTIONAL { ?mvt wdt:P571 ?inception }
  OPTIONAL { ?mvt wdt:P576 ?dissolution }
  OPTIONAL { ?mvt wdt:P18  ?image }
  OPTIONAL {
    ?mvt wdt:P737 ?influenceQID .
    ?influenceQID rdfs:label ?influenceLabel FILTER(LANG(?influenceLabel) = "en")
  }
}
SPARQL;

// ── 2. Appel API Wikidata SPARQL ─────────────────────────────────

$url  = 'https://query.wikidata.org/sparql?format=json&query=' . urlencode($sparql);
$opts = [
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: AtlasGraphisme/1.0 (contact@example.com)\r\n",
        'timeout' => 30,
    ],
];
$json = file_get_contents($url, false, stream_context_create($opts));

if ($json === false) {
    die("Erreur : impossible de contacter l'API Wikidata.\n");
}

$data = json_decode($json, true);
if (!isset($data['results']['bindings'])) {
    die("Erreur : réponse Wikidata invalide.\n");
}

// ── 3. Parser les résultats ───────────────────────────────────────

$resultats = [];   // [qid => [...fields, 'influences' => []]]

foreach ($data['results']['bindings'] as $row) {
    $qid = basename($row['mvt']['value']);   // ex: Q124354

    if (!isset($resultats[$qid])) {
        $resultats[$qid] = [
            'wikidata_id'        => $qid,
            'nom'                => null,
            'description_courte' => null,
            'periode_debut'      => null,
            'periode_fin'        => null,
            'image_wikidata'     => null,
            'influences'         => [],
        ];
    }

    $r = &$resultats[$qid];

    if (!$r['nom'] && isset($row['label']))
        $r['nom'] = $row['label']['value'];

    if (!$r['description_courte'] && isset($row['desc']))
        $r['description_courte'] = $row['desc']['value'];

    if (!$r['periode_debut'] && isset($row['inception']))
        $r['periode_debut'] = (int) substr($row['inception']['value'], 0, 4);

    if (!$r['periode_fin'] && isset($row['dissolution']))
        $r['periode_fin'] = (int) substr($row['dissolution']['value'], 0, 4);

    // Image : convertir URL Commons Special:FilePath en URL directe
    if (!$r['image_wikidata'] && isset($row['image'])) {
        $r['image_wikidata'] = str_replace(
            'http://commons.wikimedia.org/wiki/Special:FilePath/',
            'https://commons.wikimedia.org/wiki/Special:FilePath/',
            $row['image']['value']
        );
    }

    if (isset($row['influenceLabel'])) {
        $inf = $row['influenceLabel']['value'];
        if (!in_array($inf, $r['influences'])) {
            $r['influences'][] = $inf;
        }
    }
}

// ── 4. Upsert en base MySQL ───────────────────────────────────────

$pdo = db();

$sql = <<<SQL
UPDATE courants SET
  description_courte = COALESCE(:desc, description_courte),
  periode_debut      = COALESCE(:debut, periode_debut),
  periode_fin        = COALESCE(:fin, periode_fin),
  image_wikidata     = COALESCE(:img, image_wikidata),
  fetched_at         = :now
WHERE wikidata_id = :qid
SQL;

$stmt = $pdo->prepare($sql);

$updated = 0;
foreach ($resultats as $qid => $r) {
    $stmt->execute([
        ':qid'  => $qid,
        ':desc' => $r['description_courte'],
        ':debut'=> $r['periode_debut'],
        ':fin'  => $r['periode_fin'],
        ':img'  => $r['image_wikidata'],
        ':now'  => db_now(),
    ]);
    $updated += $stmt->rowCount();

    echo "[Wikidata] $qid — {$r['nom']}"
        . ($r['image_wikidata'] ? " [img ✓]" : " [img ✗]")
        . "\n";
}

echo "\n✓ $updated courant(s) mis à jour depuis Wikidata.\n";
