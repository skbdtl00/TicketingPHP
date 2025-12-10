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

    case $path === '/register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = Auth::register(trim($_POST['name']), trim($_POST['email']), $_POST['password']);
            if ($result === true) {
                redirect_with_message('/login', 'สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ');
            }
            redirect_with_message('/register', $result, 'error');
        }
        render('auth_register');
        break;

    case $path === '/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = Auth::login(trim($_POST['email']), $_POST['password']);
            if ($result === true) {
                redirect_with_message('/', 'เข้าสู่ระบบสำเร็จ');
            }
            redirect_with_message('/login', $result, 'error');
        }
        render('auth_login');
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

    case preg_match('~^/tickets/(\\d+)$~', $path, $m):
        $ticketId = (int) $m[1];
        require_login();
        $user = current_user();
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
