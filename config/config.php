<?php
return [
    'db_path' => __DIR__ . '/database.sqlite',
    'app_name' => 'Tozei Ticketing',
    'base_url' => '/',
    'upload_dir' => __DIR__ . '/../uploads',
    'max_upload_size' => 10 * 1024 * 1024, // 10MB
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'zip'],
];
