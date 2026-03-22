<?php
/**
 * Category Model
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Models;

use App\Helpers\Database;
use PDO;

class Category extends Model
{
    protected string $table = 'expense_categories';

    /**
     * Get all active categories ordered by sort_order
     */
    public function getActive(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    }

    /**
     * Update sort order for multiple categories
     *
     * @param array $orders Associative array of [id => sort_order]
     */
    public function updateSortOrder(array $orders): void
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET sort_order = :sort_order WHERE id = :id");
        foreach ($orders as $id => $sortOrder) {
            $stmt->execute([
                'id' => $id,
                'sort_order' => $sortOrder,
            ]);
        }
    }
}
