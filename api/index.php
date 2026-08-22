<?php

use Illuminate\Support\Facades\Artisan;

// Set up serverless writable tmp directories
$_ENV['APP_CONFIG_CACHE'] = '/tmp/config.php';
$_ENV['APP_EVENTS_CACHE'] = '/tmp/events.php';
$_ENV['APP_PACKAGES_CACHE'] = '/tmp/packages.php';
$_ENV['APP_ROUTES_CACHE'] = '/tmp/routes.php';
$_ENV['APP_SERVICES_CACHE'] = '/tmp/services.php';
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/views';
$_ENV['LOG_CHANNEL'] = 'errorlog';
$_ENV['SESSION_DRIVER'] = 'cookie';
$_ENV['CACHE_STORE'] = 'array';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = '/tmp/database.sqlite';

if (!file_exists('/tmp/views')) {
    mkdir('/tmp/views', 0755, true);
}

// Copy seed database to /tmp if it doesn't exist
$sourceDb = __DIR__ . '/../database/database.sqlite';
$targetDb = '/tmp/database.sqlite';

if (!file_exists($targetDb)) {
    if (file_exists($sourceDb)) {
        copy($sourceDb, $targetDb);
    } else {
        touch($targetDb);
    }
}

require __DIR__ . '/../public/index.php';
