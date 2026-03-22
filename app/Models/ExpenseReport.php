<?php
/**
 * ExpenseReport Model
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Models;

use App\Helpers\Database;
use PDO;

class ExpenseReport extends Model
{
    protected string $table = 'expense_reports';

    /**
     * Get all reports with calculated totals from linked expenses
     */
    public function getWithTotals(): array
    {
        $sql = "SELECT r.*, COALESCE(SUM(e.amount), 0) AS calculated_total, COUNT(e.id) AS expense_count
                FROM {$this->table} r
                LEFT JOIN expenses e ON e.report_id = r.id
                GROUP BY r.id
                ORDER BY r.created_at DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Recalculate and update the total_amount from linked expenses
     */
    public function updateTotal(int $id): void
    {
        $sql = "UPDATE {$this->table}
                SET total_amount = (
                    SELECT COALESCE(SUM(amount), 0)
                    FROM expenses
                    WHERE report_id = :id
                )
                WHERE id = :report_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'report_id' => $id]);
    }
}
