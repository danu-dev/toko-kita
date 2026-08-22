<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// Set serverless env
putenv('APP_ENV=production');
putenv('APP_DEBUG=true');
putenv('LOG_CHANNEL=stderr');
putenv('VIEW_COMPILED_PATH=/tmp/framework/views');
putenv('SESSION_DRIVER=cookie');
putenv('CACHE_STORE=array');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=/tmp/database.sqlite');
putenv('APP_PACKAGES_CACHE=/tmp/packages.php');
putenv('APP_SERVICES_CACHE=/tmp/services.php');

$_ENV['VIEW_COMPILED_PATH'] = '/tmp/framework/views';
$_ENV['APP_ENV'] = 'production';
$_ENV['APP_DEBUG'] = 'true';
$_ENV['LOG_CHANNEL'] = 'stderr';
$_ENV['SESSION_DRIVER'] = 'cookie';
$_ENV['CACHE_STORE'] = 'array';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = '/tmp/database.sqlite';
$_ENV['APP_PACKAGES_CACHE'] = '/tmp/packages.php';
$_ENV['APP_SERVICES_CACHE'] = '/tmp/services.php';

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

try {
    $kernel = $app->make(Kernel::class);
    $request = Request::capture();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
} catch (\Throwable $e) {
    http_response_code(200);
    header('Content-Type: text/html');
    echo '<div style="background:#111;color:#ff5555;padding:30px;font-family:monospace;white-space:pre-wrap;font-size:14px;">';
    echo "<h1>EXCEPTION CAUGHT ON SERVERLESS ENTRY</h1>\n";
    echo "<b>Exception:</b> " . get_class($e) . "\n";
    echo "<b>Message:</b> " . $e->getMessage() . "\n";
    echo "<b>File:</b> " . $e->getFile() . " : line " . $e->getLine() . "\n\n";
    echo "<b>Trace:</b>\n" . $e->getTraceAsString();
    echo '</div>';
}
