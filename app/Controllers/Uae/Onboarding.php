<?php

namespace App\Controllers\Uae;

use App\Controllers\BaseController;
use App\Models\PeppolParticipantModel;

class Onboarding extends BaseController
{
    // -----------------------------------------------------------------------
    // Test mode — set TRUE to bypass FTA API calls (no GSB token needed)
    // -----------------------------------------------------------------------
    const TEST_MODE = true;

    // -----------------------------------------------------------------------
    // FTA / GSB API — base URL
    // -----------------------------------------------------------------------
    const FTA_BASE_URL = 'https://api.gsb.government.ae/gateway/validateTaxPayerDetails_FTAX/1.0';

    // -----------------------------------------------------------------------
    // Header 1: GW-APIKey — static key issued via UAE API Marketplace
    // -----------------------------------------------------------------------
    const GSB_API_KEY = 'YOUR_GW_APIKEY_HERE';

    // -----------------------------------------------------------------------
    // Header 2: Authorization — OAuth2 Bearer token from GSB getAccessToken
    // -----------------------------------------------------------------------
    const GSB_TOKEN_URL     = 'https://api.gsb.government.ae/invoke/pub.apigateway.oauth2/getAccessToken';
    const GSB_SCOPE         = '9b9346b3-5b18-11f0-a374-856a43324051';
    const GSB_CLIENT_ID     = 'YOUR_GSB_CLIENT_ID_HERE';
    const GSB_CLIENT_SECRET = 'YOUR_GSB_CLIENT_SECRET_HERE';

    // -----------------------------------------------------------------------
    // Header 3: CustomAuth — Bearer token from FTAX generateAccessToken_FTAX
    // -----------------------------------------------------------------------
    const FTAX_TOKEN_URL     = 'YOUR_FTAX_TOKEN_URL_HERE'; // e.g. from UAE API Marketplace portal
    const FTAX_CLIENT_ID     = 'YOUR_FTAX_CLIENT_ID_HERE';
    const FTAX_CLIENT_SECRET = 'YOUR_FTAX_CLIENT_SECRET_HERE';

    // -----------------------------------------------------------------------
    // phoss-SMP
    // -----------------------------------------------------------------------
    const SMP_BASE_URL   = 'https://smp.ethicfin.com';
    const SMP_ADMIN_USER = 'r&d@ethicfin.com';
    const SMP_ADMIN_PASS = '@1Direct';

    // -----------------------------------------------------------------------
    // Our AP / ASP identity
    // -----------------------------------------------------------------------
    const AP_ENDPOINT          = 'https://as4.ethicfin.com/as4';
    const ASP_NAME             = 'Ethicfin';
    const ASP_ACCREDITATION_NO = 'UAE_ACCREDITATION_NUMBER';
    const UAE_VAT_SCHEME       = '0235';
    const PEPPOL_ACTOR_SCHEME  = 'iso6523-actorid-upis';

    // -----------------------------------------------------------------------
    // Email — Brevo API
    // -----------------------------------------------------------------------
    const FROM_EMAIL    = 'noreply@ethicfin.com';
    const FROM_NAME     = 'Ethicfin PEPPOL e-Invoicing';
    // Loaded from .env: brevo.apiKey

    // AP public certificate (BASE64 DER)
    const AP_CERT_BASE64 = 'MIIFtDCCA5ygAwIBAgIUIZADkyW0hedKq3uFB3jvadTeLW8wDQYJKoZIhvcNAQELBQAwazELMAkGA1UEBhMCQkUxGTAXBgNVBAoTEE9wZW5QRVBQT0wgQUlTQkwxFjAUBgNVBAsTDUZPUiBURVNUIE9OTFkxKTAnBgNVBAMTIFBFUFBPTCBBQ0NFU1MgUE9JTlQgVEVTVCBDQSAtIEczMB4XDTI2MDYyMjAwMDAwMFoXDTI4MDYxMDIzNTk1OVowYjELMAkGA1UEBhMCSU4xJjAkBgNVBAoMHUV0aGljcHJvIEludGVsbGlnZW5jZSBQdnQgTHRkMRcwFQYDVQQLDA5QRVBQT0wgVEVTVCBBUDESMBAGA1UEAwwJUE9QMDAxMTcwMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApvv8H2DRpnjT1Od0BRMosYzINboeNEmXnDoPI1uAziDpRFW1xjHfAZXvHx1brhHCLiYM5+aOFvVcFKNnveZZbJqgH97lgIgF9YLqnT/YCDbd/32KUfM1CVb7AOn0Fy57+AWuuqebkqPeY2mS3ddeHnGP1W8ScXrdxvS4QqHRIZDljFXqJX2KE8WC57OGMpNqpbhVcvyc9O1MXQQCFmGLd1zSYk7hoAl6GjJosHmqe+cf4ywh/7JH8FbO/DpsSoF14qhZ5sPzS+HTsIpiXXvwfQXpe1zBrTghqHbxANJnrfRsimtKr5QHF1Wu507euk35r7EM8v+1qyR46ReKQdmX3wIDAQABo4IBVzCCAVMwDgYDVR0PAQH/BAQDAgSwMBYGA1UdJQEB/wQMMAoGCCsGAQUFBwMCMB0GA1UdDgQWBBQwQYjtts6yblq65GpxtNhOeB/LuzAfBgNVHSMEGDAWgBSzzETvdq+Byd/zX6WeiHGtn6D3cDAMBgNVHRMBAf8EAjAAMIGKBggrBgEFBQcBAQR+MHwwKwYIKwYBBQUHMAGGH2h0dHA6Ly9vY3NwLm9uZS5ubC5kaWdpY2VydC5jb20wTQYIKwYBBQUHMAKGQWh0dHA6Ly9jYWNlcnRzLm9uZS5ubC5kaWdpY2VydC5jb20vUEVQUE9MQUNDRVNTUE9JTlRURVNUQ0EtRzMuY3J0ME4GA1UdHwRHMEUwQ6BBoD+GPWh0dHA6Ly9jcmwub25lLm5sLmRpZ2ljZXJ0LmNvbS9QRVBQT0xBQ0NFU1NQT0lOVFRFU1RDQS1HMy5jcmwwDQYJKoZIhvcNAQELBQADggIBABEZpfFlITvezuqqBhE0xxFnCEoVA1o7QiHwGSIOUF4rzq741SFNezKP0pzhLuKuPXFbqNp6w1DOh7gGuSW+IELMglfXqICpYPnPQNV3XJ9GmJCorUp8LzLIV1xrds5D1r+SavpSJyAVLLSsCim0e+hZF41jYJE1BOQKLPXq9dSbvtor1sb/JRHH0PkyVEGiTSUclC0/h+XPeuheDkr7sgHhx+M3Jw9ei2YQ/qrz2VxgdpNgT+FAWOxLytS9M66siRoW7+Tnfbjakas/xaWatc7PVhzYP2dCTgphCHdy1u5aC0qDFreOWdwC1/9TtwQrRKXe+9udlLKmpg9GVBh1aicnig0eC1fN0jQALaDsqgTlO9atySBZFT+pZndHRgBBfTfnil596jfVFzgbB/YK5MNwTJoWeKoQDoI3euoX//USyiFO8sOHprAQBLgYS07vjDWjdih9VFHSh6pJO8PsQLApkLaYNAL8qvi4x8YjjxgMWn1iGJFoYs2IMuOoyuPRaAx1+o9/iQjLC4kXMuWNw0QFHkecRAvALNFrP/LC1pdhqAgQ2HEJzptH9e/EF9bfuhdw+alnVT4OsyeMc8l9nMpPiPHOz9k41I41165gitbsBQ24S56dKBvnj+0Stq2stk1i5Ker10Su14gpKJzLf4wc3kkSG+ov6INR2n9Epmqr';

    const DOC_TYPES = [
        [
            'scheme'  => 'peppol-doctype-wildcard',
            'id'      => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2::Invoice##urn:peppol:pint:billing-1@ae-1*::2.1',
            'process' => 'urn:peppol:bis:billing',
            'desc'    => 'PINT AE Invoice',
        ],
        [
            'scheme'  => 'peppol-doctype-wildcard',
            'id'      => 'urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2::CreditNote##urn:peppol:pint:billing-1@ae-1*::2.1',
            'process' => 'urn:peppol:bis:billing',
            'desc'    => 'PINT AE Credit Note',
        ],
        [
            'scheme'  => 'busdox-docid-qns',
            'id'      => 'urn:oasis:names:specification:ubl:schema:xsd:ApplicationResponse-2::ApplicationResponse##urn:fdc:peppol.eu:poacc:trns:mlr:3::2.1',
            'process' => 'urn:fdc:peppol.eu:poacc:bis:mlr:3',
            'desc'    => 'Message Level Response (MLS)',
        ],
    ];

    private PeppolParticipantModel $peppolModel;

    public function __construct()
    {
        $this->peppolModel = new PeppolParticipantModel();
    }

    // -----------------------------------------------------------------------
    // GET /uae/onboard?authcode=XXX          (FTA redirect — preferred)
    // GET /uae/onboard?emarataxToken=XXX     (legacy)
    // GET /uae/onboard?emarataxToken=XXX     (legacy)
    // GET /uae/onboard?tin=XXX&authcode=XXX
    // GET /uae/onboard?tin=XXX&authcode=XXX
    // -----------------------------------------------------------------------
    public function onboard()
    {
        // FTA sends the token as "authcode"; keep emarataxToken as fallback
        $emarataxToken = $this->request->getGet('authcode')
                      ?? $this->request->getGet('emarataxToken')
                      ?? '';
        $tin           = $this->request->getGet('TIN') ?? '';

        if (empty($emarataxToken)) {
            return view('uae/onboard', ['error' => 'Missing required parameter (authcode).']);
        }

        $mode = empty($tin) ? 'onboard' : 'reverify';

        return view('uae/onboard', [
            'mode'          => $mode,
            'emarataxToken' => $emarataxToken,
            'tin'           => $tin,
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /uae/ajax-verify  (AJAX)
    // -----------------------------------------------------------------------
    public function ajaxVerify()
    {
        $raw           = json_decode($this->request->getBody(), true) ?? [];
        $tin           = trim($raw['tin']           ?? '');
        $email         = trim($raw['email']         ?? '');
        $mobile        = trim($raw['mobile']        ?? '');
        $emarataxToken = trim($raw['emarataxToken'] ?? '');

        if (empty($emarataxToken) || empty($email) || empty($tin)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'TIN, Email and Token are required.',
            ]);
        }

        $result = $this->verifyTaxpayer($tin, $email, $mobile, $emarataxToken);

        if (!$result['success']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $result['message'],
            ]);
        }

        $d = $result['data'];
        return $this->response->setJSON([
            'success' => true,
            'data'    => [
                'TIN'            => $d['TIN']           ?? $tin,
                'legalType'      => $d['legalType']     ?? '',
                'entityNameEn'   => $d['entityNameEn']  ?? '',
                'entityNameAr'   => $d['entityNameAr']  ?? '',
                'vatTrn'         => $d['vatTrn']        ?? '',
                'effectiveDate'  => $d['effectiveDate'] ?? '',
                'submissionDate' => date('Y-m-d'),
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /uae/confirm-onboard
    // -----------------------------------------------------------------------
    public function confirmOnboard()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to(base_url('uae/onboard'));
        }

        $emarataxToken = $this->request->getPost('emarataxToken');
        $email         = $this->request->getPost('email');
        $mobile        = $this->request->getPost('mobile');
        $tin           = $this->request->getPost('tin');
        $vatTrn        = $this->request->getPost('vat_trn');
        $entityNameEn  = $this->request->getPost('entity_name_en');
        $entityNameAr  = $this->request->getPost('entity_name_ar');
        $legalType     = $this->request->getPost('legal_type');
        $effectiveDate = $this->request->getPost('effective_date');

        if (empty($tin) || empty($vatTrn)) {
            return view('uae/onboard', ['error' => 'Invalid submission. Please go back and try again.']);
        }

        $peppolId = self::UAE_VAT_SCHEME . ':' . $vatTrn;

        // Already registered?
        $existing = $this->peppolModel->getByTin($tin);
        if ($existing && $existing['status'] === 'active') {
            return view('uae/success', [
                'action'         => 'onboard',
                'already_linked' => true,
                'entity_name_en' => $existing['entity_name_en'],
                'peppol_id'      => $existing['peppol_id'],
                'vat_trn'        => $existing['vat_trn'],
                'email'          => $email,
            ]);
        }

        // 1. Register in SMP
        $smpResult = $this->registerSmpParticipant($vatTrn);
        if (!$smpResult['success']) {
            return view('uae/onboard', ['error' => 'SMP registration failed: ' . $smpResult['error']]);
        }

        // 2. Register document type endpoints
        foreach (self::DOC_TYPES as $docType) {
            $this->registerSmpEndpoint($vatTrn, $docType);
        }

        // 3. Notify FTA
        $crr = $this->crregupdate([
            'TIN'                   => $tin,
            'legalType'             => $legalType,
            'entityNameEn'          => $entityNameEn,
            'entityNameAr'          => $entityNameAr,
            'vatTrn'                => $vatTrn,
            'effectiveDate'         => $effectiveDate,
            'emarataxToken'         => $emarataxToken,
            'peppolParticipantId'   => self::PEPPOL_ACTOR_SCHEME . '::' . $peppolId,
            'eventDate'             => date('Y-m-d'),
            'aspName'               => self::ASP_NAME,
            'aspAccrediationNumber' => self::ASP_ACCREDITATION_NO,
            'reason'                => 'New PEPPOL e-Invoicing registration',
            'action'                => 1,
        ]);

        // 4. Save to DB
        $this->peppolModel->saveParticipant([
            'tin'            => $tin,
            'vat_trn'        => $vatTrn,
            'peppol_id'      => $peppolId,
            'entity_name_en' => $entityNameEn,
            'entity_name_ar' => $entityNameAr,
            'legal_type'     => $legalType,
            'effective_date' => $effectiveDate ?: null,
            'email'          => $email,
            'mobile'         => $mobile,
            'emaratax_token' => $emarataxToken,
            'status'         => 'active',
            'linked_at'      => date('Y-m-d H:i:s'),
            'fta_response'   => json_encode($crr['response'] ?? []),
        ]);

        // 5. Send confirmation email
        $this->sendConfirmationEmail($email, $entityNameEn, $vatTrn, $peppolId, 'onboard');

        return view('uae/success', [
            'action'         => 'onboard',
            'already_linked' => false,
            'entity_name_en' => $entityNameEn,
            'peppol_id'      => $peppolId,
            'vat_trn'        => $vatTrn,
            'email'          => $email,
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /uae/confirm-reverify-delink
    // -----------------------------------------------------------------------
    public function confirmReverifyDelink()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to(base_url('uae/onboard'));
        }

        $action        = $this->request->getPost('selected_action'); // 'reverify' or 'delink'
        $emarataxToken = $this->request->getPost('emarataxToken');
        $email         = $this->request->getPost('email');
        $mobile        = $this->request->getPost('mobile');
        $tin           = $this->request->getPost('tin');
        $vatTrn        = $this->request->getPost('vat_trn');
        $entityNameEn  = $this->request->getPost('entity_name_en');
        $entityNameAr  = $this->request->getPost('entity_name_ar');
        $legalType     = $this->request->getPost('legal_type');
        $effectiveDate = $this->request->getPost('effective_date');

        if (empty($tin) || empty($vatTrn) || !in_array($action, ['reverify', 'delink'])) {
            return view('uae/onboard', ['error' => 'Invalid submission. Please go back and try again.']);
        }

        $peppolId = self::UAE_VAT_SCHEME . ':' . $vatTrn;

        if ($action === 'delink') {
            $this->deregisterSmpParticipant($vatTrn);

            $crr = $this->crregupdate([
                'TIN'                   => $tin,
                'legalType'             => $legalType,
                'entityNameEn'          => $entityNameEn,
                'entityNameAr'          => $entityNameAr,
                'vatTrn'                => $vatTrn,
                'effectiveDate'         => $effectiveDate,
                'emarataxToken'         => $emarataxToken,
                'peppolParticipantId'   => self::PEPPOL_ACTOR_SCHEME . '::' . $peppolId,
                'eventDate'             => date('Y-m-d'),
                'aspName'               => self::ASP_NAME,
                'aspAccrediationNumber' => self::ASP_ACCREDITATION_NO,
                'reason'                => 'Customer requested PEPPOL deregistration',
                'action'                => 3, // 3 = Remove (delete)
            ]);

            $this->peppolModel->delinkParticipant($tin, date('Y-m-d H:i:s'));
            $this->sendConfirmationEmail($email, $entityNameEn, $vatTrn, $peppolId, 'delink');

            return view('uae/success', [
                'action'         => 'delink',
                'entity_name_en' => $entityNameEn,
                'peppol_id'      => $peppolId,
                'vat_trn'        => $vatTrn,
                'email'          => $email,
            ]);

        } else {
            // Reverify
            $existing = $this->peppolModel->getByTin($tin);
            if (!$existing || $existing['status'] !== 'active') {
                $this->registerSmpParticipant($vatTrn);
                foreach (self::DOC_TYPES as $docType) {
                    $this->registerSmpEndpoint($vatTrn, $docType);
                }
            }

            $crr = $this->crregupdate([
                'TIN'                   => $tin,
                'legalType'             => $legalType,
                'entityNameEn'          => $entityNameEn,
                'entityNameAr'          => $entityNameAr,
                'vatTrn'                => $vatTrn,
                'effectiveDate'         => $effectiveDate,
                'emarataxToken'         => $emarataxToken,
                'peppolParticipantId'   => self::PEPPOL_ACTOR_SCHEME . '::' . $peppolId,
                'eventDate'             => date('Y-m-d'),
                'aspName'               => self::ASP_NAME,
                'aspAccrediationNumber' => self::ASP_ACCREDITATION_NO,
                'reason'                => 'PEPPOL e-Invoicing re-verification',
                'action'                => 2, // 2 = Update (reverify)
            ]);

            $this->peppolModel->saveParticipant([
                'tin'            => $tin,
                'vat_trn'        => $vatTrn,
                'peppol_id'      => $peppolId,
                'entity_name_en' => $entityNameEn,
                'entity_name_ar' => $entityNameAr,
                'legal_type'     => $legalType,
                'effective_date' => $effectiveDate ?: null,
                'email'          => $email,
                'mobile'         => $mobile,
                'emaratax_token' => $emarataxToken,
                'status'         => 'active',
                'linked_at'      => date('Y-m-d H:i:s'),
                'fta_response'   => json_encode($crr['response'] ?? []),
            ]);

            $this->sendConfirmationEmail($email, $entityNameEn, $vatTrn, $peppolId, 'reverify');

            return view('uae/success', [
                'action'         => 'reverify',
                'entity_name_en' => $entityNameEn,
                'peppol_id'      => $peppolId,
                'vat_trn'        => $vatTrn,
                'email'          => $email,
            ]);
        }
    }

    // -----------------------------------------------------------------------
    // Private: Verify taxpayer with FTA
    // -----------------------------------------------------------------------
    private function verifyTaxpayer(string $tin, string $email, string $mobile, string $emarataxToken): array
    {
        if (self::TEST_MODE) {
            return [
                'success' => true,
                'data'    => [
                    'TIN'                => $tin,
                    'verificationStatus' => 'True',
                    'legalType'          => 'Free Zone LLC',
                    'entityNameEn'       => 'EF Intelligence L.L.C-FZ',
                    'entityNameAr'       => 'إي إف إنتيليجنس ش.ذ.م.م',
                    'vatTrn'             => $tin,
                    'effectiveDate'      => '2024-01-01',
                ],
            ];
        }

        $gsbToken  = $this->getGsbBearerToken();
        $ftaxToken = $this->getFtaxBearerToken();

        if (!$gsbToken) {
            return ['success' => false, 'message' => 'Failed to obtain GSB authorization token. Please try again.'];
        }
        if (!$ftaxToken) {
            return ['success' => false, 'message' => 'Failed to obtain FTAX authorization token. Please try again.'];
        }

        $url     = self::FTA_BASE_URL . '/api/prc/ects-einvoicing/v1/verifyenduser';
        $body    = json_encode(['TIN' => $tin, 'email' => $email, 'mobile' => $mobile, 'emarataxToken' => $emarataxToken]);
        $headers = [
            'GW-APIKey: '     . self::GSB_API_KEY,
            'Authorization: Bearer ' . $gsbToken,
            'CustomAuth: Bearer '    . $ftaxToken,
            'Content-Type: application/json',
        ];
        $result = $this->httpPost($url, $body, $headers);

        if (!$result['success']) {
            return ['success' => false, 'message' => 'Connection to FTA failed. Please try again.'];
        }
        if ($result['http_code'] === 401) {
            return ['success' => false, 'message' => 'Service temporarily unavailable. Please try again later.'];
        }
        if ($result['http_code'] !== 200) {
            $data = json_decode($result['body'], true);
            return ['success' => false, 'message' => $data['title'] ?? $data['message'] ?? 'Verification failed.'];
        }

        $data = json_decode($result['body'], true);
        if (!$data || ($data['verificationStatus'] ?? '') !== 'True') {
            return ['success' => false, 'message' => 'Could not verify your details. Please check your TIN and email address.'];
        }

        return ['success' => true, 'data' => $data];
    }

    // -----------------------------------------------------------------------
    // Private: Notify FTA crregupdate
    // -----------------------------------------------------------------------
    private function crregupdate(array $payload): array
    {
        if (self::TEST_MODE) {
            return ['success' => true, 'response' => ['status' => 'test_mode']];
        }

        $gsbToken  = $this->getGsbBearerToken();
        $ftaxToken = $this->getFtaxBearerToken();

        if (!$gsbToken || !$ftaxToken) {
            return ['success' => false, 'error' => 'Failed to obtain authorization tokens.'];
        }

        $url     = self::FTA_BASE_URL . '/api/prc/ects-einvoicing/v1/crregupdate';
        $body    = json_encode(array_merge($payload, ['field1' => '', 'field2' => '', 'field3' => '', 'field4' => '', 'field5' => '']));
        $headers = [
            'GW-APIKey: '            . self::GSB_API_KEY,
            'Authorization: Bearer ' . $gsbToken,
            'CustomAuth: Bearer '    . $ftaxToken,
            'Content-Type: application/json',
        ];
        $result = $this->httpPost($url, $body, $headers);

        if (!$result['success'] || $result['http_code'] !== 200) {
            return ['success' => false, 'error' => 'HTTP ' . $result['http_code'] . ': ' . $result['body']];
        }

        return ['success' => true, 'response' => json_decode($result['body'], true)];
    }

    // -----------------------------------------------------------------------
    // Private: Fetch GSB OAuth2 Bearer token (Authorization header)
    // -----------------------------------------------------------------------
    private function getGsbBearerToken(): ?string
    {
        $body   = http_build_query([
            'grant_type'    => 'client_credentials',
            'client_id'     => self::GSB_CLIENT_ID,
            'client_secret' => self::GSB_CLIENT_SECRET,
            'scope'         => self::GSB_SCOPE,
        ]);
        $result = $this->httpPost(self::GSB_TOKEN_URL, $body, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        if (!$result['success'] || $result['http_code'] !== 200) {
            return null;
        }

        $data = json_decode($result['body'], true);
        return $data['access_token'] ?? null;
    }

    // -----------------------------------------------------------------------
    // Private: Fetch FTAX Bearer token (CustomAuth header)
    // -----------------------------------------------------------------------
    private function getFtaxBearerToken(): ?string
    {
        $body   = http_build_query([
            'grant_type'    => 'client_credentials',
            'client_id'     => self::FTAX_CLIENT_ID,
            'client_secret' => self::FTAX_CLIENT_SECRET,
        ]);
        $result = $this->httpPost(self::FTAX_TOKEN_URL, $body, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        if (!$result['success'] || $result['http_code'] !== 200) {
            return null;
        }

        $data = json_decode($result['body'], true);
        return $data['access_token'] ?? null;
    }

    // -----------------------------------------------------------------------
    // Private: SMP participant registration
    // -----------------------------------------------------------------------
    private function registerSmpParticipant(string $vatTrn): array
    {
        $participantId = self::PEPPOL_ACTOR_SCHEME . '::' . self::UAE_VAT_SCHEME . ':' . $vatTrn;
        $url           = self::SMP_BASE_URL . '/' . rawurlencode($participantId);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
             . '<ServiceGroup xmlns="http://busdox.org/serviceMetadata/publishing/1.0/"'
             . ' xmlns:id="http://busdox.org/transport/identifiers/1.0/">'
             . '<id:ParticipantIdentifier scheme="' . self::PEPPOL_ACTOR_SCHEME . '">'
             . self::UAE_VAT_SCHEME . ':' . htmlspecialchars($vatTrn)
             . '</id:ParticipantIdentifier>'
             . '<ServiceMetadataReferenceCollection/>'
             . '</ServiceGroup>';

        $result = $this->httpPut($url, $xml, ['Content-Type: application/xml'], self::SMP_ADMIN_USER, self::SMP_ADMIN_PASS);

        if (!$result['success'] || !in_array($result['http_code'], [200, 201, 204, 409])) {
            return ['success' => false, 'error' => 'HTTP ' . $result['http_code'] . ': ' . $result['body']];
        }

        return ['success' => true];
    }

    // -----------------------------------------------------------------------
    // Private: SMP endpoint registration
    // -----------------------------------------------------------------------
    private function registerSmpEndpoint(string $vatTrn, array $docType): array
    {
        $participantId = self::PEPPOL_ACTOR_SCHEME . '::' . self::UAE_VAT_SCHEME . ':' . $vatTrn;
        $docTypeId     = $docType['scheme'] . '::' . $docType['id'];
        $url           = self::SMP_BASE_URL . '/' . rawurlencode($participantId) . '/services/' . rawurlencode($docTypeId);

        $today   = date('Y-m-d') . 'T00:00:00Z';
        $expires = '2099-12-31T00:00:00Z';

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
             . '<ServiceMetadata xmlns="http://busdox.org/serviceMetadata/publishing/1.0/"'
             . ' xmlns:id="http://busdox.org/transport/identifiers/1.0/">'
             . '<ServiceInformation>'
             . '<id:ParticipantIdentifier scheme="' . self::PEPPOL_ACTOR_SCHEME . '">'
             . self::UAE_VAT_SCHEME . ':' . htmlspecialchars($vatTrn)
             . '</id:ParticipantIdentifier>'
             . '<id:DocumentIdentifier scheme="' . htmlspecialchars($docType['scheme']) . '">'
             . htmlspecialchars($docType['id'])
             . '</id:DocumentIdentifier>'
             . '<ProcessList><Process>'
             . '<id:ProcessIdentifier scheme="cenbii-procid-ubl">'
             . htmlspecialchars($docType['process'])
             . '</id:ProcessIdentifier>'
             . '<ServiceEndpointList>'
             . '<Endpoint transportProfile="peppol-transport-as4-v2_0">'
             . '<wsa:EndpointReference xmlns:wsa="http://www.w3.org/2005/08/addressing">'
             . '<wsa:Address>' . self::AP_ENDPOINT . '</wsa:Address>'
             . '</wsa:EndpointReference>'
             . '<RequireBusinessLevelSignature>false</RequireBusinessLevelSignature>'
             . '<ServiceActivationDate>' . $today . '</ServiceActivationDate>'
             . '<ServiceExpirationDate>' . $expires . '</ServiceExpirationDate>'
             . '<Certificate>' . self::AP_CERT_BASE64 . '</Certificate>'
             . '<ServiceDescription>' . htmlspecialchars($docType['desc']) . '</ServiceDescription>'
             . '<TechnicalContactUrl>mailto:support@ethicfin.com</TechnicalContactUrl>'
             . '</Endpoint></ServiceEndpointList>'
             . '</Process></ProcessList>'
             . '</ServiceInformation>'
             . '</ServiceMetadata>';

        $result = $this->httpPut($url, $xml, ['Content-Type: application/xml'], self::SMP_ADMIN_USER, self::SMP_ADMIN_PASS);

        if (!$result['success'] || !in_array($result['http_code'], [200, 201, 204])) {
            return ['success' => false, 'error' => 'HTTP ' . $result['http_code'] . ': ' . $result['body']];
        }

        return ['success' => true];
    }

    // -----------------------------------------------------------------------
    // Private: SMP participant deregistration
    // -----------------------------------------------------------------------
    private function deregisterSmpParticipant(string $vatTrn): array
    {
        $participantId = self::PEPPOL_ACTOR_SCHEME . '::' . self::UAE_VAT_SCHEME . ':' . $vatTrn;
        $url           = self::SMP_BASE_URL . '/' . rawurlencode($participantId);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_USERPWD        => self::SMP_ADMIN_USER . ':' . self::SMP_ADMIN_PASS,
        ]);
        $body    = curl_exec($ch);
        $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $success = !curl_errno($ch);
        curl_close($ch);

        return ['success' => $success && in_array($code, [200, 204, 404])];
    }

    // -----------------------------------------------------------------------
    // Private: Send confirmation email via Brevo
    // -----------------------------------------------------------------------
    private function sendConfirmationEmail(string $to, string $entityNameEn, string $vatTrn, string $peppolId, string $action): void
    {
        $labels = [
            'onboard'  => ['subject' => 'PEPPOL e-Invoicing Registration Confirmed',    'heading' => 'Registration Successful',   'msg' => 'Your business has been successfully registered on the UAE PEPPOL e-Invoicing network.'],
            'reverify' => ['subject' => 'PEPPOL e-Invoicing Re-verification Confirmed',  'heading' => 'Re-verification Successful', 'msg' => 'Your PEPPOL e-Invoicing registration has been successfully re-verified.'],
            'delink'   => ['subject' => 'PEPPOL e-Invoicing Deregistration Confirmed',   'heading' => 'Deregistration Successful',  'msg' => 'Your business has been successfully deregistered from the UAE PEPPOL e-Invoicing network.'],
        ];

        $l = $labels[$action] ?? $labels['onboard'];

        $htmlBody = '
        <html><body style="font-family:Arial,sans-serif;color:#333;margin:0;padding:0;">
        <div style="max-width:600px;margin:30px auto;border:1px solid #ddd;border-radius:6px;overflow:hidden;">
            <div style="background:#1a3a6b;padding:24px 30px;">
                <h2 style="color:#fff;margin:0;font-size:20px;">Ethicfin PEPPOL e-Invoicing</h2>
                <p style="color:#aac4f0;margin:4px 0 0;font-size:13px;">Accredited Access Point – UAE</p>
            </div>
            <div style="padding:30px;background:#fff;">
                <h3 style="color:#1a3a6b;margin-top:0;">' . $l['heading'] . '</h3>
                <p>Dear <strong>' . htmlspecialchars($entityNameEn) . '</strong>,</p>
                <p>' . $l['msg'] . '</p>
                <table style="width:100%;border-collapse:collapse;margin:20px 0;">
                    <tr style="background:#eaf0fb;"><td style="padding:10px 14px;border:1px solid #d0ddef;font-weight:bold;width:40%;">Company Name</td><td style="padding:10px 14px;border:1px solid #d0ddef;">' . htmlspecialchars($entityNameEn) . '</td></tr>
                    <tr><td style="padding:10px 14px;border:1px solid #d0ddef;font-weight:bold;">VAT TRN</td><td style="padding:10px 14px;border:1px solid #d0ddef;">' . htmlspecialchars($vatTrn) . '</td></tr>
                    <tr style="background:#eaf0fb;"><td style="padding:10px 14px;border:1px solid #d0ddef;font-weight:bold;">PEPPOL Participant ID</td><td style="padding:10px 14px;border:1px solid #d0ddef;font-family:monospace;font-size:13px;">' . htmlspecialchars($peppolId) . '</td></tr>
                    <tr><td style="padding:10px 14px;border:1px solid #d0ddef;font-weight:bold;">Date</td><td style="padding:10px 14px;border:1px solid #d0ddef;">' . date('d M Y') . '</td></tr>
                </table>
                <p style="color:#555;font-size:13px;">For any queries, contact us at <a href="mailto:support@ethicfin.com" style="color:#1a3a6b;">support@ethicfin.com</a></p>
            </div>
            <div style="padding:14px 30px;background:#f4f6fa;text-align:center;font-size:12px;color:#999;">
                Ethicpro Intelligence Pvt Ltd &bull; Accredited PEPPOL Access Point &bull; UAE
            </div>
        </div>
        </body></html>';

        $payload = json_encode([
            'sender'      => ['name' => self::FROM_NAME, 'email' => self::FROM_EMAIL],
            'to'          => [['email' => $to]],
            'subject'     => $l['subject'],
            'htmlContent' => $htmlBody,
        ]);

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => ['api-key: ' . env('brevo.apiKey', ''), 'Content-Type: application/json', 'Accept: application/json'],
        ]);
        $code = curl_getinfo(curl_exec($ch) ? $ch : $ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }

    // -----------------------------------------------------------------------
    // Private: HTTP helpers
    // -----------------------------------------------------------------------
    private function httpPost(string $url, string $body, array $headers = [], ?string $user = null, ?string $pass = null): array
    {
        $ch   = curl_init($url);
        $opts = [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $body, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_HTTPHEADER => $headers, CURLOPT_SSL_VERIFYPEER => true];
        if ($user) $opts[CURLOPT_USERPWD] = $user . ':' . $pass;
        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);
        return ['success' => empty($err), 'http_code' => $code, 'body' => $response, 'error' => $err];
    }

    private function httpPut(string $url, string $body, array $headers = [], ?string $user = null, ?string $pass = null): array
    {
        $ch   = curl_init($url);
        $opts = [CURLOPT_CUSTOMREQUEST => 'PUT', CURLOPT_POSTFIELDS => $body, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_HTTPHEADER => $headers, CURLOPT_SSL_VERIFYPEER => true];
        if ($user) $opts[CURLOPT_USERPWD] = $user . ':' . $pass;
        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);
        return ['success' => empty($err), 'http_code' => $code, 'body' => $response, 'error' => $err];
    }
}
