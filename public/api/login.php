<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

$host = $_ENV['DB_HOST'] ?? 'localhost';
$db   = $_ENV['DB_NAME'] ?? 'brightray_db';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

$input = json_decode(file_get_contents("php://input"), true);
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Email and password required."]);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userRow || !password_verify($password, $userRow['password'])) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
        exit;
    }

    // Get roles
    $stmt = $pdo->prepare("SELECT r.name FROM roles r JOIN user_roles ur ON ur.role_id = r.id WHERE ur.user_id = ?");
    $stmt->execute([$userRow['id']]);
    $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get permissions
    $stmt = $pdo->prepare("SELECT DISTINCT p.name FROM permissions p JOIN role_permissions rp ON rp.permission_id = p.id JOIN user_roles ur ON ur.role_id = rp.role_id WHERE ur.user_id = ?");
    $stmt->execute([$userRow['id']]);
    $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        "status" => "success",
        "message" => "Login successful",
        "user" => [
            "id" => $userRow['id'],
            "full_name" => $userRow['full_name'],
            "email" => $userRow['email'],
            "roles" => $roles,
            "permissions" => $permissions
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}