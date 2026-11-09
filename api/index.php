<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

if (isset($_ENV['VERCEL']) || getenv('VERCEL')) {
    $storagePath = '/tmp/storage';

    foreach ([
        $storagePath.'/app/public',
        $storagePath.'/framework/cache/data',
        $storagePath.'/framework/sessions',
        $storagePath.'/framework/views',
        $storagePath.'/logs',
        '/tmp/views',
    ] as $dir) {
        if (! is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
    }

    $app->useStoragePath($storagePath);

    $dbPath = getenv('DB_DATABASE') ?: '/tmp/database.sqlite';
    if ((getenv('DB_CONNECTION') ?: 'sqlite') === 'sqlite' && ! file_exists($dbPath)) {
        @touch($dbPath);
    }

    $app->booted(function () {
        URL::forceScheme('https');

        if (config('database.default') === 'sqlite' && ! Schema::hasTable('users')) {
            Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
        }
    });
}

$app->handleRequest(Request::capture());

