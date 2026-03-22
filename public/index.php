<?php
/**
 * MyExpenses - Application Entry Point
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

// Autoloader
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

// Load config
require_once dirname(__DIR__) . '/config/app.php';
require_once BASE_PATH . '/app/Helpers/functions.php';

// Start session
session_start();

// Generate CSRF token if not present
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set default language
if (empty($_SESSION['lang'])) {
    $_SESSION['lang'] = DEFAULT_LANG;
}

// Load routes
require_once BASE_PATH . '/routes/web.php';

// Dispatch
\App\Helpers\Router::dispatch();
