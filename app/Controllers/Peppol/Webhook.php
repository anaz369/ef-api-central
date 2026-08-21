<?php

namespace App\Controllers\Peppol;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\InvoiceTransmissionModel;
use App\Models\ParticipantModel;

/**
 * Webhook endpoint called by phase4 when an inbound PEPPOL invoice arrives.
 *
 * POST /peppol/webhook
 *
 * Headers expected from phase4:
 *   X-Webhook-Token    — must match PHASE4_WEBHOOK_TOKEN
 *   X-Peppol-Sender    — sender participant ID
 *   X-Peppol-DocType   — full PEPPOL document type URI
 *   X-Peppol-Process   — process URI
 *   X-Peppol-Filepath  — path where phase4 saved the .sbd file on EC2
 *
 * Always returns HTTP 200 so phase4 does not retry.
 */
class Webhook extends BaseController
{
    // Must match phase4.webhook.token in application.properties
    private const PHASE4_WEBHOOK_TOKEN = 'whpk_d9f3a8b2e5c1d4f7a3b6e8c2d1f4a9b5';

    public function receive(): \CodeIgniter\HTTP\Response
    {
        // ── 1. Verify token ──────────────────────────────────────────────────
        $token = $this->request->getHeaderLine('X-Webhook-Token');
        if ($token !== self::PHASE4_WEBHOOK_TOKEN) {
            log_message('warning', 'Peppol webhook: invalid token from IP=' . $this->request->getIPAddress());
            return $this->response->setStatusCode(401)->setBody('Unauthorized');
        }

        // ── 2. Read raw XML body ─────────────────────────────────────────────
        $xml = $this->request->getBody();
        if (empty($xml)) {
            return $this->response->setStatusCode(400)->setBody('Empty body');
        }

        // ── 3. Read PEPPOL metadata headers ─────────────────────────────────
        $sender      = $this->request->getHeaderLine('X-Peppol-Sender');
        $docTypeId   = $this->request->getHeaderLine('X-Peppol-DocType');
        $processId   = $this->request->getHeaderLine('X-Peppol-Process');
        $ec2Filepath = $this->request->getHeaderLine('X-Peppol-Filepath');

        // ── 4. Parse UBL XML ─────────────────────────────────────────────────
        $invoice = $this->parseUBL($xml);
        if ($invoice === null) {
            log_message('error', "Peppol webhook: failed to parse UBL XML from sender={$sender}");
            // Still return 200 — phase4 must not retry malformed documents
            return $this->response->setStatusCode(200)
                ->setJSON(['status' => 'parse_error', 'sender' => $sender]);
        }

        // ── 5. Save XML to local writable storage ────────────────────────────
        $localXmlPath = $this->saveXmlToFile($xml, $invoice['invoice_no']);

        // ── 6. Find participant by buyer VAT (the receiver = our client) ─────
        $participantModel = new ParticipantModel();
        $participant      = $participantModel->where('trn', $invoice['buyer_vat'])->first();
        $participantId    = $participant['id'] ?? 0;

        // ── 7. Determine document_type constant ──────────────────────────────
        $docType = match ($invoice['type_code']) {
            '381'   => InvoiceModel::DOC_CREDIT_NOTE,
            '389'   => InvoiceModel::DOC_SB_INVOICE,
            '261'   => InvoiceModel::DOC_SB_CREDIT,
            default => InvoiceModel::DOC_INVOICE,
        };

        // ── 8. Store in tbl_invoices ─────────────────────────────────────────
        $invoiceModel = new InvoiceModel();
        $invoiceModel->insert([
            'participant_id'      => $participantId,
            'direction'           => InvoiceModel::DIR_INBOUND,
            'document_type'       => $docType,
            'environment'         => 0, // default test; phase4 does not send environment
            'invoice_number'      => $invoice['invoice_no'],
            'issue_date'          => $invoice['issue_date'] ?: null,
            'currency'            => $invoice['currency'] ?: 'AED',
            'supplier_name'       => $invoice['seller_name'],
            'supplier_peppol_id'  => $sender,
            'buyer_name'          => $invoice['buyer_name'],
            'buyer_peppol_id'     => null,
            'subtotal'            => (float) ($invoice['tax_exclusive_amt'] ?? 0),
            'tax_amount'          => (float) ($invoice['total_tax'] ?? 0),
            'total'               => (float) ($invoice['tax_inclusive_amt'] ?? 0),
            'transmission_status' => InvoiceModel::TX_RECEIVED,
            'status'              => InvoiceModel::STATUS_ACTIVE,
            'xml_file_path'       => $localXmlPath,
            'raw_xml'             => $xml,
        ]);

        $invoiceId = $invoiceModel->getInsertID();

        // ── 9. Log transmission event ────────────────────────────────────────
        $txModel = new InvoiceTransmissionModel();
        $txModel->addEvent(
            $invoiceId,
            InvoiceTransmissionModel::EVENT_AP_RECEIVED,
            "Received via Peppol from sender={$sender} docType={$docTypeId}",
            InvoiceTransmissionModel::LEVEL_SUCCESS
        );

        log_message('info', "Peppol webhook: saved inbound invoice #{$invoice['invoice_no']} id={$invoiceId} sender={$sender}");

        // ── 10. Always return 200 so phase4 does not retry ───────────────────
        return $this->response->setStatusCode(200)
            ->setJSON(['status' => 'ok', 'invoice_no' => $invoice['invoice_no']]);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function parseUBL(string $xml): ?array
    {
        $dom = new \DOMDocument();
        if (!@$dom->loadXML($xml)) {
            return null;
        }

        $ns  = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';
        $cac = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
        $root = $dom->documentElement;

        $get = function (string $tag) use ($root, $ns): string {
            $nodes = $root->getElementsByTagNameNS($ns, $tag);
            return $nodes->length > 0 ? trim($nodes->item(0)->nodeValue) : '';
        };

        $getIn = function (\DOMElement $el, string $tag) use ($ns): string {
            $nodes = $el->getElementsByTagNameNS($ns, $tag);
            return $nodes->length > 0 ? trim($nodes->item(0)->nodeValue) : '';
        };

        $docKind  = $root->localName; // 'Invoice' or 'CreditNote'
        $typeCode = $docKind === 'Invoice' ? $get('InvoiceTypeCode') : $get('CreditNoteTypeCode');

        $sellerVat = $sellerName = '';
        $supplierNodes = $root->getElementsByTagNameNS($cac, 'AccountingSupplierParty');
        if ($supplierNodes->length > 0) {
            $sellerVat  = $getIn($supplierNodes->item(0), 'CompanyID');
            $sellerName = $getIn($supplierNodes->item(0), 'RegistrationName')
                       ?: $getIn($supplierNodes->item(0), 'Name');
        }

        $buyerVat = $buyerName = '';
        $customerNodes = $root->getElementsByTagNameNS($cac, 'AccountingCustomerParty');
        if ($customerNodes->length > 0) {
            $buyerVat  = $getIn($customerNodes->item(0), 'CompanyID');
            $buyerName = $getIn($customerNodes->item(0), 'RegistrationName')
                      ?: $getIn($customerNodes->item(0), 'Name');
        }

        $taxExcl = $taxIncl = $payable = $totalTax = '';
        $totalNodes = $root->getElementsByTagNameNS($cac, 'LegalMonetaryTotal');
        if ($totalNodes->length > 0) {
            $taxExcl = $getIn($totalNodes->item(0), 'TaxExclusiveAmount');
            $taxIncl = $getIn($totalNodes->item(0), 'TaxInclusiveAmount');
            $payable = $getIn($totalNodes->item(0), 'PayableAmount');
        }

        $taxTotalNodes = $root->getElementsByTagNameNS($cac, 'TaxTotal');
        if ($taxTotalNodes->length > 0) {
            $totalTax = $getIn($taxTotalNodes->item(0), 'TaxAmount');
        }

        return [
            'doc_type'          => $docKind,
            'invoice_no'        => $get('ID'),
            'issue_date'        => $get('IssueDate'),
            'type_code'         => $typeCode,
            'currency'          => $get('DocumentCurrencyCode'),
            'customization_id'  => $get('CustomizationID'),
            'seller_vat'        => $sellerVat,
            'seller_name'       => $sellerName,
            'buyer_vat'         => $buyerVat,
            'buyer_name'        => $buyerName,
            'tax_exclusive_amt' => $taxExcl,
            'tax_inclusive_amt' => $taxIncl,
            'payable_amount'    => $payable,
            'total_tax'         => $totalTax,
        ];
    }

    private function saveXmlToFile(string $xml, string $invoiceNo): string
    {
        $subDir = WRITEPATH . 'peppol_xml/received/' . date('Y-m') . '/';
        if (!is_dir($subDir)) {
            mkdir($subDir, 0755, true);
        }

        $safeName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $invoiceNo);
        $filename  = $safeName . '_' . date('YmdHis') . '.xml';
        $fullPath  = $subDir . $filename;

        if (file_put_contents($fullPath, $xml) === false) {
            log_message('error', "Peppol webhook: failed to write XML to {$fullPath}");
            return '';
        }
        return $fullPath;
    }
}
