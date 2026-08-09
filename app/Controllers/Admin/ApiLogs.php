<?php

namespace App\Controllers\Admin;

use App\Models\ApiLogModel;
use App\Models\ParticipantModel;

class ApiLogs extends BaseAdminController
{
    private ApiLogModel      $logModel;
    private ParticipantModel $participantModel;

    public function __construct()
    {
        $this->logModel          = new ApiLogModel();
        $this->participantModel  = new ParticipantModel();
    }

    public function index()
    {
        $this->requireLogin();

        $filters = [
            'participant_id' => $this->request->getGet('participant_id') ?? '',
            'status'         => $this->request->getGet('status') ?? '',
            'environment'    => $this->request->getGet('environment') ?? '',
        ];

        $data = [
            'page_title'   => 'API Logs',
            'active_menu'  => 'logs',
            'breadcrumb'   => 'API Logs',
            'logs'         => $this->logModel->getList($filters),
            'participants' => $this->participantModel->getActive(),
            'filters'      => $filters,
        ];

        return $this->renderView(view('api_logs/index', $data), $data);
    }
}
