<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

abstract class BaseAdminController extends BaseController
{
    protected function requireLogin(): void
    {
        if (!session()->get('logged_in')) {
            redirect()->to(base_url('login'))->send();
            exit;
        }
    }

    protected function requireSuperAdmin(): void
    {
        $this->requireLogin();
        if ((int) session()->get('type') !== 1) {
            session()->setFlashdata('error', 'Access denied.');
            redirect()->to(base_url('dashboard'))->send();
            exit;
        }
    }

    protected function isSuperAdmin(): bool
    {
        return (int) session()->get('type') === 1;
    }

    protected function renderView(string $content, array $data = []): string
    {
        return view('layouts/header', $data) . $content . view('layouts/footer');
    }
}
