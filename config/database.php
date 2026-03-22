<?php
/**
 * Database Configuration
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

return [
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'expenses'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'test_database' => env('DB_TEST_DATABASE', 'expenses_test'),
];
