<?php
/**
 * Expense Model
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Models;

use App\Helpers\Database;
use PDO;

class Expense extends Model
{
    protected string $table = 'expenses';

    /**
     * Get all expenses for a specific report
     */
    public function getByReport(int $reportId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE report_id = :report_id ORDER BY expense_date DESC");
        $stmt->execute(['report_id' => $reportId]);
        return $stmt->fetchAll();
    }

    /**
     * Get expenses within a date range, optionally filtered by user
     */
    public function getByDateRange(string $from, string $to, ?int $userId = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE expense_date BETWEEN :from AND :to";
        $params = ['from' => $from, 'to' => $to];

        if ($userId !== null) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $userId;
        }

        $sql .= " ORDER BY expense_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get total amounts grouped by category with optional date filtering
     */
    public function getTotalByCategory(?string $from = null, ?string $to = null): array
    {
        $sql = "SELECT e.category_id, c.name AS category_name, SUM(e.amount) AS total_amount, COUNT(*) AS expense_count
                FROM {$this->table} e
                LEFT JOIN expense_categories c ON e.category_id = c.id";

        $params = [];
        $conditions = [];

        if ($from !== null) {
            $conditions[] = "e.expense_date >= :from";
            $params['from'] = $from;
        }
        if ($to !== null) {
            $conditions[] = "e.expense_date <= :to";
            $params['to'] = $to;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " GROUP BY e.category_id, c.name ORDER BY total_amount DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get monthly expense totals for charting
     */
    public function getMonthlyTotals(int $months = 12): array
    {
        $sql = "SELECT DATE_FORMAT(expense_date, '%Y-%m') AS month,
                       SUM(amount) AS total_amount,
                       COUNT(*) AS expense_count
                FROM {$this->table}
                WHERE expense_date >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(expense_date, '%Y-%m')
                ORDER BY month ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':months', $months, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get recent expenses with category name joined
     */
    public function getRecentExpenses(int $limit = 10): array
    {
        $sql = "SELECT e.*, c.name AS category_name
                FROM {$this->table} e
                LEFT JOIN expense_categories c ON e.category_id = c.id
                ORDER BY e.expense_date DESC, e.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get dashboard statistics
     *
     * @return array Contains: total_this_month, total_last_month, total_credits, total_debits, count
     */
    public function getDashboardStats(): array
    {
        $sql = "SELECT
                    COALESCE(SUM(CASE WHEN DATE_FORMAT(expense_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN amount ELSE 0 END), 0) AS total_this_month,
                    COALESCE(SUM(CASE WHEN DATE_FORMAT(expense_date, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m') THEN amount ELSE 0 END), 0) AS total_last_month,
                    COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END), 0) AS total_credits,
                    COALESCE(SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END), 0) AS total_debits,
                    COUNT(*) AS count
                FROM {$this->table}";

        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
}
