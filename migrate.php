<?php
// Run this script from CLI via `php migrate.php` or from your browser
// It connects to the DB and executes all pending .sql scripts from the /migrations directory.

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

// If run from browser, output as plain text
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain');
}

try {
    $pdo = DB::getConnection();
    
    // 1. Ensure the migrations table exists to track what has already been run
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `migration_name` VARCHAR(255) NOT NULL UNIQUE,
            `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "Migrations table ready.\n";

    // 2. Fetch already executed migrations
    $stmt = $pdo->query("SELECT migration_name FROM migrations");
    $executedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 3. Scan the migrations directory
    $migrationsDir = __DIR__ . '/migrations';
    if (!is_dir($migrationsDir)) {
        die("Migrations directory not found.\n");
    }

    $files = scandir($migrationsDir);
    $migrationFiles = [];

    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $migrationFiles[] = $file;
        }
    }

    sort($migrationFiles); // Ensure they execute in alphabetical/numerical order
    
    $runCount = 0;

    // 4. Run pending migrations
    foreach ($migrationFiles as $file) {
        if (!in_array($file, $executedMigrations)) {
            echo "Executing migration: {$file}...\n";
            
            $filePath = $migrationsDir . '/' . $file;
            $sql = file_get_contents($filePath);
            
            if (empty(trim($sql))) {
                echo "Skipping empty file: {$file}\n";
                continue;
            }

            try {
                // Execute the SQL file contents
                // Using exec() handles multiple queries cleanly in most MySQL setups
                $pdo->exec($sql);
                
                // Record the migration as executed
                $insertStmt = $pdo->prepare("INSERT INTO migrations (migration_name) VALUES (?)");
                $insertStmt->execute([$file]);
                
                echo "Successfully executed {$file}.\n";
                $runCount++;
            } catch (Exception $e) {
                die("Error executing migration {$file}: " . $e->getMessage() . "\n");
            }
        }
    }

    if ($runCount === 0) {
        echo "No new migrations to execute.\n";
    } else {
        echo "Successfully executed {$runCount} migration(s).\n";
    }

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage() . "\n");
}
