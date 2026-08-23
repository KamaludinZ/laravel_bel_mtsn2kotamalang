<?php
// Simple PHP test - bypass Laravel completely
echo "PHP is working!<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

// Test database
try {
    $pdo = new PDO(
        'pgsql:host=postgres;port=5432;dbname=laravel_bel',
        'postgres',
        getenv('DB_PASSWORD') ?: 'secret'
    );
    echo "Database: Connected<br>";
} catch (PDOException $e) {
    echo "Database: ERROR - " . $e->getMessage() . "<br>";
}

// Test if Laravel bootstrap works
echo "<br>Testing Laravel bootstrap...<br>";
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "Laravel: Autoload OK<br>";

    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    echo "Laravel: Kernel OK<br>";

} catch (Throwable $e) {
    echo "Laravel ERROR: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
