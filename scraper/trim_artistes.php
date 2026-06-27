<?php
// ─────────────────────────────────────────────────────────────────
// Trim artistes arrays to first 3 entries (one-time script)
// Usage: https://www.nothuman.be/atlas/scraper/trim_artistes.php?token=AtlasRun2024
// ─────────────────────────────────────────────────────────────────

require_once __DIR__ . '/config.php';

if (($_GET['token'] ?? '') !== 'AtlasRun2024') {
    http_response_code(403); echo 'Forbidden'; exit;
}

$pdo  = db();
$rows = $pdo->query("SELECT id, slug, artistes FROM courants WHERE artistes IS NOT NULL")->fetchAll();

$updated = 0;
foreach ($rows as $row) {
    $arr = json_decode($row['artistes'], true);
    if (!is_array($arr) || count($arr) <= 3) continue;

    $trimmed = array_slice($arr, 0, 3);
    $stmt = $pdo->prepare("UPDATE courants SET artistes = :a WHERE id = :id");
    $stmt->execute([':a' => json_encode($trimmed, JSON_UNESCAPED_UNICODE), ':id' => $row['id']]);
    echo "✓ {$row['slug']}: " . count($arr) . " → 3<br>\n";
    $updated++;
}

echo "<br>Done. {$updated} courant(s) updated.";
