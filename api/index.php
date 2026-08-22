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

// Copy sqlite database to /tmp
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

$app = require __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath('/tmp');
$app->useBootstrapPath('/tmp');

// Create request and handle with catch block that exposes the actual error
$request = Request::capture();

try {
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
} catch (\Throwable $e) {
    header('HTTP/1.1 200 OK', true, 200);
    header('Content-Type: text/html');
    echo '<div style="background:#111;color:#ff5555;padding:30px;font-family:monospace;white-space:pre-wrap;font-size:14px;">';
    echo "<h1>LARAVEL VERCEL ERROR INSPECTION</h1>\n\n";
    echo "<b>EXCEPTION:</b> " . get_class($e) . "\n";
    echo "<b>MESSAGE:</b> " . $e->getMessage() . "\n";
    echo "<b>FILE:</b> " . $e->getFile() . " : line " . $e->getLine() . "\n\n";
    echo "<b>STACK TRACE:</b>\n" . $e->getTraceAsString();
    echo '</div>';
}
