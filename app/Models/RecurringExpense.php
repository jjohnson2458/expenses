<?php
/**
 * RecurringExpense Model
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Models;

use App\Helpers\Database;
use PDO;

class RecurringExpense extends Model
{
    protected string $table = 'recurring_expenses';

    /**
     * Get all active recurring expenses
     */
    public function getActive(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Get recurring expenses that are due for processing
     *
     * Returns records where last_processed is null or before the current month, and is_active=1
     */
    public function getDueForProcessing(string $currentDate): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE is_active = 1
                AND (
                    last_processed IS NULL
                    OR DATE_FORMAT(last_processed, '%Y-%m') < DATE_FORMAT(:current_date, '%Y-%m')
                )
                ORDER BY id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['current_date' => $currentDate]);
        return $stmt->fetchAll();
    }

    /**
     * Mark a recurring expense as processed on a given date
     */
    public function markProcessed(int $id, string $date): void
    {
        $this->update($id, ['last_processed' => $date]);
    }
}
