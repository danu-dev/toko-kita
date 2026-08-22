<?php

define('LARAVEL_START', microtime(true));

// Ensure required tmp directories exist in Vercel runtime
$dirs = [
    '/tmp/views',
    '/tmp/framework/views',
    '/tmp/framework/sessions',
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

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath('/tmp');
$app->useBootstrapPath('/tmp');

// Configure session, cache, and database dynamically in runtime container
$app->booting(function () use ($app) {
    $app['config']->set('session.driver', 'array');
    $app['config']->set('cache.default', 'array');
    $app['config']->set('logging.default', 'errorlog');
    $app['config']->set('view.compiled', '/tmp/framework/views');
    $app['config']->set('database.default', 'sqlite');
    $app['config']->set('database.connections.sqlite.database', '/tmp/database.sqlite');
});

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
