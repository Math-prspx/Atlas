<?php
// ─────────────────────────────────────────────────────────────────
// Atlas du Graphisme — Configuration (TEMPLATE)
// Copier ce fichier en config.php et renseigner les vraies valeurs.
// config.php est dans .gitignore — ne jamais commiter les credentials.
// ─────────────────────────────────────────────────────────────────

define('APP_ENV', getenv('APP_ENV') ?: 'local');

// ── MySQL config (OVH prod) ───────────────────────────────────────
define('DB_HOST',    'VOTRE_HOST.mysql.db');
define('DB_NAME',    'VOTRE_DB_NAME');
define('DB_USER',    'VOTRE_USER');
define('DB_PASS',    'VOTRE_PASSWORD');
define('DB_CHARSET', 'utf8mb4');

// ── SQLite config (local dev) ─────────────────────────────────────
define('SQLITE_PATH', __DIR__ . '/../database/atlas.db');

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    if (APP_ENV === 'local') {
        if (!is_dir(dirname(SQLITE_PATH))) mkdir(dirname(SQLITE_PATH), 0755, true);
        $pdo = new PDO('sqlite:' . SQLITE_PATH, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA journal_mode=WAL; PRAGMA foreign_keys=ON;');
    } else {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}
