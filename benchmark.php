<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$start = microtime(true);
$request = Illuminate\Http\Request::create('/login', 'GET');
$response = $app->handle($request);
$elapsed = round((microtime(true) - $start) * 1000, 2);

echo "Status: " . $response->getStatusCode() . " in " . $elapsed . "ms\n";

