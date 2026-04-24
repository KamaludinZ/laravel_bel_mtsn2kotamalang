<?php

/**
 * Entry point for Vercel Serverless Function
 * This file serves as the main entry point for the Laravel application on Vercel
 */

// Set the Laravel base path
$basePath = __DIR__ . '/..';

// Require Laravel's autoloader
require $basePath . '/vendor/autoload.php';

// Create the Laravel application
$app = require_once $basePath . '/bootstrap/app.php';

// Handle the request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
)->send();

$kernel->terminate($request, $response);

