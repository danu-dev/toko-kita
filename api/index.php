<?php

use Illuminate\Http\Request;

// Ensure writable directories in Vercel /tmp
$dirs = [
    '/tmp/views',
    '/tmp/framework/sessions',
    '/tmp/framework/views',
    '/tmp/framework/cache',
    '/tmp/cache',
    '/tmp/logs'
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Ensure database sqlite exists in /tmp
$sourceDb = __DIR__ . '/../database/database.sqlite';
$targetDb = '/tmp/database.sqlite';

if (!file_exists($targetDb)) {
    if (file_exists($sourceDb)) {
        @copy($sourceDb, $targetDb);
    } else {
        @touch($targetDb);
    }
}

require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require __DIR__ . '/../bootstrap/app.php';

// Override storage and bootstrap paths before running
$app->useStoragePath('/tmp');
$app->useBootstrapPath('/tmp');

// Check if database tables exist, otherwise run migrations & seeders automatically
if (!file_exists('/tmp/.migrated')) {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
            '--force' => true,
            '--seed' => true,
        ]);
        @touch('/tmp/.migrated');
    } catch (\Throwable $e) {}
}

$app->handleRequest(Request::capture());
