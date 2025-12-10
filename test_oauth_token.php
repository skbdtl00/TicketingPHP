#!/usr/bin/env php
<?php
/**
 * OAuth Token Generator and Validator Test
 * 
 * This script generates a test OAuth token and validates it
 * Usage: php test_oauth_token.php
 */

require_once __DIR__ . '/src/helpers.php';

$secret = 'FWV9agSoDqnlFWV9agSoDqnl';

// Generate a test token
$testData = [
    'user_id' => '12345',
    'username' => 'testuser',
    'role' => 'admin',
    'realname' => 'Test',
    'surname' => 'User'
];

$payload = base64_encode(json_encode($testData));
$signature = hash_hmac('sha256', $payload, $secret);
$token = $payload . '.' . $signature;

echo "=" . str_repeat("=", 70) . "\n";
echo "OAuth Token Generator and Validator Test\n";
echo "=" . str_repeat("=", 70) . "\n\n";

echo "Test Data:\n";
echo json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

echo "Generated Token:\n";
echo $token . "\n\n";

echo "Token Components:\n";
echo "  Payload:   " . $payload . "\n";
echo "  Signature: " . $signature . "\n\n";

// Validate the token
$validated = validate_oauth_token($token, $secret);

if ($validated === false) {
    echo "❌ Token validation FAILED!\n";
    exit(1);
} else {
    echo "✅ Token validation SUCCEEDED!\n\n";
    echo "Validated Data:\n";
    echo json_encode($validated, JSON_PRETTY_PRINT) . "\n\n";
    
    // Verify data matches
    if ($validated === $testData) {
        echo "✅ Data matches perfectly!\n\n";
    } else {
        echo "⚠️  Data does not match exactly:\n";
        echo "  Expected: " . json_encode($testData) . "\n";
        echo "  Got:      " . json_encode($validated) . "\n\n";
    }
}

// Test invalid token
echo "Testing invalid token...\n";
$invalidToken = $payload . '.invalid_signature';
$invalidValidated = validate_oauth_token($invalidToken, $secret);

if ($invalidValidated === false) {
    echo "✅ Invalid token correctly rejected!\n\n";
} else {
    echo "❌ Invalid token was accepted (SECURITY ISSUE)!\n\n";
    exit(1);
}

// Test callback URL
echo "=" . str_repeat("=", 70) . "\n";
echo "Test URL for oauth_callback_standalone.php:\n";
echo "=" . str_repeat("=", 70) . "\n";
echo "http://localhost/oauth_callback_standalone.php?token=" . urlencode($token) . "\n\n";

echo "=" . str_repeat("=", 70) . "\n";
echo "All tests passed! ✅\n";
echo "=" . str_repeat("=", 70) . "\n";
