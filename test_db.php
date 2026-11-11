<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Database\Connection;

try {
    $pdo = Connection::get();
    echo "✅ SUCCESS: Connected to brightray_db !";
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage();
}

