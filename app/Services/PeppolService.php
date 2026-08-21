<?php

namespace App\Services;

/**
 * PeppolService — builds PINT AE UBL XML and submits to phase4 AS4 gateway.
 *
 * Migrated from the standalone peppol/src/PeppolSender.php.
 * Seller details now come from the tbl_participants row (passed in as $participant)
 * instead of a separate HTTP call to the old ethicfin-rfq API.
 */
class PeppolService
{
    // phase4 AS4 gateway
    private const PHASE4_URL   = 'https://as4.ethicfin.com';
    private const PHASE4_TOKEN = 'd4ad13cbe6dcf250e6545a8cb65eac613afe011cf36ebbb0e2150726fda7b6f2';

    // UAE Peppol scheme
    private const PEPPOL_SCHEME  = '0242';
    private const PEPPOL_SCHEME_URN = 'iso6523-actorid-upis';

    // PINT AE CustomizationIDs
    private const CUSTOMIZATION_PINT_AE  = 'urn:peppol:pint:billing-1@ae-1';
    private const CUSTOMIZATION_SELFBILLING_PINT_AE = 'urn:peppol:pint:selfbilling-1@ae-1';

    // PINT AE document type IDs
    private const DOCTYPE_INVOICE_PINT_AE =
        'peppol-doctype-wildcard::urn:oasis:names:specification:ubl:schema:xsd:Invoice-2' .
        '::Invoice##urn:peppol:pint:billing-1@ae-1::2.1';

    private const DOCTYPE_CREDITNOTE_PINT_AE =
        'peppol-doctype-wildcard::urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2' .
        '::CreditNote##urn:peppol:pint:billing-1@ae-1::2.1';

    private const DOCTYPE_SELFBILLING_INVOICE_PINT_AE =
        'peppol-doctype-wildcard::urn:oasis:names:specification:ubl:schema:xsd:Invoice-2' .
        '::Invoice##urn:peppol:pint:selfbilling-1@ae-1::2.1';

    private const DOCTYPE_SELFBILLING_CREDITNOTE_PINT_AE =
        'peppol-doctype-wildcard::urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2' .
        '::CreditNote##urn:peppol:pint:selfbilling-1@ae-1::2.1';

    // Process IDs
    private const PROCESS_ID = 'cenbii-procid-ubl::urn:peppol:bis:billing';
    private const PROCESS_ID_SELFBILLING = 'cenbii-procid-ubl::urn:peppol:bis:selfbilling';

    // Directory of UBL XML templates (relative to this file)
    private const TEMPLATE_DIR = __DIR__ . '/../Libraries/PeppolTemplates/';

    /**
     * Build PINT AE UBL XML and submit it to the phase4 AS4 gateway.
     *
     * @param  array $invoiceData  The invoice payload (same structure as the REST API body).
     * @param  array $participant  Row from tbl_participants for the authenticated sender.
     * @return array               ['success' => bool, 'http_code' => int, 'data' => ..., 'invoice' => ...]
     * @throws \RuntimeException   On missing required data.
     */
    public function send(array $invoiceData, array $participant): array
    {
        // ── Step 1: Build seller info from the CI4 participant record ────────
        $sellerScheme = $participant['peppol_scheme'] ?? self::PEPPOL_SCHEME;

        if (empty($participant['trn'])) {
            throw new \RuntimeException('Seller TRN is missing from participant record.');
        }

        $seller = [
            'vat'     => $participant['trn'],
            'name'    => $participant['name'] ?? '',
            'street'  => $participant['address_line1'] ?? '',
            'city'    => $participant['city'] ?? '',
            'postal'  => $participant['postal_zone'] ?? '',
            'country' => $participant['country'] ?? 'AE',
            'state'   => $participant['emirate'] ?? $participant['city'] ?? '',
        ];

        // ── Step 2: Build buyer info from invoice payload ────────────────────
        $custDetails = $invoiceData['customer_details'][0] ?? [];
        if (empty($custDetails['VAT_number'])) {
            throw new \RuntimeException('Buyer VAT_number is missing from customer_details.');
        }

        $buyer = [
            'vat'     => $custDetails['VAT_number'],
            'name'    => $custDetails['VAT_name'] ?? '',
            'street'  => $custDetails['street']      ?? $invoiceData['location'][0]['street']      ?? '',
            'city'    => $custDetails['city']         ?? $invoiceData['location'][0]['city']         ?? '',
            'postal'  => $custDetails['postal_zone']  ?? $invoiceData['location'][0]['postal_zone']  ?? '',
            'country' => $custDetails['country_code'] ?? $invoiceData['location'][0]['country_code'] ?? 'AE',
            'state'   => $custDetails['state']        ?? $custDetails['city']                        ?? $invoiceData['location'][0]['city'] ?? '',
        ];

        // ── Step 3: Build line items ─────────────────────────────────────────
        if (empty($invoiceData['itemdetails'])) {
            throw new \RuntimeException('itemdetails is empty.');
        }

        $lineItems = [];
        $i = 1;
        foreach ($invoiceData['itemdetails'] as $item) {
            $vatPercent = is_numeric($item['vat_perc']) ? (float) $item['vat_perc'] : 0.0;
            $allowances = [];
            if (!empty($item['allowance']) && is_array($item['allowance'])) {
                foreach ($item['allowance'] as $a) {
                    $allowances[] = [
                        'type'        => $a['type'],
                        'amount'      => round($a['amount'], 2),
                        'reason'      => $a['reason'],
                        'reason_code' => $a['reason_code'],
                    ];
                }
            }
            $lineItems[] = [
                'id'                 => $i++,
                'name'               => $item['item_name'] ?: $item['description'],
                'description'        => $item['description'] ?: $item['item_name'] ?: '-',
                'uom'                => $item['unit'] ?: 'PCS',
                'quantity'           => $item['quantity'],
                'unit_price'         => round($item['price'], 2),
                'gross_price'        => round($item['gross_price'] ?? $item['price'], 2),
                'vat_percent'        => $vatPercent,
                'tax_category'       => $item['tax_category'] ?? 'S',
                'exempt_reason'      => $item['exempt_reason'] ?? '',
                'exempt_reason_code' => $item['exempt_reason_code'] ?? '',
                'allowances'         => $allowances,
            ];
        }

        // ── Step 4: Invoice header ───────────────────────────────────────────
        $basic       = $invoiceData['basicdetails'][0];
        $issueDate   = date('Y-m-d', strtotime($basic['inv_date']));
        $currency    = $basic['currency'] ?? 'AED';
        $mode        = (int) $invoiceData['mode'];
        $paymentCode = $invoiceData['payment_code'] ?? 42;

        $uuid               = $this->generateUuid();
        $profileExecutionId = '00000000'; // domestic non-export (BTAE-02)

        // DueDate (ibr-127-ae): required for invoices (380, 383, 389)
        $rawDue = $basic['due_date'] ?? '';
        if ($rawDue) {
            $dueDateXml = '<cbc:DueDate>' . date('Y-m-d', strtotime($rawDue)) . '</cbc:DueDate>';
        } elseif (in_array($mode, [1, 2, 4])) {
            $dueDateXml = '<cbc:DueDate>' . date('Y-m-d', strtotime($issueDate . ' +30 days')) . '</cbc:DueDate>';
        } else {
            $dueDateXml = '';
        }

        // PayeeFinancialAccount (ibr-192-ae): required for credit transfer payment codes
        $paymentAccount = $invoiceData['payment_account'] ?? $invoiceData['iban'] ?? '';
        if ($paymentAccount && in_array($paymentCode, [30, 42])) {
            $paymentAccountXml = "<cac:PayeeFinancialAccount>\n            <cbc:ID>" . htmlspecialchars($paymentAccount) . "</cbc:ID>\n        </cac:PayeeFinancialAccount>";
        } else {
            $paymentAccountXml = '';
        }

        // BillingReference (modes 3 and 5 credit notes require IssueDate per ibr-055-ae)
        $billingRef = '';
        if (in_array($mode, [2, 3, 5])) {
            $canceledInvNo   = $invoiceData['canceled_invoice_number'] ?? '';
            $canceledInvDate = $invoiceData['canceled_invoice_date']   ?? '';
            if ($canceledInvNo) {
                $issueDateXml = (in_array($mode, [3, 5]) && $canceledInvDate)
                    ? "\n            <cbc:IssueDate>{$canceledInvDate}</cbc:IssueDate>"
                    : '';
                $billingRef = "<cac:BillingReference>\n" .
                              "        <cac:InvoiceDocumentReference>\n" .
                              "            <cbc:ID>{$canceledInvNo}</cbc:ID>" .
                              $issueDateXml . "\n" .
                              "        </cac:InvoiceDocumentReference>\n" .
                              "    </cac:BillingReference>";
            }
        }

        // DiscrepancyResponse (self-billing credit note only, optional)
        $discrepancyXml = '';
        if ($mode === 5) {
            $discrepancyCode = $invoiceData['discrepancy_reason_code'] ?? '';
            if ($discrepancyCode) {
                $discrepancyXml = "<cac:DiscrepancyResponse>\n" .
                                  "        <cbc:ResponseCode>{$discrepancyCode}</cbc:ResponseCode>\n" .
                                  "    </cac:DiscrepancyResponse>";
            }
        }

        // ── Step 5: Determine template, doc-type, process per mode ──────────
        switch ($mode) {
            case 3:
                $invoiceTypeCode = '381';
                $customizationId = self::CUSTOMIZATION_PINT_AE;
                $docTypeId       = self::DOCTYPE_CREDITNOTE_PINT_AE;
                $processId       = self::PROCESS_ID;
                $template        = require self::TEMPLATE_DIR . 'creditnote.php';
                $lineTemplate    = require self::TEMPLATE_DIR . 'creditnote_line_item.php';
                break;
            case 4:
                $invoiceTypeCode = '389';
                $customizationId = self::CUSTOMIZATION_SELFBILLING_PINT_AE;
                $docTypeId       = self::DOCTYPE_SELFBILLING_INVOICE_PINT_AE;
                $processId       = self::PROCESS_ID_SELFBILLING;
                $template        = require self::TEMPLATE_DIR . 'selfbilling_invoice.php';
                $lineTemplate    = require self::TEMPLATE_DIR . 'line_item.php';
                break;
            case 5:
                $invoiceTypeCode = '261';
                $customizationId = self::CUSTOMIZATION_SELFBILLING_PINT_AE;
                $docTypeId       = self::DOCTYPE_SELFBILLING_CREDITNOTE_PINT_AE;
                $processId       = self::PROCESS_ID_SELFBILLING;
                $template        = require self::TEMPLATE_DIR . 'selfbilling_creditnote.php';
                $lineTemplate    = require self::TEMPLATE_DIR . 'creditnote_line_item.php';
                break;
            default: // mode 1 (invoice)
                $invoiceTypeCode = '380';
                $customizationId = self::CUSTOMIZATION_PINT_AE;
                $docTypeId       = self::DOCTYPE_INVOICE_PINT_AE;
                $processId       = self::PROCESS_ID;
                $template        = require self::TEMPLATE_DIR . 'invoice.php';
                $lineTemplate    = require self::TEMPLATE_DIR . 'line_item.php';
                break;
        }

        // ── Step 6: Calculate totals ─────────────────────────────────────────
        $totals = $this->calculateTotals($lineItems);

        // ── Step 7: Build line items XML ─────────────────────────────────────
        $lineItemsXml = '';
        foreach ($lineItems as $item) {
            $lineExtAmount = round($item['quantity'] * $item['unit_price'], 2);

            $allowancesXml = '';
            foreach ($item['allowances'] as $a) {
                if ($a['amount'] > 0) {
                    $chargeIndicator = ($a['type'] === 'true') ? 'true' : 'false';
                    $allowancesXml  .= "<cac:AllowanceCharge>\n" .
                        "            <cbc:ChargeIndicator>{$chargeIndicator}</cbc:ChargeIndicator>\n" .
                        "            <cbc:AllowanceChargeReasonCode>{$a['reason_code']}</cbc:AllowanceChargeReasonCode>\n" .
                        "            <cbc:AllowanceChargeReason>{$a['reason']}</cbc:AllowanceChargeReason>\n" .
                        "            <cbc:Amount currencyID=\"{$currency}\">" . number_format($a['amount'], 2, '.', '') . "</cbc:Amount>\n" .
                        "        </cac:AllowanceCharge>";
                    if ($a['type'] === 'true') {
                        $lineExtAmount += $a['amount'];
                    } else {
                        $lineExtAmount -= $a['amount'];
                    }
                }
            }
            $lineExtAmount = round($lineExtAmount, 2);

            $exemptionXml = '';
            if (!empty($item['exempt_reason']) && $item['tax_category'] !== 'S') {
                $exemptionXml = "<cbc:TaxExemptionReasonCode>{$item['exempt_reason_code']}</cbc:TaxExemptionReasonCode>\n" .
                                "                <cbc:TaxExemptionReason>{$item['exempt_reason']}</cbc:TaxExemptionReason>";
            }

            $taxCat      = $item['tax_category'];
            $lineAmtAed  = number_format($lineExtAmount, 2, '.', '');
            if ($taxCat === 'E' || $taxCat === 'O') {
                $itemPriceExtXml = "<cac:ItemPriceExtension>\n" .
                    "            <cbc:Amount currencyID=\"{$currency}\">{$lineAmtAed}</cbc:Amount>\n" .
                    "        </cac:ItemPriceExtension>";
            } else {
                $lineVatAed      = number_format(round($lineExtAmount * ($item['vat_percent'] / 100), 2), 2, '.', '');
                $itemPriceExtXml = "<cac:ItemPriceExtension>\n" .
                    "            <cbc:Amount currencyID=\"{$currency}\">{$lineAmtAed}</cbc:Amount>\n" .
                    "            <cac:TaxTotal>\n" .
                    "                <cbc:TaxAmount currencyID=\"{$currency}\">{$lineVatAed}</cbc:TaxAmount>\n" .
                    "            </cac:TaxTotal>\n" .
                    "        </cac:ItemPriceExtension>";
            }

            $lineItemsXml .= str_replace(
                [
                    'SET_LINE_ID', 'SET_UOM', 'SET_QUANTITY',
                    'SET_CURRENCY', 'SET_LINE_EXTENSION_AMOUNT',
                    'SET_LINE_ALLOWANCES', 'SET_ITEM_NAME', 'SET_ITEM_DESC',
                    'SET_TAX_CATEGORY', 'SET_TAX_PERCENT',
                    'SET_EXEMPTION_REASON', 'SET_UNIT_PRICE',
                    'SET_GROSS_PRICE', 'SET_ITEM_PRICE_EXT',
                ],
                [
                    $item['id'], $item['uom'], $item['quantity'],
                    $currency, $lineAmtAed,
                    $allowancesXml, htmlspecialchars($item['name']), htmlspecialchars($item['description']),
                    $taxCat, $item['vat_percent'],
                    $exemptionXml, number_format($item['unit_price'], 2, '.', ''),
                    number_format($item['gross_price'], 2, '.', ''), $itemPriceExtXml,
                ],
                $lineTemplate
            );
        }

        // ── Step 8: Build tax totals XML ─────────────────────────────────────
        $taxTotalsXml = $this->buildTaxTotalsXml($totals['tax_subtotals'], $totals['total_tax'], $currency);

        // ── Step 9: Fill template ────────────────────────────────────────────
        $xml = str_replace(
            [
                'SET_CUSTOMIZATION_ID',
                'SET_PROFILE_EXECUTION_ID',
                'SET_UUID',
                'SET_INVOICE_NUMBER',
                'SET_ISSUE_DATE',
                'SET_DUE_DATE',
                'SET_INVOICE_TYPE_CODE',
                'SET_CURRENCY',
                'SET_DISCREPANCY_RESPONSE',
                'SET_BILLING_REFERENCE',
                'SET_SELLER_SCHEME', 'SET_SELLER_VAT', 'SET_SELLER_NAME',
                'SET_SELLER_STREET', 'SET_SELLER_CITY', 'SET_SELLER_POSTAL', 'SET_SELLER_STATE', 'SET_SELLER_COUNTRY',
                'SET_BUYER_SCHEME',  'SET_BUYER_VAT',  'SET_BUYER_NAME',
                'SET_BUYER_STREET',  'SET_BUYER_CITY',  'SET_BUYER_POSTAL',  'SET_BUYER_STATE', 'SET_BUYER_COUNTRY',
                'SET_PAYMENT_CODE',
                'SET_PAYMENT_ACCOUNT',
                'SET_TAX_TOTALS',
                'SET_LINE_EXTENSION_TOTAL',
                'SET_TAX_EXCLUSIVE_TOTAL',
                'SET_TAX_INCLUSIVE_TOTAL',
                'SET_PAYABLE_AMOUNT',
                'SET_INVOICE_LINES',
            ],
            [
                $customizationId,
                $profileExecutionId,
                $uuid,
                $basic['inv_no'],
                $issueDate,
                $dueDateXml,
                $invoiceTypeCode,
                $currency,
                $discrepancyXml,
                $billingRef,
                $sellerScheme, $seller['vat'], htmlspecialchars($seller['name']),
                htmlspecialchars($seller['street']), htmlspecialchars($seller['city']),
                $seller['postal'], htmlspecialchars($seller['state']), $seller['country'],
                self::PEPPOL_SCHEME, $buyer['vat'], htmlspecialchars($buyer['name']),
                htmlspecialchars($buyer['street']), htmlspecialchars($buyer['city']),
                $buyer['postal'], htmlspecialchars($buyer['state']), $buyer['country'],
                $paymentCode,
                $paymentAccountXml,
                $taxTotalsXml,
                number_format($totals['line_extension_total'], 2, '.', ''),
                number_format($totals['tax_exclusive_total'],  2, '.', ''),
                number_format($totals['tax_inclusive_total'],  2, '.', ''),
                number_format($totals['payable_amount'],       2, '.', ''),
                $lineItemsXml,
            ],
            $template
        );

        log_message('debug', 'PeppolService: XML generated for invoice=' . $basic['inv_no'] . ' length=' . strlen($xml));

        // ── Step 10: Save XML to disk ────────────────────────────────────────
        $xmlFilepath = $this->saveSentXml($xml, $basic['inv_no']);

        // ── Step 11: POST to phase4 ──────────────────────────────────────────
        $senderPeppolId = self::PEPPOL_SCHEME_URN . '::' . $sellerScheme . ':' . $seller['vat'];

        if (!empty($invoiceData['receiver_peppol_id'])) {
            $receiverPeppolId = $invoiceData['receiver_peppol_id'];
        } else {
            $receiverScheme   = $invoiceData['receiver_scheme'] ?? self::PEPPOL_SCHEME;
            $receiverPeppolId = self::PEPPOL_SCHEME_URN . '::' . $receiverScheme . ':' . $buyer['vat'];
        }

        log_message('debug', "PeppolService: posting to phase4. sender={$senderPeppolId} receiver={$receiverPeppolId}");

        $result = $this->postToPhase4($xml, $senderPeppolId, $receiverPeppolId, $docTypeId, $processId, $seller['country']);

        log_message('debug', 'PeppolService: phase4 result=' . json_encode($result));

        if (!empty($result['success'])) {
            $docType         = in_array($mode, [3, 5]) ? 'CreditNote' : 'Invoice';
            $result['invoice'] = [
                'invoice_no'         => $basic['inv_no'],
                'issue_date'         => $issueDate,
                'doc_type'           => $docType,
                'type_code'          => $invoiceTypeCode,
                'currency'           => $currency,
                'customization_id'   => $customizationId,
                'seller_vat'         => $seller['vat'],
                'seller_name'        => $seller['name'],
                'buyer_vat'          => $buyer['vat'],
                'buyer_name'         => $buyer['name'],
                'tax_exclusive_amt'  => number_format($totals['tax_exclusive_total'], 2, '.', ''),
                'tax_inclusive_amt'  => number_format($totals['tax_inclusive_total'],  2, '.', ''),
                'payable_amount'     => number_format($totals['payable_amount'],       2, '.', ''),
                'total_tax'          => number_format($totals['total_tax'],            2, '.', ''),
                'peppol_sender_id'   => $senderPeppolId,
                'peppol_receiver_id' => $receiverPeppolId,
                'doc_type_id'        => $docTypeId,
                'process_id'         => $processId,
                'xml_filepath'       => $xmlFilepath,
                'xml'                => $xml,
            ];
        }

        return $result;
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    private function calculateTotals(array $lineItems): array
    {
        $taxSubtotals       = [];
        $lineExtensionTotal = 0.0;

        foreach ($lineItems as $item) {
            $lineAmt             = round($item['quantity'] * $item['unit_price'], 2);
            $lineExtensionTotal += $lineAmt;

            $cat = $item['tax_category'];
            if (!isset($taxSubtotals[$cat])) {
                $taxSubtotals[$cat] = [
                    'taxable_amount'     => 0.0,
                    'tax_amount'         => 0.0,
                    'percent'            => $item['vat_percent'],
                    'category'           => $cat,
                    'exempt_reason'      => $item['exempt_reason'],
                    'exempt_reason_code' => $item['exempt_reason_code'],
                ];
            }
            $taxSubtotals[$cat]['taxable_amount'] += $lineAmt;
            $taxSubtotals[$cat]['tax_amount']     += round($lineAmt * ($item['vat_percent'] / 100), 2);
        }

        $totalTax = array_sum(array_column($taxSubtotals, 'tax_amount'));

        return [
            'line_extension_total' => $lineExtensionTotal,
            'tax_exclusive_total'  => $lineExtensionTotal,
            'total_tax'            => $totalTax,
            'tax_inclusive_total'  => $lineExtensionTotal + $totalTax,
            'payable_amount'       => $lineExtensionTotal + $totalTax,
            'tax_subtotals'        => $taxSubtotals,
        ];
    }

    private function buildTaxTotalsXml(array $taxSubtotals, float $totalTax, string $currency): string
    {
        $subtotalsXml = '';
        foreach ($taxSubtotals as $subtotal) {
            $exemptionXml = '';
            if (!empty($subtotal['exempt_reason']) && $subtotal['category'] !== 'S') {
                $exemptionXml =
                    "\n            <cbc:TaxExemptionReasonCode>{$subtotal['exempt_reason_code']}</cbc:TaxExemptionReasonCode>" .
                    "\n            <cbc:TaxExemptionReason>{$subtotal['exempt_reason']}</cbc:TaxExemptionReason>";
            }

            $subtotalsXml .= "\n        <cac:TaxSubtotal>" .
                "\n            <cbc:TaxableAmount currencyID=\"{$currency}\">" . number_format($subtotal['taxable_amount'], 2, '.', '') . "</cbc:TaxableAmount>" .
                "\n            <cbc:TaxAmount currencyID=\"{$currency}\">" . number_format($subtotal['tax_amount'], 2, '.', '') . "</cbc:TaxAmount>" .
                "\n            <cac:TaxCategory>" .
                "\n                <cbc:ID>{$subtotal['category']}</cbc:ID>" .
                "\n                <cbc:Percent>{$subtotal['percent']}</cbc:Percent>" .
                $exemptionXml .
                "\n                <cac:TaxScheme><cbc:ID>VAT</cbc:ID></cac:TaxScheme>" .
                "\n            </cac:TaxCategory>" .
                "\n        </cac:TaxSubtotal>";
        }

        return "<cac:TaxTotal>" .
               "\n        <cbc:TaxAmount currencyID=\"{$currency}\">" . number_format($totalTax, 2, '.', '') . "</cbc:TaxAmount>" .
               $subtotalsXml .
               "\n    </cac:TaxTotal>";
    }

    private function saveSentXml(string $xml, string $invoiceNo): string
    {
        $subDir = WRITEPATH . 'peppol_xml/sent/' . date('Y-m') . '/';
        if (!is_dir($subDir)) {
            mkdir($subDir, 0755, true);
        }

        $safeName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $invoiceNo);
        $filename  = $safeName . '_' . date('YmdHis') . '.xml';
        $fullPath  = $subDir . $filename;

        if (file_put_contents($fullPath, $xml) === false) {
            log_message('error', "PeppolService: failed to write sent XML to {$fullPath}");
            return '';
        }
        return $fullPath;
    }

    private function postToPhase4(
        string $xml,
        string $senderId,
        string $receiverId,
        string $docTypeId,
        string $processId,
        string $countryC1
    ): array {
        $url = sprintf(
            '%s/api/sendas4/%s/%s/%s/%s/%s',
            self::PHASE4_URL,
            rawurlencode($senderId),
            rawurlencode($receiverId),
            rawurlencode($docTypeId),
            rawurlencode($processId),
            rawurlencode($countryC1)
        );

        log_message('debug', 'PeppolService: POST URL=' . $url);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $xml,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/xml',
                'X-Token: ' . self::PHASE4_TOKEN,
            ],
        ]);

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => 'Connection to phase4 failed: ' . $curlError];
        }

        $result = json_decode($responseBody, true) ?? ['raw' => $responseBody];

        if ($httpCode === 200) {
            return ['success' => true, 'http_code' => $httpCode, 'data' => $result];
        }

        return ['success' => false, 'http_code' => $httpCode, 'data' => $result];
    }
}
