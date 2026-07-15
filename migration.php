<?php
/**
 * migration.php
 * Run all SQL migration scripts located in the "migrations" directory.
 *
 * This version executes statements without wrapping each file in a transaction,
 * and it gracefully ignores duplicate column errors (MySQL error 1060) so
 * re‑running migrations does not abort the whole process.
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

/**
 * Retrieve all *.sql files inside the migrations folder, sorted alphabetically.
 */
function getMigrations(string $dir): array {
    $files = glob(rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . '*.sql');
    sort($files, SORT_STRING);
    return $files;
}

/**
 * Execute a single SQL statement, ignoring duplicate column errors.
 */
function execStatement(PDO $pdo, string $stmt): void {
    $stmt = trim($stmt);
    if ($stmt === '') return;
    try {
        $pdo->exec($stmt);
    } catch (PDOException $e) {
        // MySQL error 1060 = Duplicate column name
        if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1060) {
            echo "[WARN] Duplicate column ignored: {$e->getMessage()}\n";
        } else {
            echo "[ERROR] Statement failed: {$e->getMessage()}\n";
        }
    }
}

/**
 * Run a migration file – split by semicolons and execute each statement.
 */
function runMigration(PDO $pdo, string $filePath): void {
    echo "[INFO] Executing " . basename($filePath) . "...\n";
    $sql = file_get_contents($filePath);
    if ($sql === false) {
        echo "[ERROR] Unable to read $filePath\n";
        return;
    }
    $statements = preg_split('/;\s*\R/', $sql);
    foreach ($statements as $stmt) {
        execStatement($pdo, $stmt);
    }
    echo "[DONE] $filePath processed.\n";
}

try {
    $pdo = DB::getConnection();
    $migrationDir = __DIR__ . '/migrations';
    $files = getMigrations($migrationDir);
    if (empty($files)) {
        echo "[INFO] No migration files found in $migrationDir.\n";
        exit(0);
    }
    foreach ($files as $file) {
        runMigration($pdo, $file);
    }
    echo "[FINISH] All migrations completed.\n";
} catch (Exception $e) {
    echo "[CRITICAL] Migration runner failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
