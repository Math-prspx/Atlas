<?php
// Diagnostic temporaire — supprimer après debug
header('Content-Type: text/plain; charset=utf-8');

define('APP_ENV', 'prod');
define('DB_HOST',    'nothumanatlas.mysql.db');
define('DB_NAME',    'nothumanatlas');
define('DB_USER',    'nothumanatlas');
define('DB_PASS',    'GraphAtlas1234');
define('DB_CHARSET', 'utf8mb4');

echo "PHP " . phpversion() . "\n";
echo "APP_ENV: " . APP_ENV . "\n\n";

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    echo "DSN: $dsn\n";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✓ Connexion MySQL OK\n\n";

    // Tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "\n\n";

    // Count courants
    $count = $pdo->query("SELECT COUNT(*) FROM courants")->fetchColumn();
    echo "Courants: $count\n";

    // Sample row
    $row = $pdo->query("SELECT id, slug, nom, couleur_accent, pos_x, pos_y, pos_z FROM courants LIMIT 1")->fetch();
    echo "Premier courant: " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";

} catch (PDOException $e) {
    echo "✗ ERREUR PDO: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
} catch (Throwable $e) {
    echo "✗ ERREUR: " . $e->getMessage() . "\n";
}
