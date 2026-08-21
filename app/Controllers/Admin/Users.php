<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;
use App\Models\LoginLogModel;
use App\Models\ParticipantModel;
use App\Models\CredentialModel;

class Users extends BaseAdminController
{
    private UserModel        $userModel;
    private LoginLogModel    $logModel;
    private ParticipantModel $participantModel;
    private CredentialModel  $credentialModel;

    public function __construct()
    {
        $this->userModel        = new UserModel();
        $this->logModel         = new LoginLogModel();
        $this->participantModel = new ParticipantModel();
        $this->credentialModel  = new CredentialModel();
    }

    public function index()
    {
        $this->requireSuperAdmin();

        $data = [
            'page_title'  => 'Users',
            'active_menu' => 'users',
            'breadcrumb'  => 'Users',
            'users'       => $this->userModel->orderBy('created_at', 'DESC')->findAll(),
        ];

        return $this->renderView(view('users/index', $data), $data);
    }

    public function create()
    {
        $this->requireSuperAdmin();

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'  => 'required|min_length[2]|max_length[255]',
                'email' => 'required|valid_email|is_unique[tbl_users.email]',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false,
                    'errors'  => $this->validator->getErrors(),
                ]);
            }

            $tempPassword = UserModel::generateTempPassword();

            $this->userModel->insert([
                'name'                  => $this->request->getPost('name'),
                'email'                 => $this->request->getPost('email'),
                'password_hash'         => password_hash($tempPassword, PASSWORD_BCRYPT),
                'type'                  => (int) $this->request->getPost('type'),
                'company_name'          => $this->request->getPost('company_name'),
                'force_password_change' => 1,
                'is_active'             => 1,
            ]);

            $newId = $this->userModel->getInsertID();
            session()->setFlashdata('new_password', $tempPassword);

            return $this->response->setJSON([
                'success'      => true,
                'redirect'     => base_url('users/' . $newId),
                'new_password' => $tempPassword,
            ]);
        }

        $data = [
            'page_title'  => 'Add User',
            'active_menu' => 'users',
            'breadcrumb'  => 'Add User',
        ];

        return $this->renderView(view('users/create', $data), $data);
    }

    public function view(int $id)
    {
        $this->requireSuperAdmin();

        $user = $this->userModel->find($id);
        if (!$user) {
            session()->setFlashdata('error', 'User not found.');
            return redirect()->to(base_url('users'));
        }

        $loginLogs = $this->logModel->getByUser($id, 20);

        // Per-user credentials (active ones)
        $devCred  = $this->credentialModel->getForUserEnv($id, CredentialModel::ENV_DEVELOPMENT);
        $prodCred = $this->credentialModel->getForUserEnv($id, CredentialModel::ENV_PRODUCTION);

        // Pending key requests from this user
        $pendingRequests = $this->credentialModel->getPendingByUser($id);

        // Participants linked to this user
        $participants = $this->participantModel->getByUser($id);

        $data = [
            'page_title'      => $user['name'],
            'active_menu'     => 'users',
            'breadcrumb'      => $user['name'],
            'user'            => $user,
            'login_logs'      => $loginLogs,
            'dev_cred'        => $devCred,
            'prod_cred'       => $prodCred,
            'pending_requests'=> $pendingRequests,
            'participants'    => $participants,
            'new_password'    => session()->getFlashdata('new_password'),
            'new_secret'      => session()->getFlashdata('new_secret'),
            'new_secret_env'  => session()->getFlashdata('new_secret_env'),
            'new_secret_name' => session()->getFlashdata('new_secret_name'),
        ];

        return $this->renderView(view('users/view', $data), $data);
    }

    public function generateCredentials(int $userId)
    {
        $this->requireSuperAdmin();

        $user = $this->userModel->find($userId);
        if (!$user) {
            session()->setFlashdata('error', 'User not found.');
            return redirect()->to(base_url('users'));
        }

        $environment = (int) $this->request->getPost('environment');

        // Deactivate any existing credential for this user + environment
        $existing = $this->credentialModel->getForUserEnv($userId, $environment);
        if ($existing) {
            $this->credentialModel->update($existing['id'], ['is_active' => 0, 'request_status' => 'revoked']);
        }

        [$clientId, $clientSecret] = CredentialModel::generatePair();

        $this->credentialModel->insert([
            'user_id'               => $userId,
            'key_name'              => 'Admin-generated',
            'request_status'        => 'active',
            'client_id'             => $clientId,
            'client_secret_hash'    => password_hash($clientSecret, PASSWORD_BCRYPT),
            'client_secret_preview' => '...' . substr($clientSecret, -6),
            'environment'           => $environment,
            'is_active'             => 1,
        ]);

        session()->setFlashdata('new_secret',     $clientSecret);
        session()->setFlashdata('new_secret_env', $environment === CredentialModel::ENV_PRODUCTION ? 'Production' : 'Development');

        return redirect()->to(base_url('users/' . $userId));
    }

    /**
     * POST users/:userId/credentials/:credentialId/approve
     * Approve a pending API key request — generate client_id + secret.
     */
    public function approveCredential(int $userId, int $credentialId)
    {
        $this->requireSuperAdmin();

        $cred = $this->credentialModel->find($credentialId);
        if (!$cred || (int) $cred['user_id'] !== $userId || $cred['request_status'] !== 'pending') {
            session()->setFlashdata('error', 'Pending credential request not found.');
            return redirect()->to(base_url('users/' . $userId));
        }

        // Revoke any existing active credential for same user+env
        $existing = $this->credentialModel->getForUserEnv($userId, (int) $cred['environment']);
        if ($existing) {
            $this->credentialModel->update($existing['id'], [
                'is_active'      => 0,
                'request_status' => 'revoked',
            ]);
        }

        [$clientId, $clientSecret] = CredentialModel::generatePair();

        $this->credentialModel->update($credentialId, [
            'client_id'             => $clientId,
            'client_secret_hash'    => password_hash($clientSecret, PASSWORD_BCRYPT),
            'client_secret_preview' => '...' . substr($clientSecret, -6),
            'request_status'        => 'active',
            'is_active'             => 1,
        ]);

        session()->setFlashdata('new_secret',      $clientSecret);
        session()->setFlashdata('new_secret_env',  (int) $cred['environment'] === CredentialModel::ENV_PRODUCTION ? 'Production' : 'Development');
        session()->setFlashdata('new_secret_name', $cred['key_name'] ?? '');

        return redirect()->to(base_url('users/' . $userId));
    }

    /**
     * POST users/:userId/credentials/:credentialId/reject
     * Reject a pending API key request.
     */
    public function rejectCredential(int $userId, int $credentialId)
    {
        $this->requireSuperAdmin();

        $cred = $this->credentialModel->find($credentialId);
        if (!$cred || (int) $cred['user_id'] !== $userId || $cred['request_status'] !== 'pending') {
            session()->setFlashdata('error', 'Pending credential request not found.');
            return redirect()->to(base_url('users/' . $userId));
        }

        $this->credentialModel->update($credentialId, ['request_status' => 'revoked']);

        session()->setFlashdata('success', 'API key request rejected.');
        return redirect()->to(base_url('users/' . $userId));
    }

    public function update(int $id)
    {
        $this->requireSuperAdmin();

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User not found.']);
        }

        $rules = ['name' => 'required|min_length[2]|max_length[255]'];
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        // Prevent downgrading own super-admin account
        $newType = (int) $this->request->getPost('type');
        if ($id === (int) session()->get('user_id') && $newType !== UserModel::TYPE_SUPER_ADMIN) {
            return $this->response->setJSON(['success' => false, 'message' => 'You cannot change your own type.']);
        }

        $this->userModel->update($id, [
            'name'         => $this->request->getPost('name'),
            'type'         => $newType,
            'company_name' => $this->request->getPost('company_name'),
            'is_active'    => (int) $this->request->getPost('is_active'),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'User updated successfully.']);
    }

    public function resetPassword(int $id)
    {
        $this->requireSuperAdmin();

        $user = $this->userModel->find($id);
        if (!$user) {
            session()->setFlashdata('error', 'User not found.');
            return redirect()->to(base_url('users'));
        }

        // Prevent resetting own password via admin panel
        if ($id === (int) session()->get('user_id')) {
            session()->setFlashdata('error', 'Use the Profile page to change your own password.');
            return redirect()->to(base_url('users/' . $id));
        }

        $tempPassword = UserModel::generateTempPassword();

        $this->userModel->update($id, [
            'password_hash'         => password_hash($tempPassword, PASSWORD_BCRYPT),
            'force_password_change' => 1,
            'totp_enabled'          => 0,
            'totp_secret'           => null,
        ]);

        session()->setFlashdata('new_password', $tempPassword);
        session()->setFlashdata('success', 'Password reset. MFA has also been disabled. Share the new temporary password securely.');
        return redirect()->to(base_url('users/' . $id));
    }
}
