<?php

try {
    $pdo = new PDO(
        "pgsql:host=127.0.0.1;port=5432;dbname=bel_sekolah_mtsn2",
        "postgres",
        "postgres123"
    );
    echo "✓ Connected successfully to PostgreSQL!\n";
    echo "Database: bel_sekolah_mtsn2\n";

    // Test query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "PostgreSQL version: " . $version . "\n";

} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
}
