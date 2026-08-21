<?php

namespace App\Controllers;

use App\Controllers\Admin\BaseAdminController;
use App\Models\CredentialModel;

class ApiKeys extends BaseAdminController
{
    private CredentialModel $credentialModel;

    public function __construct()
    {
        $this->credentialModel = new CredentialModel();
    }

    public function index()
    {
        $this->requireLogin();

        if ($this->isSuperAdmin()) {
            session()->setFlashdata('error', 'API Keys are managed per-user from the Users admin panel.');
            return redirect()->to(base_url('users'));
        }

        $userId = (int) session()->get('user_id');
        $keys   = $this->credentialModel->getAllByUser($userId);

        $data = [
            'page_title'  => 'API Keys',
            'active_menu' => 'apikeys',
            'breadcrumb'  => 'API Keys',
            'keys'        => $keys,
            'new_secret'     => session()->getFlashdata('new_secret'),
            'new_secret_env' => session()->getFlashdata('new_secret_env'),
            'new_secret_name'=> session()->getFlashdata('new_secret_name'),
        ];

        return $this->renderView(view('api_keys/index', $data), $data);
    }

    /**
     * POST /api-keys/request
     * User submits a new API key request (creates a pending credential).
     */
    public function request()
    {
        $this->requireLogin();

        if ($this->isSuperAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not available for super admins.']);
        }

        $userId      = (int) session()->get('user_id');
        $keyName     = trim($this->request->getPost('key_name') ?? '');
        $description = trim($this->request->getPost('key_description') ?? '');
        $environment = (int) $this->request->getPost('environment');

        if (!$keyName) {
            return $this->response->setJSON(['success' => false, 'message' => 'Key name is required.']);
        }

        if (!in_array($environment, [CredentialModel::ENV_DEVELOPMENT, CredentialModel::ENV_PRODUCTION])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid environment.']);
        }

        $this->credentialModel->insert([
            'user_id'         => $userId,
            'key_name'        => $keyName,
            'key_description' => $description ?: null,
            'environment'     => $environment,
            'request_status'  => 'pending',
            'is_active'       => 0,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'API key requested successfully. Our team will review and generate your credentials shortly.',
        ]);
    }

    /**
     * POST /api-keys/:id/revoke
     * User revokes one of their own active keys.
     */
    public function revoke(int $id)
    {
        $this->requireLogin();

        if ($this->isSuperAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Not available for super admins.']);
        }

        $userId = (int) session()->get('user_id');
        $cred   = $this->credentialModel->find($id);

        if (!$cred || (int) $cred['user_id'] !== $userId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Key not found.']);
        }

        if ($cred['request_status'] === 'revoked') {
            return $this->response->setJSON(['success' => false, 'message' => 'Key already revoked.']);
        }

        $this->credentialModel->update($id, [
            'request_status' => 'revoked',
            'is_active'      => 0,
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'API key revoked.']);
    }
}
