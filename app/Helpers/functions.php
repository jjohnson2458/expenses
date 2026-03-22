<?php
/**
 * Global helper functions
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

function url(string $path = ''): string
{
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url($path);
}

function csrf_field(): string
{
    $token = $_SESSION['csrf_token'] ?? '';
    return '<input type="hidden" name="_token" value="' . htmlspecialchars($token) . '">';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function method_field(string $method): string
{
    return '<input type="hidden" name="_method" value="' . htmlspecialchars(strtoupper($method)) . '">';
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function old(string $key, string $default = ''): string
{
    return htmlspecialchars($_SESSION['old_input'][$key] ?? $default, ENT_QUOTES, 'UTF-8');
}

function __t(string $key, string $default = ''): string
{
    static $translations = null;
    $lang = $_SESSION['lang'] ?? DEFAULT_LANG;

    if ($translations === null) {
        $file = LANG_PATH . "/{$lang}/messages.php";
        $translations = file_exists($file) ? require $file : [];
    }

    return $translations[$key] ?? $default ?: $key;
}

function format_currency(float $amount): string
{
    return '$' . number_format($amount, 2);
}

function format_date(string $date, string $format = 'M j, Y'): string
{
    return date($format, strtotime($date));
}

function is_current_page(string $path): bool
{
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return rtrim($uri, '/') === rtrim($path, '/');
}

function log_error(string $message, array $context = []): void
{
    $logFile = STORAGE_PATH . '/logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    file_put_contents($logFile, "[{$timestamp}] {$message} {$contextStr}\n", FILE_APPEND | LOCK_EX);

    // Also log to database if available
    try {
        $db = \App\Helpers\Database::getInstance();
        $stmt = $db->prepare("INSERT INTO error_logs (message, context, created_at) VALUES (:message, :context, NOW())");
        $stmt->execute(['message' => $message, 'context' => $contextStr]);
    } catch (\Exception $e) {
        // Silently fail if DB not available
    }

    // Send error email
    $errorEmail = env('ERROR_EMAIL', '');
    if ($errorEmail && APP_ENV === 'production') {
        $messengerPath = 'C:/xampp/htdocs/claude_messenger/notify.php';
        if (file_exists($messengerPath)) {
            $subject = escapeshellarg("Error in " . APP_NAME);
            $body = escapeshellarg("<p><strong>Error:</strong> " . htmlspecialchars($message) . "</p><p>" . htmlspecialchars($contextStr) . "</p>");
            exec("php {$messengerPath} -s {$subject} -b {$body} -p claude_expenses 2>&1 &");
        }
    }
}
