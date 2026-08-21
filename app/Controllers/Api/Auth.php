<?php

namespace App\Controllers\Api;

use App\Models\CredentialModel;

class Auth extends BaseApiController
{
    private CredentialModel $credentialModel;

    public function __construct()
    {
        parent::__construct();
        $this->credentialModel = new CredentialModel();
    }

    /**
     * POST /api/auth/token
     *
     * Body (JSON or form-encoded):
     *   client_id     string  required
     *   client_secret string  required
     *
     * Response:
     *   { "access_token": "...", "token_type": "Bearer", "expires_in": 3600, "environment": "development" }
     */
    public function token()
    {
        $body = $this->request->getJSON(true) ?? [];
        if (empty($body)) {
            $body = [
                'client_id'     => $this->request->getPost('client_id'),
                'client_secret' => $this->request->getPost('client_secret'),
            ];
        }

        $clientId     = trim($body['client_id'] ?? '');
        $clientSecret = trim($body['client_secret'] ?? '');

        if (!$clientId || !$clientSecret) {
            $this->logRequest(null, null, null, '/api/auth/token', 'POST', $body, ['error' => 'missing_credentials'], 400, 0, 0);
            return $this->jsonError('MISSING_CREDENTIALS', 'client_id and client_secret are required.', 400);
        }

        $credential = $this->credentialModel->where('client_id', $clientId)
                                            ->where('is_active', 1)
                                            ->first();

        if (!$credential || !password_verify($clientSecret, $credential['client_secret_hash'])) {
            $this->logRequest(null, null, null, '/api/auth/token', 'POST', ['client_id' => $clientId], ['error' => 'invalid_credentials'], 401, 0, 0);
            return $this->jsonError('INVALID_CREDENTIALS', 'Invalid client_id or client_secret.', 401);
        }

        $userId      = (int) $credential['user_id'];
        $environment = (int) $credential['environment'];
        $ttl         = 3600;

        $rawToken = $this->tokenModel->issueToken($userId, (int) $credential['id'], $environment, $ttl);

        $this->credentialModel->update($credential['id'], ['last_used_at' => date('Y-m-d H:i:s')]);

        $responseData = [
            'access_token' => $rawToken,
            'token_type'   => 'Bearer',
            'expires_in'   => $ttl,
            'environment'  => $environment === CredentialModel::ENV_PRODUCTION ? 'production' : 'development',
        ];

        $this->logRequest($userId, null, (int) $credential['id'], '/api/auth/token', 'POST', ['client_id' => $clientId], $responseData, 200, $environment, 1);

        return $this->jsonSuccess($responseData);
    }
}
