<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

try {
    putenv('APP_ENV=production');
    putenv('APP_DEBUG=true');
    putenv('LOG_CHANNEL=stderr');
    putenv('VIEW_COMPILED_PATH=/tmp/views');
    putenv('DB_CONNECTION=sqlite');
    putenv('DB_DATABASE=/tmp/database.sqlite');
    putenv('SESSION_DRIVER=cookie');
    putenv('CACHE_STORE=array');

    if (!is_dir('/tmp/views')) {
        @mkdir('/tmp/views', 0777, true);
    }

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

    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = \Illuminate\Http\Request::capture()
    );
    $response->send();
    $kernel->terminate($request, $response);
} catch (\Throwable $e) {
    http_response_code(200);
    echo '<pre style="background:#1e1e1e;color:#ff6b6b;padding:20px;border-radius:10px;font-family:monospace;white-space:pre-wrap;">';
    echo "ERROR: " . $e->getMessage() . "\n\n";
    echo "FILE: " . $e->getFile() . " on line " . $e->getLine() . "\n\n";
    echo "TRACE:\n" . $e->getTraceAsString();
    echo '</pre>';
}
