<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

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

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Override storage and bootstrap paths before running
$app->useStoragePath('/tmp');
$app->useBootstrapPath('/tmp');

// Check if database tables exist, otherwise run migrations & seeders automatically
try {
    if (!file_exists('/tmp/.migrated')) {
        Artisan::call('migrate:fresh', [
            '--force' => true,
            '--seed' => true,
        ]);
        @touch('/tmp/.migrated');
    }
} catch (\Throwable $e) {
    // Ignore migration error if already seeded
}

require __DIR__ . '/../public/index.php';
