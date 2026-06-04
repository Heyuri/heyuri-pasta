<?php

/**
 * One-time installation script.
 * Run from the project root:  php private/install.php
 *
 * Safe to re-run — it skips seeding if any mod accounts already exist.
 */

declare(strict_types=1);

define('ROOT', dirname(__DIR__));

$config = parse_ini_file(ROOT . '/private/config.ini', true);
if ($config === false) {
    exit('Could not read config.ini' . PHP_EOL);
}

$db  = $config['database'];
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $db['database_host'],
    $db['database_port'],
    $db['database_name']
);

try {
    $pdo = new PDO($dsn, $db['database_user'], $db['database_password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    exit('Database connection failed: ' . $e->getMessage() . PHP_EOL);
}

// ── Apply schema ──────────────────────────────────────────────────────────────

$schema     = file_get_contents(ROOT . '/private/schema.sql');
$statements = array_filter(
    array_map('trim', explode(';', $schema)),
    fn(string $s) => $s !== '' && !preg_match('/^\s*--/', $s)
);

foreach ($statements as $stmt) {
    $pdo->exec($stmt);
}

echo 'Schema applied.' . PHP_EOL;

// ── Seed default admin ────────────────────────────────────────────────────────

$count = (int) $pdo->query('SELECT COUNT(*) FROM mods')->fetchColumn();

if ($count === 0) {
    $hash = password_hash('admin', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO mods (username, role, password_hash) VALUES (:u, :r, :h)');
    $stmt->execute([':u' => 'admin', ':r' => 'admin', ':h' => $hash]);

    echo 'Default admin account created (username: admin / password: admin).' . PHP_EOL;
    echo 'WARNING: Change this password immediately after your first login.' . PHP_EOL;
} else {
    echo 'Mod accounts already exist — skipping default admin seed.' . PHP_EOL;
}

echo 'Installation complete.' . PHP_EOL;
