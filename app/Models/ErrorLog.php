<?php
/**
 * ErrorLog Model
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Models;

use App\Helpers\Database;
use PDO;

class ErrorLog extends Model
{
    protected string $table = 'error_logs';

    /**
     * Get recent error log entries
     */
    public function getRecent(int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Clear all error log entries
     */
    public function clearAll(): bool
    {
        $stmt = $this->db->exec("TRUNCATE TABLE {$this->table}");
        return $stmt !== false;
    }
}
