<?php

namespace App\Controllers\Admin;

use App\Models\ParticipantModel;
use App\Models\CredentialModel;

class Participants extends BaseAdminController
{
    private ParticipantModel $participantModel;
    private CredentialModel  $credentialModel;

    public function __construct()
    {
        $this->participantModel = new ParticipantModel();
        $this->credentialModel  = new CredentialModel();
    }

    public function index()
    {
        $this->requireLogin();

        $data = [
            'page_title'   => 'Participants',
            'active_menu'  => 'participants',
            'breadcrumb'   => 'Participants',
            'participants' => $this->participantModel->getActive(),
            'stats'        => $this->participantModel->getStats(),
        ];

        return $this->renderView(view('participants/index', $data), $data);
    }

    public function create()
    {
        $this->requireLogin();

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'       => 'required|min_length[2]|max_length[255]',
                'email'      => 'required|valid_email|is_unique[tbl_participants.email]',
                'trn'        => 'permit_empty|regex_match[/^\d{15}$/]',
                'tin_id'     => 'permit_empty|regex_match[/^\d{10}$/]',
            ];
            $messages = [
                'trn'    => ['regex_match' => 'TRN must be exactly 15 digits.'],
                'tin_id' => ['regex_match' => 'TIN must be exactly 10 digits.'],
            ];

            if (!$this->validate($rules, $messages)) {
                session()->setFlashdata('errors', $this->validator->getErrors());
                return redirect()->to(base_url('participants/create'))->withInput();
            }

            $phoneCode   = trim($this->request->getPost('phone_code') ?? '');
            $phoneNumber = trim($this->request->getPost('phone_number') ?? '');
            $phone       = ($phoneCode && $phoneNumber) ? $phoneCode . ' ' . $phoneNumber : $phoneNumber;

            $peppolParticipantId = (int) ($this->request->getPost('peppol_participant_id') ?: 0) ?: null;

            $this->participantModel->insert([
                'name'                  => $this->request->getPost('name'),
                'email'                 => $this->request->getPost('email'),
                'phone'                 => $phone,
                'country'               => $this->request->getPost('country') ?: 'AE',
                'trn'                   => $this->request->getPost('trn'),
                'tin_id'                => $this->request->getPost('tin_id'),
                'trade_license'         => $this->request->getPost('trade_license'),
                'legal_form'            => $this->request->getPost('legal_form'),
                'address_line1'         => $this->request->getPost('address_line1'),
                'city'                  => $this->request->getPost('city'),
                'emirate'               => $this->request->getPost('emirate'),
                'postal_zone'           => $this->request->getPost('postal_zone'),
                'peppol_scheme'         => $this->request->getPost('peppol_scheme'),
                'peppol_id'             => $this->request->getPost('peppol_id'),
                'peppol_participant_id'  => $peppolParticipantId,
                'peppol_verified'       => ($this->request->getPost('peppol_status') === 'active') ? 1 : 0,
                'integration_mode'      => (int) $this->request->getPost('integration_mode'),
                'status'                => ParticipantModel::STATUS_ACTIVE,
            ]);

            $newId = $this->participantModel->getInsertID();
            session()->setFlashdata('success', 'Participant created successfully.');
            return redirect()->to(base_url('participants/' . $newId));
        }

        $data = [
            'page_title'  => 'Add Participant',
            'active_menu' => 'participants',
            'breadcrumb'  => 'Add Participant',
        ];

        return $this->renderView(view('participants/create', $data), $data);
    }

    public function view(int $id)
    {
        $this->requireLogin();

        $participant = $this->participantModel->find($id);
        if (!$participant) {
            session()->setFlashdata('error', 'Participant not found.');
            return redirect()->to(base_url('participants'));
        }

        $credentials = $this->credentialModel->getByParticipant($id);

        $devCred  = null;
        $prodCred = null;
        foreach ($credentials as $cred) {
            if ((int) $cred['environment'] === CredentialModel::ENV_DEVELOPMENT) {
                $devCred = $cred;
            } elseif ((int) $cred['environment'] === CredentialModel::ENV_PRODUCTION) {
                $prodCred = $cred;
            }
        }

        // Fetch peppol identity from the shared ethicfin DB
        $peppolInfo = null;
        if ($participant['peppol_participant_id']) {
            $peppolInfo = $this->participantModel->getPeppolById((int) $participant['peppol_participant_id']);
        }

        $data = [
            'page_title'  => $participant['name'],
            'active_menu' => 'participants',
            'breadcrumb'  => $participant['name'],
            'participant' => $participant,
            'dev_cred'    => $devCred,
            'prod_cred'   => $prodCred,
            'peppol_info' => $peppolInfo,
            'new_secret'  => session()->getFlashdata('new_secret'),
            'new_env'     => session()->getFlashdata('new_env'),
        ];

        return $this->renderView(view('participants/view', $data), $data);
    }

    public function generateCredentials(int $id)
    {
        $this->requireLogin();

        $participant = $this->participantModel->find($id);
        if (!$participant) {
            return redirect()->to(base_url('participants'));
        }

        $environment = (int) $this->request->getPost('environment');

        // Production only if production_access granted
        if ($environment === CredentialModel::ENV_PRODUCTION && !$participant['production_access']) {
            session()->setFlashdata('error', 'Production access not yet granted for this participant.');
            return redirect()->to(base_url('participants/' . $id));
        }

        // Deactivate existing credential for this env
        $existing = $this->credentialModel->getForParticipantEnv($id, $environment);
        if ($existing) {
            $this->credentialModel->update($existing['id'], ['is_active' => 0]);
        }

        [$clientId, $clientSecret] = CredentialModel::generatePair();

        $this->credentialModel->insert([
            'participant_id'        => $id,
            'client_id'             => $clientId,
            'client_secret_hash'    => password_hash($clientSecret, PASSWORD_BCRYPT),
            'client_secret_preview' => '...' . substr($clientSecret, -6),
            'environment'           => $environment,
            'is_active'             => 1,
        ]);

        session()->setFlashdata('new_secret', $clientSecret);
        session()->setFlashdata('new_env', $environment === CredentialModel::ENV_PRODUCTION ? 'Production' : 'Development');

        return redirect()->to(base_url('participants/' . $id));
    }

    public function update(int $id)
    {
        $this->requireLogin();

        $participant = $this->participantModel->find($id);
        if (!$participant) {
            return $this->response->setJSON(['success' => false, 'message' => 'Participant not found.']);
        }

        $rules = [
            'name'   => 'required|min_length[2]|max_length[255]',
            'email'  => 'required|valid_email',
            'trn'    => 'permit_empty|regex_match[/^\d{15}$/]',
            'tin_id' => 'permit_empty|regex_match[/^\d{10}$/]',
        ];
        $messages = [
            'trn'    => ['regex_match' => 'TRN must be exactly 15 digits.'],
            'tin_id' => ['regex_match' => 'TIN must be exactly 10 digits.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        // Check email uniqueness excluding current participant
        $dup = $this->participantModel->where('email', $this->request->getPost('email'))
                                      ->where('id !=', $id)
                                      ->first();
        if ($dup) {
            return $this->response->setJSON(['success' => false, 'errors' => ['email' => 'Email already in use by another participant.']]);
        }

        $phoneCode   = trim($this->request->getPost('phone_code') ?? '');
        $phoneNumber = trim($this->request->getPost('phone_number') ?? '');
        $phone       = ($phoneCode && $phoneNumber) ? $phoneCode . ' ' . $phoneNumber : $phoneNumber;

        $this->participantModel->update($id, [
            'name'             => $this->request->getPost('name'),
            'email'            => $this->request->getPost('email'),
            'phone'            => $phone,
            'legal_form'       => $this->request->getPost('legal_form'),
            'status'           => (int) $this->request->getPost('status'),
            'integration_mode' => (int) $this->request->getPost('integration_mode'),
            'country'          => $this->request->getPost('country'),
            'emirate'          => $this->request->getPost('emirate'),
            'city'             => $this->request->getPost('city'),
            'trn'              => $this->request->getPost('trn'),
            'tin_id'           => $this->request->getPost('tin_id'),
            'trade_license'    => $this->request->getPost('trade_license'),
            'peppol_scheme'    => $this->request->getPost('peppol_scheme'),
            'peppol_id'        => $this->request->getPost('peppol_id'),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Participant updated successfully.']);
    }

    public function delete(int $id)
    {
        $this->requireLogin();

        $participant = $this->participantModel->find($id);
        if (!$participant) {
            return $this->response->setJSON(['success' => false, 'message' => 'Participant not found.']);
        }

        // Soft-delete: set status to 3 (deleted)
        $this->participantModel->update($id, ['status' => 3]);

        return $this->response->setJSON(['success' => true, 'message' => 'Participant deleted.']);
    }

    public function updateStatus(int $id)
    {
        $this->requireLogin();

        $status = (int) $this->request->getPost('status');
        $this->participantModel->update($id, ['status' => $status]);

        return redirect()->to(base_url('participants/' . $id));
    }

    public function grantProduction(int $id)
    {
        $this->requireLogin();

        $this->participantModel->update($id, ['production_access' => 1]);
        session()->setFlashdata('success', 'Production access granted.');
        return redirect()->to(base_url('participants/' . $id));
    }

    /**
     * AJAX: GET /participants/lookup-peppol?trn=xxx
     * Looks up ethicfin.tbl_fta_onboard_details by TRN.
     */
    public function lookupPeppol()
    {
        $this->requireLogin();

        $trn = trim($this->request->getGet('trn') ?? '');
        if (!preg_match('/^\d{15}$/', $trn)) {
            return $this->response->setJSON(['found' => false]);
        }

        $row = $this->participantModel->lookupPeppolByTrn($trn);
        if (!$row) {
            return $this->response->setJSON(['found' => false]);
        }

        $parts = explode(':', $row['peppol_id'], 2);
        return $this->response->setJSON([
            'found'                 => true,
            'peppol_participant_id' => (int) $row['id'],
            'peppol_id'             => $row['peppol_id'],
            'peppol_scheme'         => $parts[0] ?? '',
            'entity_name'           => $row['entity_name_en'],
            'status'                => $row['status'],
        ]);
    }

    /**
     * AJAX: POST /participants/:id/link-peppol
     * Looks up by TRN and saves the peppol_participant_id FK on the participant.
     */
    public function linkPeppol(int $id)
    {
        $this->requireLogin();

        $participant = $this->participantModel->find($id);
        if (!$participant || !$participant['trn']) {
            return $this->response->setJSON(['success' => false, 'message' => 'No TRN on file for this participant.']);
        }

        $row = $this->participantModel->lookupPeppolByTrn($participant['trn']);
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'No Peppol record found for TRN ' . $participant['trn'] . '.']);
        }

        $parts = explode(':', $row['peppol_id'], 2);
        $this->participantModel->update($id, [
            'peppol_participant_id' => (int) $row['id'],
            'peppol_id'             => $row['peppol_id'],
            'peppol_scheme'         => $parts[0] ?? '',
            'peppol_verified'       => $row['status'] === 'active' ? 1 : 0,
        ]);

        return $this->response->setJSON([
            'success'     => true,
            'peppol_id'   => $row['peppol_id'],
            'entity_name' => $row['entity_name_en'],
            'status'      => $row['status'],
        ]);
    }
}
