<?php
// ─────────────────────────────────────────────────────────────────
// Atlas du Graphisme — API REST JSON
// GET /api/courants.php          → tous les courants
// GET /api/courants.php?slug=bauhaus → un seul courant (avec artistes + relations)
// ─────────────────────────────────────────────────────────────────

require_once __DIR__ . '/../scraper/config.php';

// ── Headers ───────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=300');

// ── Router ────────────────────────────────────────────────────────
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;

try {
    $pdo = db();

    if ($slug !== null) {
        echo json_encode(get_courant($pdo, $slug), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        echo json_encode(get_all_courants($pdo), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur base de données.']);
}


// ─────────────────────────────────────────────────────────────────
// Tous les courants (vue liste pour la scène 3D)
// ─────────────────────────────────────────────────────────────────
function get_all_courants(PDO $pdo): array {

    $rows = $pdo->query(<<<SQL
        SELECT
            id, slug, nom, wikidata_id,
            description_courte,
            periode_debut, periode_fin,
            image_wikidata, image_wikipedia, image_3, image_4, image_5, image_6,
            artistes, key_points,
            couleur_accent, typographie,
            mots_cles,
            pos_x, pos_y, pos_z, niveau
        FROM courants
        ORDER BY periode_debut ASC, id ASC
    SQL)->fetchAll();

    // Récupérer toutes les relations en une seule requête
    $relations = $pdo->query(<<<SQL
        SELECT
            r.source_id, r.cible_id, r.type_relation,
            cs.slug AS source_slug,
            cc.slug AS cible_slug
        FROM courant_relations r
        JOIN courants cs ON cs.id = r.source_id
        JOIN courants cc ON cc.id = r.cible_id
    SQL)->fetchAll();

    // Indexer les relations par source_slug
    $rel_map = [];
    foreach ($relations as $rel) {
        $rel_map[$rel['source_slug']][] = [
            'cible'    => $rel['cible_slug'],
            'type'     => $rel['type_relation'],
        ];
    }

    // Formatter chaque courant
    return array_map(function ($row) use ($rel_map) {
        return [
            'id'                 => (int)$row['id'],
            'slug'               => $row['slug'],
            'nom'                => $row['nom'],
            'wikidata_id'        => $row['wikidata_id'],
            'description_courte' => $row['description_courte'],
            'periode'            => [
                'debut' => $row['periode_debut'] ? (int)$row['periode_debut'] : null,
                'fin'   => $row['periode_fin']   ? (int)$row['periode_fin']   : null,
            ],
            'images'             => array_values(array_filter([
                $row['image_wikidata'],
                $row['image_wikipedia'],
                $row['image_3'] ?? null,
                $row['image_4'] ?? null,
                $row['image_5'] ?? null,
                $row['image_6'] ?? null,
            ])),
            'da'                 => [
                'couleur'    => $row['couleur_accent'],
                'typo'       => $row['typographie'],
            ],
            'artistes'           => $row['artistes'] ? json_decode($row['artistes'], true) : [],
            'key_points'         => $row['key_points'] ?? '',
            'mots_cles'          => $row['mots_cles'] ? json_decode($row['mots_cles'], true) : [],
            'position'           => [
                'x' => (float)$row['pos_x'],
                'y' => (float)$row['pos_y'],
                'z' => (float)$row['pos_z'],
            ],
            'niveau'             => (int)$row['niveau'],
            'relations'          => $rel_map[$row['slug']] ?? [],
        ];
    }, $rows);
}


// ─────────────────────────────────────────────────────────────────
// Un courant détaillé (vue fiche)
// ─────────────────────────────────────────────────────────────────
function get_courant(PDO $pdo, string $slug): array {

    $stmt = $pdo->prepare(<<<SQL
        SELECT * FROM courants WHERE slug = :slug LIMIT 1
    SQL);
    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        return ['error' => "Courant '$slug' introuvable."];
    }

    // Artistes associés
    $artistes = $pdo->prepare(<<<SQL
        SELECT a.nom, a.naissance, a.deces, a.nationalite, a.bio_courte, a.image,
               a.wikidata_id, a.wikipedia_en
        FROM artistes a
        JOIN courant_artistes ca ON ca.artiste_id = a.id
        WHERE ca.courant_id = :id
        ORDER BY a.naissance ASC
    SQL);
    $artistes->execute([':id' => $row['id']]);

    // Relations
    $relations = $pdo->prepare(<<<SQL
        SELECT c.slug, c.nom, r.type_relation
        FROM courant_relations r
        JOIN courants c ON c.id = r.cible_id
        WHERE r.source_id = :id
    SQL);
    $relations->execute([':id' => $row['id']]);

    // Objets visuels
    $objets = $pdo->prepare(<<<SQL
        SELECT titre, type, annee, image, legende, source
        FROM objets_visuels
        WHERE courant_id = :id
        ORDER BY annee ASC
    SQL);
    $objets->execute([':id' => $row['id']]);

    return [
        'id'                 => (int)$row['id'],
        'slug'               => $row['slug'],
        'nom'                => $row['nom'],
        'wikidata_id'        => $row['wikidata_id'],
        'description_courte' => $row['description_courte'],
        'description_longue' => $row['description_longue'],
        'periode'            => [
            'debut' => $row['periode_debut'] ? (int)$row['periode_debut'] : null,
            'fin'   => $row['periode_fin']   ? (int)$row['periode_fin']   : null,
        ],
        'images'             => [
            'wikidata'  => $row['image_wikidata'],
            'wikipedia' => $row['image_wikipedia'],
        ],
        'da'                 => [
            'couleur' => $row['couleur_accent'],
            'typo'    => $row['typographie'],
        ],
        'artistes'           => $row['artistes'] ? json_decode($row['artistes'], true) : [],
        'key_points'         => $row['key_points'] ?? '',
        'mots_cles'          => $row['mots_cles']         ? json_decode($row['mots_cles'], true) : [],
        'principes_visuels'  => $row['principes_visuels'],
        'position'           => [
            'x' => (float)$row['pos_x'],
            'y' => (float)$row['pos_y'],
            'z' => (float)$row['pos_z'],
        ],
        'artistes'           => array_map(function($a) {
            $wiki_en = $a['wikipedia_en'] ?? null;
            $a['wikipedia_url'] = $wiki_en
                ? 'https://en.wikipedia.org/wiki/' . str_replace(' ', '_', $wiki_en)
                : null;
            return $a;
        }, $artistes->fetchAll()),
        'relations'          => $relations->fetchAll(),
        'objets_visuels'     => $objets->fetchAll(),
        'wikipedia_url'      => $row['wikipedia_titre']
            ? 'https://en.wikipedia.org/wiki/' . str_replace(' ', '_', $row['wikipedia_titre'])
            : null,
    ];
}
