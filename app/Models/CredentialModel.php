<?php

namespace App\Models;

use CodeIgniter\Model;

class CredentialModel extends Model
{
    protected $table      = 'tbl_api_credentials';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id', 'participant_id',
        'key_name', 'key_description', 'request_status',
        'client_id', 'client_secret_hash',
        'client_secret_preview', 'environment', 'is_active', 'last_used_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    const ENV_DEVELOPMENT = 0;
    const ENV_PRODUCTION  = 1;

    // ── Per-user (standard) ──────────────────────────────────────────────

    public function getByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->where('is_active', 1)
                    ->findAll();
    }

    public function getForUserEnv(int $userId, int $environment): ?array
    {
        return $this->where('user_id', $userId)
                    ->where('environment', $environment)
                    ->where('is_active', 1)
                    ->first();
    }

    public function getAllByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function getPendingByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->where('request_status', 'pending')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function getAllPending(): array
    {
        return $this->db->table('tbl_api_credentials c')
            ->select('c.*, u.name AS user_name, u.email AS user_email')
            ->join('tbl_users u', 'u.id = c.user_id', 'left')
            ->where('c.request_status', 'pending')
            ->orderBy('c.created_at', 'ASC')
            ->get()->getResultArray();
    }

    // ── Legacy per-participant (kept for backward compat) ────────────────

    public function getByParticipant(int $participantId): array
    {
        return $this->where('participant_id', $participantId)
                    ->where('is_active', 1)
                    ->findAll();
    }

    public function getForParticipantEnv(int $participantId, int $environment): ?array
    {
        return $this->where('participant_id', $participantId)
                    ->where('environment', $environment)
                    ->where('is_active', 1)
                    ->first();
    }

    public static function generatePair(): array
    {
        $clientId     = 'eca_' . bin2hex(random_bytes(16));
        $clientSecret = bin2hex(random_bytes(24));
        return [$clientId, $clientSecret];
    }
}
