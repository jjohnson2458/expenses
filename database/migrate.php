<?php
/**
 * Migration Runner
 *
 * Reads and executes SQL files from database/migrations/ in order.
 * Tracks executed migrations in the migrations table to avoid re-running.
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

require_once dirname(__DIR__) . '/config/app.php';

use App\Helpers\Database;

require_once BASE_PATH . '/app/Helpers/Database.php';

$db = Database::getInstance();

// Ensure migrations table exists first (bootstrap it directly)
$db->exec("CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `migration` VARCHAR(255) NOT NULL,
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Get already-executed migrations
$executed = $db->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

// Scan migration files
$migrationsDir = __DIR__ . '/migrations';
$files = glob($migrationsDir . '/*.sql');
sort($files);

$count = 0;

foreach ($files as $file) {
    $filename = basename($file);

    if (in_array($filename, $executed)) {
        echo "SKIP: {$filename} (already executed)\n";
        continue;
    }

    $sql = file_get_contents($file);

    if (empty(trim($sql))) {
        echo "SKIP: {$filename} (empty file)\n";
        continue;
    }

    try {
        $db->exec($sql);
        $stmt = $db->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
        $stmt->execute(['migration' => $filename]);
        echo "  OK: {$filename}\n";
        $count++;
    } catch (PDOException $e) {
        echo "FAIL: {$filename} - " . $e->getMessage() . "\n";
        exit(1);
    }
}

if ($count === 0) {
    echo "\nNothing to migrate. All migrations are up to date.\n";
} else {
    echo "\nCompleted {$count} migration(s) successfully.\n";
}
