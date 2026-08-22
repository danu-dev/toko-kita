<?php

define('LARAVEL_START', microtime(true));

$dirs = [
    '/tmp/framework/views',
    '/tmp/framework/sessions',
    '/tmp/framework/cache',
    '/tmp/views',
    '/tmp/cache',
    '/tmp/logs'
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Copy pre-built package and service caches
$cachedFiles = ['packages.php', 'services.php'];
foreach ($cachedFiles as $file) {
    $src = __DIR__ . '/../bootstrap/cache/' . $file;
    if (file_exists($src)) {
        @copy($src, '/tmp/' . $file);
    }
}

// Copy sqlite database to /tmp if not present
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

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__ . '/../bootstrap/app.php';

$app->handleRequest(\Illuminate\Http\Request::capture());
