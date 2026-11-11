<?php
require __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

use App\Models\Service;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $input['action'] ?? '';

    if ($method === 'GET') {
        $services = Service::all();
        echo json_encode(["status" => "success", "count" => count($services), "data" => $services]);
        exit;
    }

    if ($method === 'POST' && $action === 'delete') {
        Service::delete((int)($input['id'] ?? 0));
        echo json_encode(["status" => "success", "message" => "Service deleted"]);
        exit;
    }

    if ($method === 'POST') {
        $id = (int)($input['id'] ?? 0);
        $title = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $status = $input['status'] ?? 'published';

        if ($title === '' || $description === '') {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Title and description required"]);
            exit;
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

        if ($id > 0) {
            Service::update($id, $title, $slug, $description, $status);
            echo json_encode(["status" => "success", "message" => "Service updated"]);
        } else {
            $newId = Service::create($title, $slug, $description, $status);
            echo json_encode(["status" => "success", "message" => "Service added", "id" => $newId]);
        }
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}