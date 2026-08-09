<?php

namespace App\Models;

use CodeIgniter\Model;

class CredentialModel extends Model
{
    protected $table      = 'tbl_participant_credentials';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'participant_id', 'client_id', 'client_secret_hash',
        'client_secret_preview', 'environment', 'is_active', 'last_used_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Environment constants
    const ENV_DEVELOPMENT = 0;
    const ENV_PRODUCTION  = 1;

    public function getByParticipant(int $participantId)
    {
        return $this->where('participant_id', $participantId)
                    ->where('is_active', 1)
                    ->findAll();
    }

    public function getForParticipantEnv(int $participantId, int $environment)
    {
        return $this->where('participant_id', $participantId)
                    ->where('environment', $environment)
                    ->where('is_active', 1)
                    ->first();
    }

    /**
     * Generate a new credential pair.
     * Returns ['client_id', 'client_secret'] — secret shown only once.
     */
    public static function generatePair(): array
    {
        $clientId     = 'eca_' . bin2hex(random_bytes(16));
        $clientSecret = bin2hex(random_bytes(24));
        return [$clientId, $clientSecret];
    }
}
