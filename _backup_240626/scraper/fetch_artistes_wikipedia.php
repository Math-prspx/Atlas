<?php
// ─────────────────────────────────────────────────────────────────
// Atlas du Graphisme — Scraper Wikipedia pour les artistes
// Récupère le lien Wikipedia EN pour chaque artiste via Wikidata
//
// Usage CLI : php fetch_artistes_wikipedia.php
// Usage web : http://localhost/scraper/fetch_artistes_wikipedia.php
// ─────────────────────────────────────────────────────────────────

require_once __DIR__ . '/config.php';

$pdo = db();

// ── 1. Ajouter colonne wikipedia_en si elle n'existe pas ─────────

try {
    if (APP_ENV === 'local') {
        $pdo->exec("ALTER TABLE artistes ADD COLUMN wikipedia_en VARCHAR(200) DEFAULT NULL");
        echo "✓ Colonne wikipedia_en ajoutée (SQLite).\n";
    } else {
        $pdo->exec("ALTER TABLE artistes ADD COLUMN IF NOT EXISTS wikipedia_en VARCHAR(200) DEFAULT NULL");
        echo "✓ Colonne wikipedia_en ajoutée / déjà présente (MySQL).\n";
    }
} catch (PDOException $e) {
    echo "ℹ Colonne wikipedia_en déjà présente.\n";
}

// ── 2. Récupérer les artistes avec wikidata_id ────────────────────

$artistes = $pdo->query(
    "SELECT id, nom, wikidata_id FROM artistes WHERE wikidata_id IS NOT NULL"
)->fetchAll();

if (empty($artistes)) {
    echo "Aucun artiste avec wikidata_id trouvé. Arrêt.\n";
    exit;
}

echo "→ " . count($artistes) . " artistes à traiter.\n\n";

// ── 3. Construire la requête SPARQL par lots de 50 ────────────────

$stmt = $pdo->prepare(
    "UPDATE artistes SET wikipedia_en = :wiki WHERE id = :id"
);

$chunks = array_chunk($artistes, 50);

foreach ($chunks as $chunk) {
    $qidMap = []; // [qid => artiste]
    foreach ($chunk as $a) {
        $qidMap[$a['wikidata_id']] = $a;
    }

    $values = implode(' ', array_map(fn($q) => "wd:$q", array_keys($qidMap)));

    $sparql = <<<SPARQL
SELECT ?person ?sitelink WHERE {
  VALUES ?person { $values }
  ?sitelink schema:about ?person ;
            schema:isPartOf <https://en.wikipedia.org/> .
}
SPARQL;

    $url  = 'https://query.wikidata.org/sparql?format=json&query=' . urlencode($sparql);
    $opts = [
        'http' => [
            'method'  => 'GET',
            'header'  => "User-Agent: AtlasGraphisme/1.0 (contact@example.com)\r\n",
            'timeout' => 30,
        ],
    ];

    $json = @file_get_contents($url, false, stream_context_create($opts));

    if ($json === false) {
        echo "✗ Erreur réseau Wikidata pour ce lot.\n";
        continue;
    }

    $data = json_decode($json, true);

    if (!isset($data['results']['bindings'])) {
        echo "✗ Réponse Wikidata invalide pour ce lot.\n";
        continue;
    }

    foreach ($data['results']['bindings'] as $row) {
        $qid      = basename($row['person']['value']);   // ex: Q5592
        $wikiUrl  = $row['sitelink']['value'];           // ex: https://en.wikipedia.org/wiki/Herbert_Bayer
        $title    = rawurldecode(basename($wikiUrl));    // ex: Herbert_Bayer → Herbert_Bayer
        $titleClean = str_replace('_', ' ', $title);

        if (!isset($qidMap[$qid])) continue;

        $a = $qidMap[$qid];
        $stmt->execute([':wiki' => $titleClean, ':id' => $a['id']]);
        echo "[ok] {$a['nom']} → $titleClean\n";
    }

    // Marquer les artistes sans résultat
    $foundQids = array_map(
        fn($row) => basename($row['person']['value']),
        $data['results']['bindings']
    );
    foreach ($qidMap as $qid => $a) {
        if (!in_array($qid, $foundQids)) {
            echo "[--] {$a['nom']} — pas de page Wikipedia EN trouvée\n";
        }
    }

    // Pause pour respecter le rate-limit Wikidata
    if (count($chunks) > 1) sleep(1);
}

echo "\n✓ Scraping Wikipedia des artistes terminé.\n";
