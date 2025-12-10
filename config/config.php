<?php
return [
    'db_host' => getenv('DB_HOST') ?: 'localhost',
    'db_name' => getenv('DB_NAME') ?: 'ticketing',
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_pass' => getenv('DB_PASS') ?: '',
    'app_name' => 'Tozei Ticketing',
    'base_url' => '/',
    'upload_dir' => __DIR__ . '/../uploads',
    'max_upload_size' => 10 * 1024 * 1024, // 10MB
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'zip'],
    'oauth_url' => 'https://tozei.com/oauth/',
    'oauth_secret' => getenv('OAUTH_SECRET') ?: 'FWV9agSoDqnlFWV9agSoDqnl',
    'oauth_email_domain' => getenv('OAUTH_EMAIL_DOMAIN') ?: '@tozei.com',
];
