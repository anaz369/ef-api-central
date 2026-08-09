<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LoginLogModel;
use App\Libraries\TotpService;

class Login extends BaseController
{
    private UserModel     $userModel;
    private LoginLogModel $logModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->logModel  = new LoginLogModel();
    }

    // -------------------------------------------------------------------------
    // Step 1 — Email + Password
    // -------------------------------------------------------------------------

    public function index()
    {
        if (session()->get('logged_in')) {
            return redirect()->to(base_url('dashboard'));
        }

        if ($this->request->getMethod() === 'post') {
            $email    = trim($this->request->getPost('email') ?? '');
            $password = $this->request->getPost('password') ?? '';

            $user = $this->userModel->findByEmail($email);

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $this->logModel->record([
                    'user_id'    => $user['id'] ?? null,
                    'email'      => $email,
                    'ip_address' => $this->request->getIPAddress(),
                    'user_agent' => $this->request->getUserAgent()->getAgentString(),
                    'status'     => LoginLogModel::STATUS_FAILED,
                ]);
                session()->setFlashdata('error', 'Invalid email or password.');
                return redirect()->to(base_url('login'));
            }

            if (!(int) $user['is_active']) {
                session()->setFlashdata('error', 'Your account has been deactivated. Please contact the administrator.');
                return redirect()->to(base_url('login'));
            }

            // Force password change on first login
            if ((int) $user['force_password_change']) {
                session()->set([
                    'force_change'    => true,
                    'force_user_id'   => $user['id'],
                    'force_user_name' => $user['name'],
                ]);
                return redirect()->to(base_url('profile/change-password'));
            }

            // TOTP required
            if ((int) $user['totp_enabled']) {
                session()->set([
                    'totp_pending'    => true,
                    'totp_user_id'    => $user['id'],
                    'totp_user_email' => $user['email'],
                ]);
                return redirect()->to(base_url('login/totp'));
            }

            // Full login
            $this->completeLogin($user);
            return redirect()->to(base_url('dashboard'));
        }

        return view('auth/login');
    }

    // -------------------------------------------------------------------------
    // Step 2 — TOTP Verification
    // -------------------------------------------------------------------------

    public function totp()
    {
        if (session()->get('logged_in')) {
            return redirect()->to(base_url('dashboard'));
        }

        if (!session()->get('totp_pending')) {
            return redirect()->to(base_url('login'));
        }

        if ($this->request->getMethod() === 'post') {
            $code   = trim($this->request->getPost('totp_code') ?? '');
            $userId = (int) session()->get('totp_user_id');
            $user   = $this->userModel->find($userId);

            $totp  = new TotpService();
            $valid = $user && $totp->verifyCode($user['totp_secret'], $code);

            if (!$valid) {
                $this->logModel->record([
                    'user_id'    => $userId,
                    'email'      => session()->get('totp_user_email'),
                    'ip_address' => $this->request->getIPAddress(),
                    'user_agent' => $this->request->getUserAgent()->getAgentString(),
                    'status'     => LoginLogModel::STATUS_TOTP_FAILED,
                ]);
                session()->setFlashdata('error', 'Invalid authenticator code. Please try again.');
                return redirect()->to(base_url('login/totp'));
            }

            session()->remove(['totp_pending', 'totp_user_id', 'totp_user_email']);
            $this->completeLogin($user);
            return redirect()->to(base_url('dashboard'));
        }

        return view('auth/totp');
    }

    // -------------------------------------------------------------------------

    private function completeLogin(array $user): void
    {
        session()->set([
            'logged_in' => true,
            'user_id'   => (int) $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'type'      => (int) $user['type'],
            'role'      => (int) $user['type'] === UserModel::TYPE_SUPER_ADMIN ? 'Super Admin' : 'Admin',
            'company'   => $user['company_name'],
        ]);

        $this->logModel->record([
            'user_id'    => (int) $user['id'],
            'email'      => $user['email'],
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'status'     => LoginLogModel::STATUS_SUCCESS,
        ]);

        $this->userModel->updateLastLogin((int) $user['id']);
    }
}
