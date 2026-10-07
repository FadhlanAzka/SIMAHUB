<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Artisan;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

Artisan::call('route:clear');
Artisan::call('config:clear');
Artisan::call('cache:clear');
Artisan::call('view:clear');

echo 'Cache cleared successfully!';
