<?php

namespace App\Controllers;

use App\Controllers\Admin\BaseAdminController;
use App\Models\UserModel;
use App\Models\LoginLogModel;
use App\Libraries\TotpService;

class Profile extends BaseAdminController
{
    private UserModel     $userModel;
    private LoginLogModel $logModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->logModel  = new LoginLogModel();
    }

    // -------------------------------------------------------------------------
    // Profile overview + completion bar
    // -------------------------------------------------------------------------

    public function index()
    {
        $this->requireLogin();

        $user      = $this->userModel->find(session()->get('user_id'));
        $loginLogs = $this->logModel->getByUser((int) $user['id'], 10);

        $checks = $this->completionChecks($user);
        $pct    = (int) round(array_sum(array_values($checks)) / count($checks) * 100);

        $data = [
            'page_title'  => 'My Profile',
            'active_menu' => 'profile',
            'breadcrumb'  => 'My Profile',
            'user'        => $user,
            'login_logs'  => $loginLogs,
            'checks'      => $checks,
            'pct'         => $pct,
        ];

        return $this->renderView(view('profile/index', $data), $data);
    }

    // -------------------------------------------------------------------------
    // Update profile info (name + company)
    // -------------------------------------------------------------------------

    public function update()
    {
        $this->requireLogin();

        $userId = (int) session()->get('user_id');
        $name   = trim($this->request->getPost('name') ?? '');
        $company = trim($this->request->getPost('company_name') ?? '');

        if (strlen($name) < 2) {
            session()->setFlashdata('error', 'Name must be at least 2 characters.');
            return redirect()->to(base_url('profile'));
        }

        $this->userModel->update($userId, [
            'name'         => $name,
            'company_name' => $company,
        ]);

        // Keep session name in sync
        session()->set('name', $name);
        session()->set('company', $company);

        session()->setFlashdata('success', 'Profile updated successfully.');
        return redirect()->to(base_url('profile'));
    }

    // -------------------------------------------------------------------------
    // Change password
    // -------------------------------------------------------------------------

    public function changePassword()
    {
        $this->requireLoginOrForceChange();

        if ($this->request->getMethod() === 'post') {
            $userId  = (int) (session()->get('user_id') ?? session()->get('force_user_id'));
            $newPw   = $this->request->getPost('new_password') ?? '';
            $confirm = $this->request->getPost('confirm_password') ?? '';

            if (strlen($newPw) < 8) {
                session()->setFlashdata('error', 'Password must be at least 8 characters.');
                return redirect()->to(base_url('profile/change-password'));
            }
            if ($newPw !== $confirm) {
                session()->setFlashdata('error', 'Passwords do not match.');
                return redirect()->to(base_url('profile/change-password'));
            }

            $this->userModel->update($userId, [
                'password_hash'         => password_hash($newPw, PASSWORD_BCRYPT),
                'force_password_change' => 0,
            ]);

            // If this was a forced change, now complete the login
            if (session()->get('force_change')) {
                $user = $this->userModel->find($userId);

                $this->logModel->record([
                    'user_id'    => $userId,
                    'email'      => $user['email'],
                    'ip_address' => $this->request->getIPAddress(),
                    'user_agent' => $this->request->getUserAgent()->getAgentString(),
                    'status'     => LoginLogModel::STATUS_PASSWORD_CHANGED,
                ]);

                session()->remove(['force_change', 'force_user_id', 'force_user_name']);
                session()->set([
                    'logged_in' => true,
                    'user_id'   => (int) $user['id'],
                    'name'      => $user['name'],
                    'email'     => $user['email'],
                    'type'      => (int) $user['type'],
                    'role'      => (int) $user['type'] === UserModel::TYPE_SUPER_ADMIN ? 'Super Admin' : 'Admin',
                    'company'   => $user['company_name'],
                ]);
                $this->userModel->updateLastLogin($userId);

                session()->setFlashdata('success', 'Password set. Welcome to API Central!');
                return redirect()->to(base_url('dashboard'));
            }

            session()->setFlashdata('success', 'Password changed successfully.');
            return redirect()->to(base_url('profile'));
        }

        $isForced = (bool) session()->get('force_change');
        $data = [
            'page_title'  => 'Set Password',
            'active_menu' => 'profile',
            'breadcrumb'  => $isForced ? 'Set Password' : 'Change Password',
            'is_forced'   => $isForced,
        ];

        return $this->renderView(view('profile/change_password', $data), $data);
    }

    // -------------------------------------------------------------------------
    // MFA Setup
    // -------------------------------------------------------------------------

    public function mfaSetup()
    {
        $this->requireLogin();

        $userId = (int) session()->get('user_id');
        $user   = $this->userModel->find($userId);
        $totp   = new TotpService();

        if ($this->request->getMethod() === 'post') {
            $code          = trim($this->request->getPost('totp_code') ?? '');
            $pendingSecret = session()->get('pending_totp_secret');

            if (!$pendingSecret) {
                session()->setFlashdata('error', 'Session expired. Please try again.');
                return redirect()->to(base_url('profile/mfa-setup'));
            }

            if (!$totp->verifyCode($pendingSecret, $code)) {
                session()->setFlashdata('error', 'Incorrect code. Please try again.');
                return redirect()->to(base_url('profile/mfa-setup'));
            }

            // Code verified — save secret
            $this->userModel->update($userId, [
                'totp_secret'  => $pendingSecret,
                'totp_enabled' => 1,
            ]);
            session()->remove('pending_totp_secret');
            session()->setFlashdata('success', 'Two-factor authentication is now enabled.');
            return redirect()->to(base_url('profile'));
        }

        // Generate a new pending secret (stored in session until verified)
        $secret    = $totp->createSecret();
        $otpUrl    = $totp->getOtpauthUrl($user['email'], $secret);
        session()->set('pending_totp_secret', $secret);

        $data = [
            'page_title'  => 'Set Up Authenticator',
            'active_menu' => 'profile',
            'breadcrumb'  => 'Set Up Authenticator',
            'otp_url'     => $otpUrl,
            'secret'      => $secret,
        ];

        return $this->renderView(view('profile/mfa_setup', $data), $data);
    }

    // -------------------------------------------------------------------------
    // Disable MFA
    // -------------------------------------------------------------------------

    public function mfaDisable()
    {
        $this->requireLogin();

        $userId   = (int) session()->get('user_id');
        $password = $this->request->getPost('password') ?? '';
        $user     = $this->userModel->find($userId);

        if (!password_verify($password, $user['password_hash'])) {
            session()->setFlashdata('error', 'Incorrect password. MFA not disabled.');
            return redirect()->to(base_url('profile'));
        }

        $this->userModel->update($userId, [
            'totp_secret'  => null,
            'totp_enabled' => 0,
        ]);

        session()->setFlashdata('success', 'Two-factor authentication has been disabled.');
        return redirect()->to(base_url('profile'));
    }

    // -------------------------------------------------------------------------

    private function requireLoginOrForceChange(): void
    {
        if (!session()->get('logged_in') && !session()->get('force_change')) {
            redirect()->to(base_url('login'))->send();
            exit;
        }
    }

    private function completionChecks(array $user): array
    {
        return [
            'profile_complete' => (!empty($user['name']) && !empty($user['company_name'])),
            'password_changed' => ((int) $user['force_password_change'] === 0),
            'mfa_enabled'      => ((int) $user['totp_enabled'] === 1),
        ];
    }
}
