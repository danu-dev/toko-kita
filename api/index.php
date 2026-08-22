<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Ensure tmp directories exist
if (!is_dir('/tmp/views')) {
    @mkdir('/tmp/views', 0755, true);
}

// Copy seed database to /tmp if it doesn't exist
$sourceDb = __DIR__ . '/../database/database.sqlite';
$targetDb = '/tmp/database.sqlite';

if (!file_exists($targetDb)) {
    if (file_exists($sourceDb)) {
        @copy($sourceDb, $targetDb);
    } else {
        @touch($targetDb);
    }
}

// Clean any leftover hot file
if (file_exists(__DIR__ . '/../public/hot')) {
    @unlink(__DIR__ . '/../public/hot');
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
