<?php

namespace App;

class Auth
{
    public static function oauthLogin(array $data): bool
    {
        $pdo = Database::connection();
        $config = require __DIR__ . '/../config/config.php';
        
        // Check if user exists by oauth_user_id
        $stmt = $pdo->prepare("SELECT * FROM users WHERE oauth_user_id = :oauth_user_id");
        $stmt->execute([':oauth_user_id' => $data['user_id']]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$user) {
            // Create new user
            $stmt = $pdo->prepare("
                INSERT INTO users (oauth_user_id, username, name, email, realname, surname, role, theme) 
                VALUES (:oauth_user_id, :username, :name, :email, :realname, :surname, :role, 'light')
            ");
            $fullName = trim(($data['realname'] ?? '') . ' ' . ($data['surname'] ?? ''));
            $name = ($fullName !== '') ? $fullName : $data['username'];
            $email = $data['username'] . $config['oauth_email_domain'];
            
            $stmt->execute([
                ':oauth_user_id' => $data['user_id'],
                ':username' => $data['username'],
                ':name' => $name,
                ':email' => $email,
                ':realname' => $data['realname'] ?? '',
                ':surname' => $data['surname'] ?? '',
                ':role' => $data['role'] ?? 'user',
            ]);
            
            // Fetch the newly created user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE oauth_user_id = :oauth_user_id");
            $stmt->execute([':oauth_user_id' => $data['user_id']]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        } else {
            // Update existing user with latest OAuth data
            $stmt = $pdo->prepare("
                UPDATE users 
                SET username = :username, realname = :realname, surname = :surname, role = :role
                WHERE oauth_user_id = :oauth_user_id
            ");
            $stmt->execute([
                ':oauth_user_id' => $data['user_id'],
                ':username' => $data['username'],
                ':realname' => $data['realname'] ?? '',
                ':surname' => $data['surname'] ?? '',
                ':role' => $data['role'] ?? 'user',
            ]);
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE oauth_user_id = :oauth_user_id");
            $stmt->execute([':oauth_user_id' => $data['user_id']]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        
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
