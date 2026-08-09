<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginLogModel extends Model
{
    protected $table      = 'tbl_login_logs';
    protected $primaryKey = 'id';

    protected $allowedFields = ['user_id', 'email', 'ip_address', 'user_agent', 'status', 'created_at'];

    protected $useTimestamps = false;

    // Allowed status values
    const STATUS_SUCCESS          = 'success';
    const STATUS_FAILED           = 'failed';
    const STATUS_TOTP_FAILED      = 'totp_failed';
    const STATUS_PASSWORD_CHANGED = 'password_changed';

    public function record(array $data): void
    {
        $this->insert([
            'user_id'    => $data['user_id'] ?? null,
            'email'      => $data['email'],
            'ip_address' => $data['ip_address'],
            'user_agent' => substr($data['user_agent'] ?? '', 0, 500),
            'status'     => $data['status'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getByUser(int $userId, int $limit = 10): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit);
    }
}
