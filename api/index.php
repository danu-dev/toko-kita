<?php

define('LARAVEL_START', microtime(true));

// Setup environment and paths for Vercel Serverless execution
putenv('APP_NAME=TokoKita');
putenv('APP_ENV=production');
putenv('APP_DEBUG=false');
putenv('APP_KEY=base64:XG8o9U0b+Tok0K1taUMKMHyperLocaL20260822A4Yv9g=');
putenv('LOG_CHANNEL=stderr');
putenv('VIEW_COMPILED_PATH=/tmp/framework/views');
putenv('SESSION_DRIVER=cookie');
putenv('CACHE_STORE=array');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=/tmp/database.sqlite');
putenv('APP_CONFIG_CACHE=/tmp/config.php');
putenv('APP_EVENTS_CACHE=/tmp/events.php');
putenv('APP_PACKAGES_CACHE=/tmp/packages.php');
putenv('APP_ROUTES_CACHE=/tmp/routes.php');
putenv('APP_SERVICES_CACHE=/tmp/services.php');

$_ENV['APP_NAME'] = 'TokoKita';
$_ENV['APP_ENV'] = 'production';
$_ENV['APP_DEBUG'] = 'false';
$_ENV['APP_KEY'] = 'base64:XG8o9U0b+Tok0K1taUMKMHyperLocaL20260822A4Yv9g=';
$_ENV['LOG_CHANNEL'] = 'stderr';
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/framework/views';
$_ENV['SESSION_DRIVER'] = 'cookie';
$_ENV['CACHE_STORE'] = 'array';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = '/tmp/database.sqlite';

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

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath('/tmp');
$app->useBootstrapPath('/tmp');

$app->booted(function () use ($app) {
    $app['config']->set('app.key', 'base64:XG8o9U0b+Tok0K1taUMKMHyperLocaL20260822A4Yv9g=');
    $app['config']->set('view.compiled', '/tmp/framework/views');
    $app['config']->set('session.driver', 'cookie');
    $app['config']->set('cache.default', 'array');
    $app['config']->set('logging.default', 'stderr');
});

$app->handleRequest(\Illuminate\Http\Request::capture());
