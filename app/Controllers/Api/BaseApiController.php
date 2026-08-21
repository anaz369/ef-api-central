<?php

namespace App\Controllers\Api;

use App\Models\ApiTokenModel;
use App\Models\ApiLogModel;
use CodeIgniter\Controller;

class BaseApiController extends Controller
{
    protected ApiTokenModel $tokenModel;
    protected ApiLogModel   $logModel;

    public function __construct()
    {
        $this->tokenModel = new ApiTokenModel();
        $this->logModel   = new ApiLogModel();
    }

    /**
     * Extract and validate the Bearer token from the Authorization header.
     * Returns the token row (includes participant_id, environment) or null.
     */
    protected function authenticate(): ?array
    {
        $header = $this->request->getHeaderLine('Authorization');
        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return null;
        }
        $raw = trim(substr($header, 7));
        return $this->tokenModel->findByToken($raw) ?: null;
    }

    protected function jsonSuccess(mixed $data, int $status = 200): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON(['success' => true, 'data' => $data]);
    }

    protected function jsonError(string $code, string $message, int $status = 400): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON(['success' => false, 'error' => ['code' => $code, 'message' => $message]]);
    }

    protected function logRequest(
        ?int   $userId,
        ?int   $participantId,
        ?int   $credentialId,
        string $endpoint,
        string $method,
        mixed  $requestBody,
        mixed  $responseBody,
        int    $responseCode,
        int    $environment = 0,
        int    $status = 1
    ): void {
        $this->logModel->record([
            'user_id'        => $userId,
            'participant_id' => $participantId,
            'credential_id'  => $credentialId,
            'endpoint'       => $endpoint,
            'method'         => $method,
            'request_body'   => is_string($requestBody)  ? $requestBody  : json_encode($requestBody),
            'response_body'  => is_string($responseBody) ? $responseBody : json_encode($responseBody),
            'response_code'  => $responseCode,
            'environment'    => $environment,
            'status'         => $status,
            'ip_address'     => $this->request->getIPAddress(),
        ]);
    }
}
