<?php
/**
 * User Model
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Models;

use App\Helpers\Database;
use PDO;

class User extends Model
{
    protected string $table = 'users';

    /**
     * Find a user by email address
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Verify a password against a hash
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Update a user's password
     */
    public function updatePassword(int $id, string $password): bool
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        return $this->update($id, ['password' => $hash]);
    }
}
