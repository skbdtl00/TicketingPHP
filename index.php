<?php
require __DIR__ . '/src/helpers.php';
require __DIR__ . '/src/Database.php';
require __DIR__ . '/src/Auth.php';
require __DIR__ . '/src/TicketService.php';

use App\Auth;
use App\TicketService;

start_session();
verify_csrf();
$path = route();
$user = current_user();

switch (true) {
    case $path === '/':
        render('home');
        break;

    case $path === '/login':
        // Redirect to OAuth provider
        $config = require __DIR__ . '/config/config.php';
        $callbackUrl = urlencode($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/oauth/callback');
        header('Location: ' . $config['oauth_url'] . '?callback=' . $callbackUrl);
        exit;
        break;

    case $path === '/oauth/callback':
        // OAuth callback handler
        if (!isset($_GET['token'])) {
            redirect_with_message('/', 'Missing token', 'error');
        }

        $token = $_GET['token'];
        $data = validate_oauth_token($token);

        if (!$data) {
            redirect_with_message('/', 'Invalid token', 'error');
        }

        // Login or create user with OAuth data
        Auth::oauthLogin($data);
        redirect_with_message('/', 'เข้าสู่ระบบสำเร็จ');
        break;

    case $path === '/logout':
        Auth::logout();
        redirect_with_message('/', 'ออกจากระบบแล้ว');
        break;

    case $path === '/profile':
        require_login();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::updateProfile($user['id'], trim($_POST['name']), $_POST['theme']);
            redirect_with_message('/profile', 'อัปเดตโปรไฟล์เรียบร้อย');
        }
        render('profile');
        break;

    case $path === '/tickets':
        require_login();
        $tickets = TicketService::myTickets($user['id']);
        render('tickets_list', compact('tickets'));
        break;

    case $path === '/tickets/new':
        require_login();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                TicketService::createTicket(
                    $user['id'],
                    trim($_POST['title']),
                    trim($_POST['category']),
                    trim($_POST['content']),
                    $_FILES['attachment'] ?? null
                );
                redirect_with_message('/tickets', 'สร้างตั๋วเรียบร้อย');
            } catch (Exception $e) {
                redirect_with_message('/tickets/new', $e->getMessage(), 'error');
            }
        }
        render('ticket_new');
        break;

    case preg_match('~^/tickets/(\\d+)/attachment$~', $path, $m):
        $ticketId = (int) $m[1];
        require_login();
        $ticket = TicketService::findTicket($ticketId);
        if (!$ticket) {
            http_response_code(404);
            exit('ไม่พบตั๋ว');
        }
        if ($user['role'] !== 'admin' && $ticket['user_id'] !== $user['id']) {
            http_response_code(403);
            exit('ไม่สามารถเข้าถึงไฟล์แนบนี้');
        }
        if (empty($ticket['attachment_path'])) {
            http_response_code(404);
            exit('ไม่มีไฟล์แนบ');
        }
        $config = require __DIR__ . '/config/config.php';
        $uploadDir = realpath($config['upload_dir']);
        $filePath = $uploadDir ? realpath($uploadDir . '/' . basename($ticket['attachment_path'])) : false;
        $safePrefix = $uploadDir ? rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : null;
        if (!$safePrefix || !$filePath || strncmp($filePath, $safePrefix, strlen($safePrefix)) !== 0 || !is_file($filePath)) {
            http_response_code(404);
            exit('ไม่พบไฟล์');
        }
        $mime = mime_content_type($filePath) ?: 'application/octet-stream';
        $downloadName = basename($filePath);
        $downloadName = str_replace(['"', '\\', "\r", "\n"], '', $downloadName);
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filePath));
        header('Content-Disposition: attachment; filename="' . rawurlencode($downloadName) . '"');
        readfile($filePath);
        exit;

    case preg_match('~^/tickets/(\\d+)$~', $path, $m):
        $ticketId = (int) $m[1];
        require_login();
        $ticket = TicketService::findTicket($ticketId);
        if (!$ticket) {
            http_response_code(404);
            exit('ไม่พบตั๋ว');
        }
        if ($user['role'] !== 'admin' && $ticket['user_id'] !== $user['id']) {
            http_response_code(403);
            exit('ไม่สามารถเข้าถึงตั๋วนี้');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ticket['status'] !== 'closed') {
            TicketService::addReply($ticketId, $user['id'], trim($_POST['message']));
            redirect_with_message("/tickets/{$ticketId}", 'ส่งข้อความแล้ว');
        }
        $replies = TicketService::replies($ticketId);
        render('ticket_show', compact('ticket', 'replies'));
        break;

    case $path === '/admin':
        require_admin();
        $stats = TicketService::stats();
        $tickets = TicketService::allTickets(null, null, null);
        render('admin_dashboard', compact('stats', 'tickets'));
        break;

    case $path === '/admin/tickets':
        require_admin();
        $status = $_GET['status'] ?? null;
        $category = $_GET['category'] ?? null;
        $userEmail = $_GET['user'] ?? null;
        $stats = TicketService::stats();
        $tickets = TicketService::allTickets($status ?: null, $category ?: null, $userEmail ?: null);
        render('admin_dashboard', compact('stats', 'tickets'));
        break;

    case preg_match('~^/admin/tickets/(\\d+)/status$~', $path, $m):
        require_admin();
        $ticketId = (int) $m[1];
        $status = $_POST['status'] ?? 'open';
        TicketService::updateStatus($ticketId, $status);
        redirect_with_message("/tickets/{$ticketId}", 'อัปเดตสถานะแล้ว');
        break;

    case preg_match('~^/admin/tickets/(\\d+)/delete$~', $path, $m):
        require_admin();
        $ticketId = (int) $m[1];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            TicketService::deleteTicket($ticketId);
            redirect_with_message('/admin', 'ลบตั๋วแล้ว');
        } else {
            http_response_code(405);
            exit('ต้องใช้คำขอแบบ POST');
        }
        break;

    default:
        http_response_code(404);
        echo 'ไม่พบหน้า';
}
