<?php

require_once __DIR__ . '/../../core/Database.php';

class User
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = ?';
        $params = [$email];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function allByRole(string $role): array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM users WHERE role = ? ORDER BY name');
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    public static function activeInstructors(): array
    {
        $stmt = Database::connect()->prepare(
            "SELECT * FROM users WHERE role = 'instructor' AND status = 'active' ORDER BY name"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function allManaged(): array
    {
        // Everyone the Super Admin manages -- instructors and students, not other admins.
        $stmt = Database::connect()->prepare(
            "SELECT * FROM users WHERE role IN ('instructor','student') ORDER BY role, name"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function create(string $name, string $email, string $password, string $role): int
    {
        $stmt = Database::connect()->prepare(
            'INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, "active")'
        );
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_BCRYPT), $role]);
        return (int)Database::connect()->lastInsertId();
    }

    public static function setStatus(int $id, string $status): void
    {
        $status = $status === 'active' ? 'active' : 'inactive';
        $stmt = Database::connect()->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public static function countByRole(string $role): int
    {
        $stmt = Database::connect()->prepare('SELECT COUNT(*) FROM users WHERE role = ?');
        $stmt->execute([$role]);
        return (int)$stmt->fetchColumn();
    }
}
