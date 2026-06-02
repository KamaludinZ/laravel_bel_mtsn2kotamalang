<?php

/**
 * Script untuk memperbaiki masalah CSS production
 * Jalankan di server production: php fix-production.php
 */

echo "========================================\n";
echo "Fix Production CSS/JS Loading Issue\n";
echo "========================================\n\n";

// Check if we're in Laravel root
if (!file_exists('artisan')) {
    die("❌ Error: artisan file not found. Run this from Laravel root directory.\n");
}

echo "Step 1: Checking environment...\n";
echo "----------------------------------------\n";

// Load .env file
if (!file_exists('.env')) {
    die("❌ Error: .env file not found!\n");
}

$envContent = file_get_contents('.env');
preg_match('/APP_ENV=(.+)/', $envContent, $envMatches);
preg_match('/APP_DEBUG=(.+)/', $envContent, $debugMatches);
preg_match('/APP_URL=(.+)/', $envContent, $urlMatches);

$appEnv = isset($envMatches[1]) ? trim($envMatches[1]) : 'NOT SET';
$appDebug = isset($debugMatches[1]) ? trim($debugMatches[1]) : 'NOT SET';
$appUrl = isset($urlMatches[1]) ? trim($urlMatches[1]) : 'NOT SET';

echo "Current .env settings:\n";
echo "  APP_ENV = {$appEnv}\n";
echo "  APP_DEBUG = {$appDebug}\n";
echo "  APP_URL = {$appUrl}\n\n";

// Check if production
if ($appEnv !== 'production') {
    echo "⚠️  WARNING: APP_ENV is not 'production'!\n";
    echo "Please update .env file:\n";
    echo "  APP_ENV=production\n\n";

    $answer = readline("Do you want me to update it now? (yes/no): ");
    if (strtolower(trim($answer)) === 'yes') {
        $envContent = preg_replace('/APP_ENV=.+/', 'APP_ENV=production', $envContent);
        file_put_contents('.env', $envContent);
        echo "✅ Updated APP_ENV to production\n\n";
    }
}

// Check if debug is false
if ($appDebug !== 'false') {
    echo "⚠️  WARNING: APP_DEBUG is not 'false'!\n";
    echo "Please update .env file:\n";
    echo "  APP_DEBUG=false\n\n";

    $answer = readline("Do you want me to update it now? (yes/no): ");
    if (strtolower(trim($answer)) === 'yes') {
        $envContent = file_get_contents('.env');
        $envContent = preg_replace('/APP_DEBUG=.+/', 'APP_DEBUG=false', $envContent);
        file_put_contents('.env', $envContent);
        echo "✅ Updated APP_DEBUG to false\n\n";
    }
}

echo "\nStep 2: Checking build files...\n";
echo "----------------------------------------\n";

$manifestPath = 'public/build/manifest.json';
if (!file_exists($manifestPath)) {
    echo "❌ Error: public/build/manifest.json not found!\n";
    echo "You need to run 'npm run build' first.\n";
    die();
}

$manifest = json_decode(file_get_contents($manifestPath), true);
echo "✅ Found manifest.json\n";
echo "Build files:\n";
foreach ($manifest as $key => $value) {
    $filePath = 'public/build/' . $value['file'];
    $exists = file_exists($filePath) ? '✅' : '❌';
    $size = file_exists($filePath) ? round(filesize($filePath) / 1024, 2) . ' KB' : 'NOT FOUND';
    echo "  {$exists} {$value['file']} ({$size})\n";
}
echo "\n";

echo "Step 3: Clearing all caches...\n";
echo "----------------------------------------\n";

$commands = [
    'config:clear' => 'Configuration cache',
    'cache:clear' => 'Application cache',
    'view:clear' => 'View cache',
    'route:clear' => 'Route cache',
    'event:clear' => 'Event cache',
];

foreach ($commands as $cmd => $desc) {
    echo "Clearing {$desc}...\n";
    $output = shell_exec("php artisan {$cmd} 2>&1");
    echo "  " . trim($output) . "\n";
}

// Delete config cache file manually
$configCache = 'bootstrap/cache/config.php';
if (file_exists($configCache)) {
    unlink($configCache);
    echo "✅ Deleted bootstrap/cache/config.php manually\n";
}

// Delete services cache
$servicesCache = 'bootstrap/cache/services.php';
if (file_exists($servicesCache)) {
    unlink($servicesCache);
    echo "✅ Deleted bootstrap/cache/services.php manually\n";
}

echo "\n";

echo "Step 4: Re-caching for production...\n";
echo "----------------------------------------\n";

$cacheCommands = [
    'config:cache' => 'Configuration',
];

foreach ($cacheCommands as $cmd => $desc) {
    echo "Caching {$desc}...\n";
    $output = shell_exec("php artisan {$cmd} 2>&1");
    echo "  " . trim($output) . "\n";
}

echo "\n";

echo "Step 5: Verifying configuration...\n";
echo "----------------------------------------\n";

// Bootstrap Laravel to check actual config
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$actualEnv = app()->environment();
$actualDebug = config('app.debug');
$actualUrl = config('app.url');

echo "Loaded configuration:\n";
echo "  app.env = {$actualEnv}\n";
echo "  app.debug = " . ($actualDebug ? 'true' : 'false') . "\n";
echo "  app.url = {$actualUrl}\n\n";

if ($actualEnv !== 'production') {
    echo "❌ STILL WRONG! Environment is still '{$actualEnv}'\n";
    echo "This means Laravel is not reading the updated .env file.\n";
    echo "\nTry these steps:\n";
    echo "1. Make sure .env file is saved\n";
    echo "2. Restart web server (Apache/Nginx/PHP-FPM)\n";
    echo "3. Run: php artisan optimize:clear\n";
    echo "4. Run: php artisan config:cache\n";
} else {
    echo "✅ Environment is correctly set to 'production'\n";
}

echo "\n";
echo "========================================\n";
echo "Summary\n";
echo "========================================\n\n";

echo "✅ All caches cleared\n";
echo "✅ Build files verified\n";
echo "✅ Configuration re-cached\n\n";

if ($actualEnv === 'production' && !$actualDebug) {
    echo "🎉 All good! Your app should now load CSS/JS from /build/assets/\n\n";
    echo "Next steps:\n";
    echo "1. Restart your web server (important!):\n";
    echo "   - Apache: sudo systemctl restart apache2\n";
    echo "   - Nginx: sudo systemctl restart nginx\n";
    echo "   - PHP-FPM: sudo systemctl restart php8.2-fpm\n";
    echo "2. Hard refresh browser: Ctrl+F5\n";
    echo "3. Check browser console - should NOT see [::1]:5173 anymore\n\n";
} else {
    echo "⚠️  There are still issues. Please check the output above.\n\n";
}

echo "If still not working, check TROUBLESHOOTING.md for more help.\n";
