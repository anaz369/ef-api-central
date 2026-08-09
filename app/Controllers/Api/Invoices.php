<?php

namespace App\Controllers\Api;

use App\Models\InvoiceModel;
use App\Models\InvoiceTransmissionModel;
use App\Models\ParticipantModel;

class Invoices extends BaseApiController
{
    // URL of send_peppol.php — change for production deployment
    private const SEND_PEPPOL_URL = 'http://localhost:8888/peppol/send_peppol.php';

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

    public function __construct()
    {
        parent::__construct();
        $this->invoiceModel      = new InvoiceModel();
        $this->transmissionModel = new InvoiceTransmissionModel();
        $this->participantModel  = new ParticipantModel();
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

        $participantId = (int) $tokenRow['participant_id'];
        $environment   = (int) $tokenRow['environment'];
        $credentialId  = (int) $tokenRow['credential_id'];

        $body = $this->request->getJSON(true) ?? [];

        // ── 2. Validate mode ─────────────────────────────────────────────────
        $mode = (int) ($body['mode'] ?? 0);
        if (!array_key_exists($mode, self::VALID_MODES)) {
            $resp = ['error' => 'invalid_mode'];
            $this->logRequest($participantId, $credentialId, '/api/invoices', 'POST', $body, $resp, 422, $environment, 0);
            return $this->jsonError(
                'INVALID_MODE',
                'mode must be 1 (invoice), 3 (credit_note), 4 (sb_invoice), or 5 (sb_credit_note).',
                422
            );
        }

        // ── 3. Validate required fields ──────────────────────────────────────
        foreach (['basicdetails', 'location', 'customer_details', 'itemdetails'] as $field) {
            if (empty($body[$field])) {
                $this->logRequest($participantId, $credentialId, '/api/invoices', 'POST', $body, ['error' => "missing $field"], 422, $environment, 0);
                return $this->jsonError('VALIDATION_ERROR', "Missing required field: {$field}.", 422);
            }
        }

        // receiver_peppol_id required for invoice & credit note
        if (in_array($mode, [1, 3]) && empty($body['receiver_peppol_id'])) {
            return $this->jsonError('VALIDATION_ERROR', 'receiver_peppol_id is required for invoice and credit note.', 422);
        }

        // receiver_scheme required for self-billing
        if (in_array($mode, [4, 5]) && empty($body['receiver_scheme'])) {
            return $this->jsonError('VALIDATION_ERROR', 'receiver_scheme is required for self-billing documents.', 422);
        }

        // canceled_invoice_number required for credit notes
        if (in_array($mode, [3, 5]) && empty($body['canceled_invoice_number'])) {
            return $this->jsonError('VALIDATION_ERROR', 'canceled_invoice_number is required for credit notes.', 422);
        }

        // ── 4. Inject tknd from participant TRN ──────────────────────────────
        $participant   = $this->participantModel->find($participantId);
        $body['tknd']  = $participant['trn'] ?? 'api_central';

        // ── 5. Forward to send_peppol.php ────────────────────────────────────
        $sendResponse = $this->forwardToSendPeppol($body);

        $success  = ($sendResponse['status'] ?? '') === 'success';
        $txStatus = $success ? InvoiceModel::TX_SENT : InvoiceModel::TX_FAILED;

        // ── 6. Store invoice in DB ───────────────────────────────────────────
        $basic   = $body['basicdetails'][0]    ?? [];
        $cust    = $body['customer_details'][0] ?? [];

        // Derive totals from line items (sum of quantity × price + vat)
        $subtotal   = 0;
        $taxAmount  = 0;
        foreach (($body['itemdetails'] ?? []) as $line) {
            $lineAmt   = (float)($line['quantity'] ?? 0) * (float)($line['price'] ?? 0);
            $lineTax   = $lineAmt * ((float)($line['vat_perc'] ?? 0) / 100);
            $subtotal  += $lineAmt;
            $taxAmount += $lineTax;
        }
        $total = $subtotal + $taxAmount;

        // Supplier info comes from the authenticated participant
        $supplierPeppolId = $participant['peppol_id'] ?? null;
        $supplierName     = $participant['name']      ?? null;

        // Receiver (buyer for modes 1/3, actual supplier for modes 4/5)
        $buyerName      = $cust['VAT_name'] ?? null;
        $buyerPeppolId  = $body['receiver_peppol_id'] ?? null;
        if (!$buyerPeppolId && !empty($body['receiver_scheme'])) {
            $buyerPeppolId = $body['receiver_scheme'] . ':' . ($cust['VAT_number'] ?? '');
        }

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
            'raw_xml'             => $sendResponse['data']['xml'] ?? null,
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
                $sendResponse['message'] ?? 'Submitted to Peppol network.',
                InvoiceTransmissionModel::LEVEL_SUCCESS
            );
        } else {
            $this->transmissionModel->addEvent(
                $invoiceId,
                InvoiceTransmissionModel::EVENT_FAILED,
                $sendResponse['message'] ?? 'Submission failed.',
                InvoiceTransmissionModel::LEVEL_ERROR
            );
        }

        // ── 8. Log API request ───────────────────────────────────────────────
        $responseData = [
            'invoice_id'     => $invoiceId,
            'invoice_number' => $basic['inv_no'] ?? '',
            'mode'           => $mode,
            'status'         => $success ? 'sent' : 'failed',
            'peppol_response' => $sendResponse,
        ];

        $this->logRequest(
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
                $sendResponse['message'] ?? 'Peppol transmission failed.',
                502
            );
        }

        return $this->jsonSuccess($responseData, 201);
    }

    /**
     * Forward the invoice data payload to send_peppol.php.
     * Wraps as {"invoice": {"data": $data}} — same format as sentInvoice2() in Welcome.php.
     */
    private function forwardToSendPeppol(array $data): array
    {
        $ch = curl_init(self::SEND_PEPPOL_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode(['invoice' => ['data' => $data]]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            return ['status' => 'error', 'message' => 'Could not reach Peppol sender service.'];
        }

        return json_decode($response, true) ?? ['status' => 'error', 'message' => 'Invalid response from sender service.'];
    }
}
