<?php
/**
 * PHPUnit Test Bootstrap
 *
 * Sets up the test environment: loads config, registers autoloader,
 * overrides DB to expenses_test, and runs migrations.
 */

// Force test database via environment before config loads
$_ENV['DB_DATABASE'] = 'expenses_test';
putenv('DB_DATABASE=expenses_test');
$_ENV['APP_ENV'] = 'testing';
putenv('APP_ENV=testing');

// Load application config (reads .env, defines constants)
require_once dirname(__DIR__) . '/config/app.php';

// Load helper functions
require_once BASE_PATH . '/app/Helpers/functions.php';

// Register the same PSR-4 autoloader used by public/index.php
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    if (str_starts_with($class, $prefix)) {
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Get a PDO connection to the test database (singleton for test suite)
 */
function getTestDb(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $config = require BASE_PATH . '/config/database.php';
        $database = $_ENV['DB_DATABASE'] ?? 'expenses_test';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $database,
            $config['charset']
        );

        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $pdo;
}

// Run migrations on the test database
$testDb = getTestDb();

$testDb->exec("CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `migration` VARCHAR(255) NOT NULL,
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$executed = $testDb->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
$files = glob(BASE_PATH . '/database/migrations/*.sql');
sort($files);

foreach ($files as $file) {
    $filename = basename($file);
    if (in_array($filename, $executed, true)) continue;
    $testDb->exec(file_get_contents($file));
    $stmt = $testDb->prepare("INSERT INTO migrations (migration) VALUES (:m)");
    $stmt->execute(['m' => $filename]);
}

// Inject the test DB into the singleton so all models use it
\App\Helpers\Database::setInstance($testDb);
