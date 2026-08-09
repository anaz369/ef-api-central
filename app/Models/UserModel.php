<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'tbl_users';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name', 'email', 'password_hash', 'type', 'company_name',
        'totp_secret', 'totp_enabled', 'force_password_change',
        'is_active', 'last_login_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    const TYPE_ADMIN       = 0;
    const TYPE_SUPER_ADMIN = 1;

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function getActive(): array
    {
        return $this->where('is_active', 1)->orderBy('created_at', 'DESC')->findAll();
    }

    public function updateLastLogin(int $id): void
    {
        $this->update($id, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Generate a random temporary password.
     */
    public static function generateTempPassword(): string
    {
        return bin2hex(random_bytes(8)); // 16 hex chars
    }
}
