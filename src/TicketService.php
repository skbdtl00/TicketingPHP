<?php

namespace App;

use PDO;

class TicketService
{
    public static function createTicket(int $userId, string $title, string $category, string $content, ?array $file): void
    {
        $pdo = Database::connection();
        $attachmentPath = self::handleUpload($file);
        $stmt = $pdo->prepare("INSERT INTO tickets (user_id, title, category, content, status, attachment_path) VALUES (:user_id, :title, :category, :content, 'open', :attachment)");
        $stmt->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':category' => $category,
            ':content' => $content,
            ':attachment' => $attachmentPath,
        ]);
    }

    public static function myTickets(int $userId): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function allTickets(?string $status, ?string $category, ?string $userEmail): array
    {
        $pdo = Database::connection();
        $conditions = [];
        $params = [];

        if ($status) {
            $conditions[] = 't.status = :status';
            $params[':status'] = $status;
        }
        if ($category) {
            $conditions[] = 't.category = :category';
            $params[':category'] = $category;
        }
        if ($userEmail) {
            $conditions[] = 'u.email LIKE :email';
            $params[':email'] = '%' . $userEmail . '%';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $stmt = $pdo->prepare("
            SELECT t.*, u.name AS user_name, u.email AS user_email
            FROM tickets t
            JOIN users u ON u.id = t.user_id
            {$where}
            ORDER BY t.created_at DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findTicket(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare("
            SELECT t.*, u.name AS user_name, u.email AS user_email
            FROM tickets t
            JOIN users u ON u.id = t.user_id
            WHERE t.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        return $ticket ?: null;
    }

    public static function replies(int $ticketId): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare("
            SELECT r.*, u.name AS user_name, u.role AS user_role
            FROM replies r
            JOIN users u ON u.id = r.user_id
            WHERE ticket_id = :id
            ORDER BY r.created_at ASC
        ");
        $stmt->execute([':id' => $ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addReply(int $ticketId, int $userId, string $message): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare("INSERT INTO replies (ticket_id, user_id, message) VALUES (:ticket_id, :user_id, :message)");
        $stmt->execute([
            ':ticket_id' => $ticketId,
            ':user_id' => $userId,
            ':message' => $message,
        ]);
        $pdo->prepare("UPDATE tickets SET updated_at = CURRENT_TIMESTAMP WHERE id = :id")
            ->execute([':id' => $ticketId]);
    }

    public static function updateStatus(int $ticketId, string $status): void
    {
        $allowed = ['open', 'in_progress', 'closed'];
        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('สถานะไม่ถูกต้อง');
        }
        $pdo = Database::connection();
        $stmt = $pdo->prepare("UPDATE tickets SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $ticketId]);
    }

    public static function deleteTicket(int $ticketId): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare("SELECT attachment_path FROM tickets WHERE id = :id");
        $stmt->execute([':id' => $ticketId]);
        $attachment = $stmt->fetchColumn();

        $stmt = $pdo->prepare("DELETE FROM tickets WHERE id = :id");
        $stmt->execute([':id' => $ticketId]);

        if ($attachment) {
            $config = require __DIR__ . '/../config/config.php';
            $filePath = rtrim($config['upload_dir'], '/') . '/' . basename($attachment);
            if (is_file($filePath)) {
                if (!unlink($filePath)) {
                    throw new \RuntimeException('ลบไฟล์แนบไม่สำเร็จ');
                }
            }
        }
    }

    public static function stats(): array
    {
        $pdo = Database::connection();
        $counts = $pdo->query("SELECT status, COUNT(*) as total FROM tickets GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
        return [
            'total' => (int) $pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn(),
            'open' => (int) ($counts['open'] ?? 0),
            'in_progress' => (int) ($counts['in_progress'] ?? 0),
            'closed' => (int) ($counts['closed'] ?? 0),
        ];
    }

    private static function handleUpload(?array $file): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $config = require __DIR__ . '/../config/config.php';
        if ($file['size'] > $config['max_upload_size']) {
            throw new \RuntimeException('ไฟล์มีขนาดเกินกำหนด 10MB');
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $config['allowed_extensions'], true)) {
            throw new \RuntimeException('รูปแบบไฟล์ไม่รองรับ');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $allowedMimes = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'pdf' => ['application/pdf'],
            'zip' => ['application/zip', 'application/x-zip-compressed'],
        ];
        if (!in_array($mime, $allowedMimes[$extension] ?? [], true)) {
            throw new \RuntimeException('Mime type ไม่ถูกต้อง');
        }

        ensure_upload_dir();
        $safeName = uniqid('file_', true) . '.' . $extension;
        $destination = $config['upload_dir'] . '/' . $safeName;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('อัปโหลดไฟล์ไม่สำเร็จ');
        }
        $base = trim($config['base_url'] ?? '', '/');
        $prefix = $base === '' ? '' : '/' . $base;
        return $prefix . '/uploads/' . $safeName;
    }
}
