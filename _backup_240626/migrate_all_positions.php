<?php
// ─────────────────────────────────────────────────────────────────
// Migration : toutes les positions (37 courants, niveaux 1 + 2)
// Source : init_sqlite.php (référence canonique)
// Usage : https://www.nothuman.be/atlas/migrate_all_positions.php
// ⚠️  Supprimer ce fichier après usage
// ─────────────────────────────────────────────────────────────────

require_once __DIR__ . '/scraper/config.php';
$pdo = db();

// [slug, pos_x, pos_y, pos_z, niveau]
$positions = [
  // ── Niveau 1 ────────────────────────────────────────────────────
  ['arts-crafts',      -3.0,  0.5,   8.0, 1],
  ['art-nouveau',       3.5,  1.2,   6.0, 1],
  ['futurisme',        -5.0,  2.0,   2.2, 1],
  ['constructivisme',  -2.0, -1.5,   1.0, 1],
  ['de-stijl',          2.5,  1.8,   0.6, 1],
  ['bauhaus',           0.0, -0.5,   0.2, 1],
  ['art-deco',          5.0,  0.8,  -0.4, 1],
  ['surrealisme',      -4.5,  1.8,  -1.0, 1],
  ['style-suisse',     -2.5, -1.0,  -7.0, 1],
  ['pop-art',           4.5,  1.2,  -7.5, 1],
  ['vernacular',        6.5,  2.5,  -6.0, 1],
  ['psychedelique',     5.5,  2.8, -10.0, 1],
  ['new-wave-typo',    -5.0, -0.8, -10.5, 1],
  ['pixel-art',        -6.0,  1.5, -11.0, 1],
  ['postmodernisme',    2.0, -1.5, -12.0, 1],
  ['memphis',           5.5, -0.8, -12.5, 1],
  ['grunge',           -3.5, -2.5, -14.0, 1],
  ['y2k',               3.5,  1.5, -15.0, 1],
  ['skeuomorphisme',    0.5,  1.0, -16.0, 1],
  ['flat-design',      -1.0, -0.5, -18.0, 1],
  ['vaporwave',         4.5,  2.0, -18.5, 1],
  ['brutalisme-web',   -4.0,  0.8, -19.5, 1],

  // ── Niveau 2 (Z corrigé : z = 8 - (debut-1880)*0.2) ────────────
  ['aesthetic-movement', -4.5,  1.5,  10.4, 2],
  ['jugendstil',          5.0,  2.2,   4.8, 2],
  ['vorticism',          -6.5,  3.0,   1.2, 2],
  ['neoplasticisme',      4.0,  2.8,   0.6, 2],
  ['new-typography',      1.5,  0.5,  -1.6, 2],
  ['ulm-school',         -1.5,  0.8,  -6.6, 2],
  ['streamline',          6.5,  1.8,  -2.0, 2],
  ['op-art',             -4.0,  0.0,  -8.8, 2],
  ['lettrisme',          -6.0,  2.8,  -5.0, 2],
  ['psychedelic-poster',  7.0,  3.8,  -9.2, 2],
  ['deconstruction-typo', 3.5, -2.5, -13.6, 2],
  ['neo-pop',             6.0,  2.2, -12.0, 2],
  ['material-design',     0.5,  0.5, -18.8, 2],
  ['synthwave',           6.0,  3.0, -18.4, 2],
  ['lo-fi-aesthetic',     3.0,  3.5, -19.0, 2],
];

$stmt = $pdo->prepare("UPDATE courants SET pos_x=:x, pos_y=:y, pos_z=:z WHERE slug=:s");
$ok = 0; $skip = 0;
foreach ($positions as [$slug, $x, $y, $z]) {
    $stmt->execute([':x' => $x, ':y' => $y, ':z' => $z, ':s' => $slug]);
    $stmt->rowCount() ? $ok++ : $skip++;
}

echo "<pre>✓ $ok nœuds mis à jour, $skip non trouvés.\n\n";
foreach ($positions as [$slug]) echo "  - $slug\n";
echo "\n⚠️  Supprime ce fichier maintenant.</pre>";
