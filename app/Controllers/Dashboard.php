<?php

namespace App\Controllers;

use App\Models\ParticipantModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (! session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }

        $participantModel = new ParticipantModel();
        $pStats = $participantModel->getStats();

        $data = [
            'page_title'      => 'Dashboard',
            'active_menu'     => 'dashboard',
            'breadcrumb'      => 'Dashboard',
            'stats'           => [
                'participants'  => $pStats['total'],
                'active'        => $pStats['active'],
                'production'    => $pStats['production'],
                'invoices_sent' => 0,
            ],
            'recent_invoices' => [],
        ];

        return view('layouts/header', $data)
             . view('dashboard/index', $data)
             . view('layouts/footer');
    }
}
