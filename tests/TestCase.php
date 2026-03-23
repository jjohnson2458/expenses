<?php
/**
 * Base TestCase — uses truncation cleanup instead of transactions
 * to avoid InnoDB lock contention with foreign keys.
 */

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use App\Helpers\Database;
use PDO;

abstract class TestCase extends BaseTestCase
{
    protected PDO $db;

    /** Tables to clean after each test, in FK-safe order */
    private static array $tables = [
        'expenses',
        'recurring_expenses',
        'expense_reports',
        'expense_categories',
        'users',
        'error_logs',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = getTestDb();
        Database::setInstance($this->db);
    }

    protected function tearDown(): void
    {
        // Clean all tables after each test (FK-safe order, DELETE not TRUNCATE to avoid implicit commit issues)
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach (self::$tables as $table) {
            $this->db->exec("TRUNCATE TABLE `{$table}`");
        }
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');

        $_SESSION = ['csrf_token' => $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32))];

        parent::tearDown();
    }

    protected function actingAsAdmin(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Admin User';
        $_SESSION['user_email'] = 'admin@test.com';
        $_SESSION['user_role'] = 'admin';
    }

    protected function actingAsUser(): void
    {
        $_SESSION['user_id'] = 2;
        $_SESSION['user_name'] = 'Test User';
        $_SESSION['user_email'] = 'user@test.com';
        $_SESSION['user_role'] = 'user';
    }

    protected function createTestUser(array $overrides = []): array
    {
        $data = array_merge([
            'name' => 'Test User',
            'email' => 'testuser_' . uniqid() . '@test.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'user',
        ], $overrides);

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $stmt = $this->db->prepare("INSERT INTO users ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);

        $id = (int) $this->db->lastInsertId();
        return $this->db->query("SELECT * FROM users WHERE id = {$id}")->fetch();
    }

    protected function createTestCategory(array $overrides = []): array
    {
        $data = array_merge([
            'name' => 'Test Category ' . uniqid(),
            'description' => 'A test category',
            'color' => '#007bff',
            'icon' => 'bi-tag',
            'sort_order' => 0,
            'is_active' => 1,
        ], $overrides);

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $stmt = $this->db->prepare("INSERT INTO expense_categories ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);

        $id = (int) $this->db->lastInsertId();
        return $this->db->query("SELECT * FROM expense_categories WHERE id = {$id}")->fetch();
    }

    protected function createTestExpense(array $overrides = []): array
    {
        if (!isset($overrides['user_id'])) {
            $user = $this->createTestUser();
            $overrides['user_id'] = $user['id'];
        }
        if (!isset($overrides['category_id'])) {
            $category = $this->createTestCategory();
            $overrides['category_id'] = $category['id'];
        }

        $data = array_merge([
            'description' => 'Test Expense ' . uniqid(),
            'amount' => 25.50,
            'type' => 'debit',
            'expense_date' => date('Y-m-d'),
            'vendor' => 'Test Vendor',
            'notes' => 'Test notes',
            'is_recurring' => 0,
        ], $overrides);

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $stmt = $this->db->prepare("INSERT INTO expenses ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);

        $id = (int) $this->db->lastInsertId();
        return $this->db->query("SELECT * FROM expenses WHERE id = {$id}")->fetch();
    }

    protected function createTestReport(array $overrides = []): array
    {
        if (!isset($overrides['user_id'])) {
            $user = $this->createTestUser();
            $overrides['user_id'] = $user['id'];
        }

        $data = array_merge([
            'title' => 'Test Report ' . uniqid(),
            'description' => 'A test report',
            'status' => 'draft',
            'date_from' => date('Y-m-01'),
            'date_to' => date('Y-m-t'),
            'total_amount' => 0.00,
        ], $overrides);

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $stmt = $this->db->prepare("INSERT INTO expense_reports ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);

        $id = (int) $this->db->lastInsertId();
        return $this->db->query("SELECT * FROM expense_reports WHERE id = {$id}")->fetch();
    }
}
