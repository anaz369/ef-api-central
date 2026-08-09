<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiTokenModel extends Model
{
    protected $table      = 'tbl_api_tokens';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'participant_id', 'credential_id', 'token_hash',
        'environment', 'expires_at', 'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = null;

    /**
     * Validate a raw Bearer token.
     * Returns the token row (with participant_id + environment) or null.
     */
    public function findByToken(string $rawToken): ?array
    {
        $hash = hash('sha256', $rawToken);
        return $this->where('token_hash', $hash)
                    ->where('is_active', 1)
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->first();
    }

    /**
     * Issue a new token for a credential. Revokes the previous one for the same credential.
     * Returns the raw token string.
     */
    public function issueToken(int $participantId, int $credentialId, int $environment, int $ttlSeconds = 3600): string
    {
        // Revoke existing tokens for this credential
        $this->where('credential_id', $credentialId)->set(['is_active' => 0])->update();

        $rawToken = bin2hex(random_bytes(32));

        $this->insert([
            'participant_id' => $participantId,
            'credential_id'  => $credentialId,
            'token_hash'     => hash('sha256', $rawToken),
            'environment'    => $environment,
            'expires_at'     => date('Y-m-d H:i:s', time() + $ttlSeconds),
            'is_active'      => 1,
        ]);

        return $rawToken;
    }
}
