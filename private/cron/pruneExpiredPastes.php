<?php

// CLI only
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit(1);
}

require_once __DIR__ . '/../../vendor/autoload.php';

use Puchiko\database\databaseConnection;

$config = parse_ini_file(__DIR__ . '/../config.ini', true);
$db     = $config['database'];

databaseConnection::createInstance(
    'mysql',
    $db['database_host'],
    $db['database_name'],
    'utf8mb4',
    $db['database_user'],
    $db['database_password'],
);

$connection = databaseConnection::getInstance();
$pdo        = $connection->getConnection();

$stmt = $pdo->prepare(
    'DELETE FROM pastes
     WHERE time_to_live > 0
       AND UNIX_TIMESTAMP(created_at) + time_to_live < UNIX_TIMESTAMP(NOW())'
);
$stmt->execute();

$deleted = $stmt->rowCount();
echo '[' . date('Y-m-d H:i:s') . '] Pruned ' . $deleted . ' expired paste(s).' . PHP_EOL;
