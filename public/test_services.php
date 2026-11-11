<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use App\Models\Service;

header('Content-Type: application/json');

try {
    $services = Service::allActive();
    echo json_encode([
        "status" => "success",
        "count" => count($services),
        "data" => $services
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}