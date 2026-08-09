<?php

namespace App\Controllers\Admin;

use App\Models\InvoiceModel;
use App\Models\InvoiceTransmissionModel;
use App\Models\ParticipantModel;

class Invoices extends BaseAdminController
{
    private InvoiceModel             $invoiceModel;
    private InvoiceTransmissionModel $transmissionModel;
    private ParticipantModel         $participantModel;

    public function __construct()
    {
        $this->invoiceModel      = new InvoiceModel();
        $this->transmissionModel = new InvoiceTransmissionModel();
        $this->participantModel  = new ParticipantModel();
    }

    public function index()
    {
        $this->requireLogin();

        $filters = [
            'participant_id'      => $this->request->getGet('participant_id') ?? '',
            'direction'           => $this->request->getGet('direction') ?? '',
            'transmission_status' => $this->request->getGet('tx_status') ?? '',
            'environment'         => $this->request->getGet('environment') ?? '',
        ];

        $data = [
            'page_title'   => 'Invoices',
            'active_menu'  => 'invoices',
            'breadcrumb'   => 'Invoices',
            'invoices'     => $this->invoiceModel->getList($filters),
            'stats'        => $this->invoiceModel->getStats(),
            'participants' => $this->participantModel->getActive(),
            'filters'      => $filters,
        ];

        return $this->renderView(view('invoices/index', $data), $data);
    }

    public function view(int $id)
    {
        $this->requireLogin();

        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            session()->setFlashdata('error', 'Invoice not found.');
            return redirect()->to(base_url('invoices'));
        }

        $participant   = $this->participantModel->find($invoice['participant_id']);
        $transmissions = $this->transmissionModel->getByInvoice($id);

        $data = [
            'page_title'    => 'Invoice ' . $invoice['invoice_number'],
            'active_menu'   => 'invoices',
            'breadcrumb'    => $invoice['invoice_number'],
            'invoice'       => $invoice,
            'participant'   => $participant,
            'transmissions' => $transmissions,
        ];

        return $this->renderView(view('invoices/view', $data), $data);
    }
}
