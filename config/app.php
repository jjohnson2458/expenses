<?php
/**
 * Application Configuration
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

// Load .env file
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

function env(string $key, $default = null)
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

define('APP_NAME', env('APP_NAME', 'MyExpenses'));
define('APP_ENV', env('APP_ENV', 'local'));
define('APP_DEBUG', env('APP_DEBUG', 'false') === 'true');
define('APP_URL', env('APP_URL', 'http://localhost'));
define('APP_KEY', env('APP_KEY', ''));
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('VIEW_PATH', BASE_PATH . '/resources/views');
define('LANG_PATH', BASE_PATH . '/lang');
define('DEFAULT_LANG', env('DEFAULT_LANG', 'en'));
