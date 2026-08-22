<?php

// Force plain error rendering for diagnosis
ini_set('display_errors', '1');
error_reporting(E_ALL);

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

$app->useStoragePath('/tmp');
$app->useBootstrapPath('/tmp');

// Uncaught handler to output exact exception details
try {
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $request = \Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);

    if ($response->getStatusCode() === 500 && isset($response->exception) && $response->exception instanceof \Throwable) {
        throw $response->exception;
    }

    $response->send();
    $kernel->terminate($request, $response);
} catch (\Throwable $e) {
    header('HTTP/1.1 200 OK', true, 200);
    header('Content-Type: text/plain; charset=utf-8');
    echo "EXPLICIT SERVERLESS TRACE:\n";
    echo "Class: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "Trace:\n" . $e->getTraceAsString();
    exit;
}
