<?php
namespace App\Models;

use App\Database\Connection;
use PDO;

class Service
{
    public static function allActive(): array
    {
        $pdo = Connection::get();
        $stmt = $pdo->prepare("SELECT * FROM services WHERE status = ? ORDER BY id DESC");
        $stmt->execute(['published']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function all(): array
    {
        $pdo = Connection::get();
        return $pdo->query("SELECT id, title, slug, description, image, status, created_at, updated_at FROM services ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(string $title, string $slug, string $description, string $status): int
    {
        $pdo = Connection::get();
        $stmt = $pdo->prepare('INSERT INTO services (title, slug, description, image, status) VALUES (?, ?, ?, NULL, ?)');
        $stmt->execute([$title, $slug, $description, $status]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, string $title, string $slug, string $description, string $status): void
    {
        $pdo = Connection::get();
        $pdo->prepare('UPDATE services SET title=?, slug=?, description=?, status=?, updated_at=NOW() WHERE id=?')
            ->execute([$title, $slug, $description, $status, $id]);
    }

    public static function delete(int $id): void
    {
        $pdo = Connection::get();
        $pdo->prepare('DELETE FROM services WHERE id=?')->execute([$id]);
    }
}