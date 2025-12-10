<?php
/**
 * Standalone OAuth Callback Script (for testing purposes)
 * This is a standalone version of the OAuth callback handler
 * The actual OAuth flow is handled in index.php at /oauth/callback
 * 
 * IMPORTANT: Set OAUTH_SECRET environment variable before using this script
 * Example: export OAUTH_SECRET='your_secret_key_here'
 */

session_start();
define('OAUTH_SECRET', getenv('OAUTH_SECRET') ?: 'FWV9agSoDqnlFWV9agSoDqnl');

function validateToken(string $token): array|false {
    if (!str_contains($token, '.')) return false;

    [$payload, $signature] = explode('.', $token, 2);

    // Verify signature
    $expectedSig = hash_hmac('sha256', $payload, OAUTH_SECRET);
    if (!hash_equals($expectedSig, $signature)) {
        return false; // signature invalid
    }

    // Decode JSON data
    $data = json_decode(base64_decode($payload), true);

    if (!$data) return false;

    return $data;
}

// Check token
if (!isset($_GET['token'])) {
    die("Missing token");
}

$token = $_GET['token'];
$data  = validateToken($token);

if (!$data) {
    die("Invalid token");
}

// Transfer to local session
$_SESSION['user_id']  = $data['user_id'];
$_SESSION['username'] = $data['username'];
$_SESSION['role']     = $data['role'];
$_SESSION['realname'] = $data['realname'] ?? '';
$_SESSION['surname']  = $data['surname'] ?? '';

echo "OAuth Transfer Success!<br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
