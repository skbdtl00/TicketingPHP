<?php

use App\Database;

function start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    start_session();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf'] ?? '';
        if (empty($token) || $token !== ($_SESSION['csrf'] ?? '')) {
            http_response_code(403);
            exit('การยืนยันความปลอดภัยล้มเหลว');
        }
    }
}

function current_user(): ?array
{
    start_session();
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        header('Location: /login');
        exit;
    }
}

function require_admin(): void
{
    $user = current_user();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        exit('จำกัดสิทธิ์สำหรับผู้ดูแลระบบ');
    }
}

function render(string $view, array $data = []): void
{
    extract($data);
    $user = current_user();
    $currentView = $view;
    include __DIR__ . '/../views/layout.php';
}

function sanitize(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function route(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
    return rtrim($path, '/') ?: '/';
}

function redirect_with_message(string $url, string $message, string $type = 'success'): void
{
    start_session();
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    header("Location: {$url}");
    exit;
}

function flash(): ?array
{
    start_session();
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function human_status(string $status): string
{
    return match ($status) {
        'open' => 'รอดำเนินการ',
        'in_progress' => 'กำลังดำเนินการ',
        'closed' => 'ปิดตั๋วแล้ว',
        default => $status,
    };
}

function ensure_upload_dir(): void
{
    $config = require __DIR__ . '/../config/config.php';
    if (!is_dir($config['upload_dir'])) {
        mkdir($config['upload_dir'], 0755, true);
    }
}

function validate_oauth_token(string $token, ?string $secret = null): array|false
{
    if ($secret === null) {
        $config = require __DIR__ . '/../config/config.php';
        $secret = $config['oauth_secret'];
    }
    
    if (!str_contains($token, '.')) {
        return false;
    }

    [$payload, $signature] = explode('.', $token, 2);

    // Verify signature
    $expectedSig = hash_hmac('sha256', $payload, $secret);
    if (!hash_equals($expectedSig, $signature)) {
        return false; // signature invalid
    }

    // Decode JSON data
    $data = json_decode(base64_decode($payload), true);

    if (!$data) {
        return false;
    }

    return $data;
}
