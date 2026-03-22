<?php
/**
 * Database Seeder
 *
 * Seeds the database with initial data: admin user and default expense categories.
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

require_once dirname(__DIR__, 2) . '/config/app.php';

use App\Helpers\Database;

require_once BASE_PATH . '/app/Helpers/Database.php';

$db = Database::getInstance();

// --- Seed Admin User ---
$existingUser = $db->prepare("SELECT id FROM users WHERE email = :email");
$existingUser->execute(['email' => 'email4johnson@gmail.com']);

if (!$existingUser->fetch()) {
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
    $stmt->execute([
        'name' => 'Admin',
        'email' => 'email4johnson@gmail.com',
        'password' => password_hash('24AdaPlace', PASSWORD_BCRYPT),
        'role' => 'admin',
    ]);
    echo "  OK: Admin user created.\n";
} else {
    echo "SKIP: Admin user already exists.\n";
}

// --- Seed Default Expense Categories ---
$categories = [
    ['name' => 'Housing',           'name_es' => 'Vivienda',            'color' => '#0d6efd', 'icon' => 'bi-house',           'sort_order' => 1],
    ['name' => 'Transportation',    'name_es' => 'Transporte',          'color' => '#6610f2', 'icon' => 'bi-car-front',       'sort_order' => 2],
    ['name' => 'Food & Dining',     'name_es' => 'Comida y Restaurantes','color' => '#fd7e14', 'icon' => 'bi-cup-straw',       'sort_order' => 3],
    ['name' => 'Utilities',         'name_es' => 'Servicios',           'color' => '#ffc107', 'icon' => 'bi-lightning',        'sort_order' => 4],
    ['name' => 'Insurance',         'name_es' => 'Seguros',             'color' => '#20c997', 'icon' => 'bi-shield-check',     'sort_order' => 5],
    ['name' => 'Healthcare',        'name_es' => 'Salud',               'color' => '#dc3545', 'icon' => 'bi-heart-pulse',      'sort_order' => 6],
    ['name' => 'Entertainment',     'name_es' => 'Entretenimiento',     'color' => '#e91e8c', 'icon' => 'bi-controller',       'sort_order' => 7],
    ['name' => 'Personal',          'name_es' => 'Personal',            'color' => '#6f42c1', 'icon' => 'bi-person',           'sort_order' => 8],
    ['name' => 'Education',         'name_es' => 'Educacion',          'color' => '#0dcaf0', 'icon' => 'bi-book',             'sort_order' => 9],
    ['name' => 'Savings',           'name_es' => 'Ahorros',             'color' => '#198754', 'icon' => 'bi-piggy-bank',       'sort_order' => 10],
    ['name' => 'Debt Payments',     'name_es' => 'Pagos de Deuda',      'color' => '#842029', 'icon' => 'bi-credit-card',      'sort_order' => 11],
    ['name' => 'Clothing',          'name_es' => 'Ropa',                'color' => '#d63384', 'icon' => 'bi-bag',              'sort_order' => 12],
    ['name' => 'Gifts & Donations', 'name_es' => 'Regalos y Donaciones','color' => '#e35d6a', 'icon' => 'bi-gift',             'sort_order' => 13],
    ['name' => 'Travel',            'name_es' => 'Viajes',              'color' => '#3d8bfd', 'icon' => 'bi-airplane',         'sort_order' => 14],
    ['name' => 'Business',          'name_es' => 'Negocios',            'color' => '#495057', 'icon' => 'bi-briefcase',        'sort_order' => 15],
    ['name' => 'Miscellaneous',     'name_es' => 'Miscelaneo',         'color' => '#6c757d', 'icon' => 'bi-tag',              'sort_order' => 16],
];

$existingCategories = $db->query("SELECT COUNT(*) FROM expense_categories")->fetchColumn();

if ($existingCategories == 0) {
    $stmt = $db->prepare("INSERT INTO expense_categories (name, name_es, color, icon, sort_order) VALUES (:name, :name_es, :color, :icon, :sort_order)");

    foreach ($categories as $cat) {
        $stmt->execute($cat);
    }
    echo "  OK: " . count($categories) . " expense categories created.\n";
} else {
    echo "SKIP: Expense categories already exist ({$existingCategories} found).\n";
}

echo "\nSeeding complete.\n";
