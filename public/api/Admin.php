<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

try {
  $pdo = new PDO(
    "mysql:host=" . ($_ENV['DB_HOST'] ?? '127.0.0.1') . ";dbname=" . ($_ENV['DB_DATABASE'] ?? 'brightray_db') . ";charset=utf8mb4",
    $_ENV['DB_USERNAME'] ?? 'root',
    $_ENV['DB_PASSWORD'] ?? ''
  );
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
  echo json_encode(["success" => false, "message" => "DB connect failed: " . $e->getMessage()]);
  exit;
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true) ?? [];

try {
  /*messages*/
  if ($action === 'messages' && $method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY id DESC");
    echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
  }
  if ($action === 'messages' && $method === 'DELETE') {
    $pdo->prepare("DELETE FROM contact_messages WHERE id=?")->execute([$input['id']]);
    echo json_encode(["success" => true, "message" => "Message deleted"]);
    exit;
  }

  /*company*/
if ($action === 'company' && $method === 'GET') {
  $stmt = $pdo->query("SELECT * FROM company_info ORDER BY id ASC LIMIT 1");
  echo json_encode(["success" => true, "data" => $stmt->fetch(PDO::FETCH_ASSOC)]);
  exit;
}
if ($action === 'company' && $method === 'PUT') {
  $pdo->prepare("UPDATE company_info SET company_name=?, tagline=?, description=?, email=?, phone=?, address=?, logo=?, website_url=?, facebook_url=?, instagram_url=?, linkedin_url=?, updated_at=NOW() WHERE id=1")
    ->execute([
      $input['company_name'] ?? '',
      $input['tagline'] ?? null,
      $input['description'] ?? null,
      $input['email'] ?? null,
      $input['phone'] ?? null,
      $input['address'] ?? null,
      $input['logo'] ?? null,
      $input['website_url'] ?? null,
      $input['facebook_url'] ?? null,
      $input['instagram_url'] ?? null,
      $input['linkedin_url'] ?? null,
    ]);
  echo json_encode(["success" => true, "message" => "Company info updated"]);
  exit;
}

  /*user*/
  if ($action === 'users' && $method === 'GET') {
    $stmt = $pdo->query("SELECT id, full_name, email, roles, permissions, created_at FROM users ORDER BY id DESC");
    echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
  }
  if ($action === 'users' && $method === 'POST') {
    $hash = password_hash($input['password'], PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (full_name, email, password, roles, permissions) VALUES (?,?,?,?,?)")
      ->execute([$input['full_name'], $input['email'], $hash, json_encode($input['roles'] ?? []), json_encode($input['permissions'] ?? [])]);
    echo json_encode(["success" => true, "message" => "User created"]);
    exit;
  }
  if ($action === 'users' && $method === 'DELETE') {
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$input['id']]);
    echo json_encode(["success" => true, "message" => "User deleted"]);
    exit;
  }

  echo json_encode(["success" => false, "message" => "Invalid action"]);
} catch (Exception $e) {
  echo json_encode(["success" => false, "message" => $e->getMessage()]);
}