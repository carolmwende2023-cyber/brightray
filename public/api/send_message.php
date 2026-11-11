<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

$host = $_ENV['DB_HOST'] ?? 'localhost';
$db   = $_ENV['DB_NAME'] ?? 'brightray_db';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

// Support both JSON (React) and FormData
$input = json_decode(file_get_contents("php://input"), true);
$name = trim($input['full_name'] ?? $_POST['full_name'] ?? '');
$email = trim($input['email'] ?? $_POST['email'] ?? '');
$message = trim($input['message'] ?? $_POST['message'] ?? '');

// Same validation as your OLD code
if (!preg_match('/^[\p{L} ]+$/u', $name)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Please enter a valid name using letters only."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Please enter a valid email address."]);
    exit;
}

if ($message === '') {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Please enter your message."]);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT INTO contact_messages (full_name, email, message) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $message]);

    echo json_encode(["status" => "success", "message" => "Message sent successfully!"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}