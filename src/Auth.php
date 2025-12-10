<?php

namespace App;

class Auth
{
    public static function register(string $name, string $email, string $password): bool|string
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            return 'อีเมลนี้ถูกใช้แล้ว';
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, theme) VALUES (:name, :email, :password_hash, 'user', 'light')");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password_hash' => $hash,
        ]);
        return true;
    }

    public static function login(string $email, string $password): bool|string
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
        }
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function updateProfile(int $id, string $name, string $theme): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare("UPDATE users SET name = :name, theme = :theme WHERE id = :id");
        $stmt->execute([
            ':name' => $name,
            ':theme' => $theme,
            ':id' => $id,
        ]);
        $stmt = $pdo->prepare("SELECT id, name, email, role, theme FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $_SESSION['user'] = $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
