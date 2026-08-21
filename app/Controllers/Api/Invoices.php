<?php

namespace App\Controllers\Api;

use App\Models\InvoiceModel;
use App\Models\InvoiceTransmissionModel;
use App\Models\ParticipantModel;
use App\Services\PeppolService;

class Invoices extends BaseApiController
{
    // PINT AE supported modes only (no mode 2 — debit note not in PINT AE)
    private const VALID_MODES = [
        1 => 'invoice',           // InvoiceTypeCode 380
        3 => 'credit_note',       // InvoiceTypeCode 381
        4 => 'sb_invoice',        // Self-Billing Invoice 389
        5 => 'sb_credit_note',    // Self-Billing Credit Note 261
    ];

    // mode → tbl_invoices document_type constant
    private const MODE_TO_DOC_TYPE = [
        1 => InvoiceModel::DOC_INVOICE,
        3 => InvoiceModel::DOC_CREDIT_NOTE,
        4 => InvoiceModel::DOC_SB_INVOICE,
        5 => InvoiceModel::DOC_SB_CREDIT,
    ];

    private InvoiceModel             $invoiceModel;
    private InvoiceTransmissionModel $transmissionModel;
    private ParticipantModel         $participantModel;
    private PeppolService            $peppolService;

    public function __construct()
    {
        parent::__construct();
        $this->invoiceModel      = new InvoiceModel();
        $this->transmissionModel = new InvoiceTransmissionModel();
        $this->participantModel  = new ParticipantModel();
        $this->peppolService     = new PeppolService();
    }

    /**
     * POST /api/invoices
     *
     * Requires: Authorization: Bearer {token}
     *
     * Body (JSON) — mirrors the `data` structure used by send_peppol.php:
     * {
     *   "mode": 1,                        // 1=invoice, 3=credit_note, 4=sb_invoice, 5=sb_credit_note
     *
     *   // Mode 1 & 3 — standard invoice / credit note
     *   "receiver_peppol_id": "iso6523-actorid-upis::9922:OPTBCNTRLP1005",
     *   "payment_account":    "AE070331234567890123456",  // IBAN
     *
     *   // Mode 4 & 5 — self-billing
     *   "receiver_scheme": "9922",
     *
     *   // Mode 3 & 5 — credit notes (reference to original invoice)
     *   "canceled_invoice_number": "INV-001",
     *   "canceled_invoice_date":   "2026-08-01",
     *   "discrepancy_reason_code": "3",   // mode 5 only, optional
     *
     *   "basicdetails": [{
     *     "inv_no":    "INV-001",
     *     "inv_date":  "2026-08-05",
     *     "due_date":  "2026-09-05",      // mode 1 only
     *     "currency":  "AED",
     *     "branch_id": 1
     *   }],
     *   "location": [{
     *     "country_code": "AE",
     *     "street":       "Office 101, Business Bay",
     *     "city":         "Dubai",
     *     "state":        "Dubai",
     *     "postal_zone":  "00000"
     *   }],
     *   "customer_details": [{
     *     "VAT_number":   "100000000000003",
     *     "VAT_name":     "Buyer Company",
     *     "street":       "Buyer Street",
     *     "city":         "Abu Dhabi",
     *     "state":        "Abu Dhabi",
     *     "postal_zone":  "00000",
     *     "country_code": "AE"
     *   }],
     *   "itemdetails": [{
     *     "item_name":          "Consulting Services",
     *     "description":        "Professional services",
     *     "unit":               "EA",
     *     "quantity":           10,
     *     "price":              100.00,
     *     "vat_perc":           "5",
     *     "tax_category":       "S",
     *     "allowance":          [],
     *     "exempt_reason":      "",
     *     "exempt_reason_code": ""
     *   }],
     *   "payment_code": 30
     * }
     */
    public function submit()
    {
        // ── 1. Authenticate ──────────────────────────────────────────────────
        $tokenRow = $this->authenticate();
        if (!$tokenRow) {
            return $this->jsonError('UNAUTHORIZED', 'Valid Bearer token required.', 401);
        }

        $userId       = (int) $tokenRow['user_id'];
        $environment  = (int) $tokenRow['environment'];
        $credentialId = (int) $tokenRow['credential_id'];

        $body = $this->request->getJSON(true) ?? [];

        // ── 2. Validate mode ─────────────────────────────────────────────────
        $mode = (int) ($body['mode'] ?? 0);
        if (!array_key_exists($mode, self::VALID_MODES)) {
            $resp = ['error' => 'invalid_mode'];
            $this->logRequest($userId, null, $credentialId, '/api/invoices', 'POST', $body, $resp, 422, $environment, 0);
            return $this->jsonError(
                'INVALID_MODE',
                'mode must be 1 (invoice), 3 (credit_note), 4 (sb_invoice), or 5 (sb_credit_note).',
                422
            );
        }

        // ── 3. Validate required fields ──────────────────────────────────────
        if (empty($body['sender_peppol_id'])) {
            $this->logRequest($userId, null, $credentialId, '/api/invoices', 'POST', $body, ['error' => 'missing sender_peppol_id'], 422, $environment, 0);
            return $this->jsonError('VALIDATION_ERROR', 'sender_peppol_id is required.', 422);
        }

        foreach (['basicdetails', 'location', 'customer_details', 'itemdetails'] as $field) {
            if (empty($body[$field])) {
                $this->logRequest($userId, null, $credentialId, '/api/invoices', 'POST', $body, ['error' => "missing $field"], 422, $environment, 0);
                return $this->jsonError('VALIDATION_ERROR', "Missing required field: {$field}.", 422);
            }
        }

        if (in_array($mode, [1, 3]) && empty($body['receiver_peppol_id'])) {
            return $this->jsonError('VALIDATION_ERROR', 'receiver_peppol_id is required for invoice and credit note.', 422);
        }

        if (in_array($mode, [4, 5]) && empty($body['receiver_scheme'])) {
            return $this->jsonError('VALIDATION_ERROR', 'receiver_scheme is required for self-billing documents.', 422);
        }

        if (in_array($mode, [3, 5]) && empty($body['canceled_invoice_number'])) {
            return $this->jsonError('VALIDATION_ERROR', 'canceled_invoice_number is required for credit notes.', 422);
        }

        // ── 4. Load participant by sender_peppol_id (must belong to this user) ──
        $senderPeppolId = trim($body['sender_peppol_id']);
        $participant = $this->participantModel
            ->where('peppol_id', $senderPeppolId)
            ->where('user_id', $userId)
            ->first();

        if (!$participant) {
            $this->logRequest($userId, null, $credentialId, '/api/invoices', 'POST', $body, ['error' => 'sender_not_found'], 404, $environment, 0);
            return $this->jsonError('SENDER_NOT_FOUND', 'No participant found for sender_peppol_id under your account.', 404);
        }

        $participantId = (int) $participant['id'];

        // ── 5. Send via PeppolService ────────────────────────────────────────
        try {
            $sendResult = $this->peppolService->send($body, $participant);
        } catch (\RuntimeException $e) {
            log_message('error', 'PeppolService error: ' . $e->getMessage());
            return $this->jsonError('BUILD_ERROR', $e->getMessage(), 422);
        }

        $success  = !empty($sendResult['success']);
        $txStatus = $success ? InvoiceModel::TX_SENT : InvoiceModel::TX_FAILED;

        // ── 6. Store invoice in DB ───────────────────────────────────────────
        $basic = $body['basicdetails'][0]    ?? [];
        $cust  = $body['customer_details'][0] ?? [];

        $subtotal  = 0;
        $taxAmount = 0;
        foreach (($body['itemdetails'] ?? []) as $line) {
            $lineAmt   = (float) ($line['quantity'] ?? 0) * (float) ($line['price'] ?? 0);
            $lineTax   = $lineAmt * ((float) ($line['vat_perc'] ?? 0) / 100);
            $subtotal  += $lineAmt;
            $taxAmount += $lineTax;
        }
        $total = $subtotal + $taxAmount;

        $supplierPeppolId = $participant['peppol_id'] ?? null;
        $supplierName     = $participant['name']      ?? null;

        $buyerName     = $cust['VAT_name'] ?? null;
        $buyerPeppolId = $body['receiver_peppol_id'] ?? null;
        if (!$buyerPeppolId && !empty($body['receiver_scheme'])) {
            $buyerPeppolId = $body['receiver_scheme'] . ':' . ($cust['VAT_number'] ?? '');
        }

        $rawXml = $sendResult['invoice']['xml'] ?? null;

        $this->invoiceModel->insert([
            'participant_id'      => $participantId,
            'direction'           => InvoiceModel::DIR_OUTBOUND,
            'document_type'       => self::MODE_TO_DOC_TYPE[$mode],
            'environment'         => $environment,
            'invoice_number'      => $basic['inv_no']   ?? '',
            'issue_date'          => $basic['inv_date']  ?? null,
            'due_date'            => $basic['due_date']  ?? null,
            'currency'            => strtoupper($basic['currency'] ?? 'AED'),
            'supplier_name'       => $supplierName,
            'supplier_peppol_id'  => $supplierPeppolId,
            'buyer_name'          => $buyerName,
            'buyer_peppol_id'     => $buyerPeppolId,
            'subtotal'            => round($subtotal, 2),
            'tax_amount'          => round($taxAmount, 2),
            'total'               => round($total, 2),
            'transmission_status' => $txStatus,
            'status'              => InvoiceModel::STATUS_ACTIVE,
            'xml_file_path'       => $sendResult['invoice']['xml_filepath'] ?? null,
            'raw_xml'             => $rawXml,
        ]);

        $invoiceId = $this->invoiceModel->getInsertID();

        // ── 7. Transmission events ───────────────────────────────────────────
        $this->transmissionModel->addEvent(
            $invoiceId,
            InvoiceTransmissionModel::EVENT_CREATED,
            'Invoice received via API.',
            InvoiceTransmissionModel::LEVEL_INFO
        );

        if ($success) {
            $this->transmissionModel->addEvent(
                $invoiceId,
                InvoiceTransmissionModel::EVENT_SUBMITTED,
                'Submitted to Peppol network via phase4.',
                InvoiceTransmissionModel::LEVEL_SUCCESS
            );
        } else {
            $this->transmissionModel->addEvent(
                $invoiceId,
                InvoiceTransmissionModel::EVENT_FAILED,
                $sendResult['error'] ?? 'Peppol submission failed.',
                InvoiceTransmissionModel::LEVEL_ERROR
            );
        }

        // ── 8. Log API request ───────────────────────────────────────────────
        $responseData = [
            'invoice_id'     => $invoiceId,
            'invoice_number' => $basic['inv_no'] ?? '',
            'mode'           => $mode,
            'status'         => $success ? 'sent' : 'failed',
            'peppol_response' => $sendResult,
        ];

        $this->logRequest(
            $userId,
            $participantId,
            $credentialId,
            '/api/invoices',
            'POST',
            $body,
            $responseData,
            $success ? 201 : 502,
            $environment,
            $success ? 1 : 0
        );

        if (!$success) {
            return $this->jsonError(
                'PEPPOL_ERROR',
                $sendResult['error'] ?? 'Peppol transmission failed.',
                502
            );
        }

        return $this->jsonSuccess($responseData, 201);
    }
}
