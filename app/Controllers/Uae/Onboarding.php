<?php

namespace App\Controllers\Uae;

use App\Controllers\BaseController;
use App\Models\PeppolParticipantModel;
use Config\Ftax;

class Onboarding extends BaseController
{
    private PeppolParticipantModel $peppolModel;
    private Ftax $cfg;

    public function __construct()
    {
        $this->peppolModel = new PeppolParticipantModel();
        $this->cfg = config('Ftax');
    }

    // -----------------------------------------------------------------------
    // GET /uae/onboard?authcode=XXX          (FTA redirect — preferred)
    // GET /uae/onboard?emarataxToken=XXX     (legacy)
    // GET /uae/onboard?tin=XXX&authcode=XXX
    // -----------------------------------------------------------------------
    public function onboard()
    {
        return $this->_renderOnboard('en');
    }

    // -----------------------------------------------------------------------
    // GET /uae/onboard-ar?authcode=XXX       (Arabic version)
    // -----------------------------------------------------------------------
    public function onboardAr()
    {
        return $this->_renderOnboard('ar');
    }

    private function _renderOnboard(string $locale)
    {
        $isAr  = $locale === 'ar';
        $viewName = $isAr ? 'uae/onboard_ar' : 'uae/onboard';

        // FTA sends the token as "authcode"; keep emarataxToken as fallback
        $emarataxToken = $this->request->getGet('authcode')
                      ?? $this->request->getGet('emarataxToken')
                      ?? '';
        $tin           = $this->request->getGet('TIN') ?? '';

        if (empty($emarataxToken)) {
            $errMsg = $isAr ? 'معامل مطلوب مفقود (authcode).' : 'Missing required parameter (authcode).';
            return view($viewName, ['error' => $errMsg]);
        }

        $mode = empty($tin) ? 'onboard' : 'reverify';

        return view($viewName, [
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

        $locale        = $this->request->getPost('locale') === 'ar' ? 'ar' : 'en';
        $isAr          = $locale === 'ar';
        $successView   = $isAr ? 'uae/success_ar' : 'uae/success';
        $onboardView   = $isAr ? 'uae/onboard_ar' : 'uae/onboard';

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
            $errMsg = $isAr ? 'طلب غير صالح. يرجى العودة والمحاولة مجدداً.' : 'Invalid submission. Please go back and try again.';
            return view($onboardView, ['error' => $errMsg]);
        }

        $peppolId = $this->cfg->uaeVatScheme . ':' . $vatTrn;

        // Already registered?
        $existing = $this->peppolModel->getByTin($tin);
        if ($existing && $existing['status'] === 'active') {
            return view($successView, [
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
            $errMsg = $isAr ? 'فشل تسجيل SMP: ' . $smpResult['error'] : 'SMP registration failed: ' . $smpResult['error'];
            return view($onboardView, ['error' => $errMsg]);
        }

        // 2. Register document type endpoints
        foreach ($this->cfg->docTypes as $docType) {
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
            'peppolParticipantId'   => $this->cfg->peppolActorScheme . '::' . $peppolId,
            'eventDate'             => date('Y-m-d'),
            'aspName'               => $this->cfg->aspName,
            'aspAccrediationNumber' => $this->cfg->aspAccreditationNo,
            'reason'                => 'New PEPPOL e-Invoicing registration',
            'action'                => 1, // 1 = Register (add)
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

        return view($successView, [
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

        $locale        = $this->request->getPost('locale') === 'ar' ? 'ar' : 'en';
        $isAr          = $locale === 'ar';
        $successView   = $isAr ? 'uae/success_ar' : 'uae/success';
        $onboardView   = $isAr ? 'uae/onboard_ar' : 'uae/onboard';

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
            $errMsg = $isAr ? 'طلب غير صالح. يرجى العودة والمحاولة مجدداً.' : 'Invalid submission. Please go back and try again.';
            return view($onboardView, ['error' => $errMsg]);
        }

        $peppolId = $this->cfg->uaeVatScheme . ':' . $vatTrn;

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
                'peppolParticipantId'   => $this->cfg->peppolActorScheme . '::' . $peppolId,
                'eventDate'             => date('Y-m-d'),
                'aspName'               => $this->cfg->aspName,
                'aspAccrediationNumber' => $this->cfg->aspAccreditationNo,
                'reason'                => 'Customer requested PEPPOL deregistration',
                'action'                => 3, // 3 = Remove (delete)
            ]);

            $this->peppolModel->delinkParticipant($tin, date('Y-m-d H:i:s'));
            $this->sendConfirmationEmail($email, $entityNameEn, $vatTrn, $peppolId, 'delink');

            return view($successView, [
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
                foreach ($this->cfg->docTypes as $docType) {
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
                'peppolParticipantId'   => $this->cfg->peppolActorScheme . '::' . $peppolId,
                'eventDate'             => date('Y-m-d'),
                'aspName'               => $this->cfg->aspName,
                'aspAccrediationNumber' => $this->cfg->aspAccreditationNo,
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

            return view($successView, [
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
        if ($this->cfg->testMode) {
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

        $url     = $this->cfg->ftaBaseUrl . '/api/prc/ects-einvoicing/v1/verifyenduser';
        $body    = json_encode(['TIN' => $tin, 'email' => $email, 'mobile' => $mobile, 'emarataxToken' => $emarataxToken]);
        $headers = [
            'GW-APIKey: '            . $this->cfg->gsbApiKey,
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
        if ($this->cfg->testMode) {
            return ['success' => true, 'response' => ['status' => 'test_mode']];
        }

        $gsbToken  = $this->getGsbBearerToken();
        $ftaxToken = $this->getFtaxBearerToken();

        if (!$gsbToken || !$ftaxToken) {
            return ['success' => false, 'error' => 'Failed to obtain authorization tokens.'];
        }

        $url     = $this->cfg->ftaBaseUrl . '/api/prc/ects-einvoicing/v1/crregupdate';
        $body    = json_encode(array_merge($payload, ['field1' => '', 'field2' => '', 'field3' => '', 'field4' => '', 'field5' => '']));
        $headers = [
            'GW-APIKey: '            . $this->cfg->gsbApiKey,
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
            'client_id'     => $this->cfg->gsbClientId,
            'client_secret' => $this->cfg->gsbClientSecret,
            'scope'         => $this->cfg->gsbScope,
        ]);
        $result = $this->httpPost($this->cfg->gsbTokenUrl, $body, [
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
            'client_id'     => $this->cfg->ftaxClientId,
            'client_secret' => $this->cfg->ftaxClientSecret,
        ]);
        $result = $this->httpPost($this->cfg->ftaxTokenUrl, $body, [
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
        $participantId = $this->cfg->peppolActorScheme . '::' . $this->cfg->uaeVatScheme . ':' . $vatTrn;
        $url           = $this->cfg->smpBaseUrl . '/' . rawurlencode($participantId);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
             . '<ServiceGroup xmlns="http://busdox.org/serviceMetadata/publishing/1.0/"'
             . ' xmlns:id="http://busdox.org/transport/identifiers/1.0/">'
             . '<id:ParticipantIdentifier scheme="' . $this->cfg->peppolActorScheme . '">'
             . $this->cfg->uaeVatScheme . ':' . htmlspecialchars($vatTrn)
             . '</id:ParticipantIdentifier>'
             . '<ServiceMetadataReferenceCollection/>'
             . '</ServiceGroup>';

        $result = $this->httpPut($url, $xml, ['Content-Type: application/xml'], $this->cfg->smpAdminUser, $this->cfg->smpAdminPass);

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
        $participantId = $this->cfg->peppolActorScheme . '::' . $this->cfg->uaeVatScheme . ':' . $vatTrn;
        $docTypeId     = $docType['scheme'] . '::' . $docType['id'];
        $url           = $this->cfg->smpBaseUrl . '/' . rawurlencode($participantId) . '/services/' . rawurlencode($docTypeId);

        $today   = date('Y-m-d') . 'T00:00:00Z';
        $expires = '2099-12-31T00:00:00Z';

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
             . '<ServiceMetadata xmlns="http://busdox.org/serviceMetadata/publishing/1.0/"'
             . ' xmlns:id="http://busdox.org/transport/identifiers/1.0/">'
             . '<ServiceInformation>'
             . '<id:ParticipantIdentifier scheme="' . $this->cfg->peppolActorScheme . '">'
             . $this->cfg->uaeVatScheme . ':' . htmlspecialchars($vatTrn)
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
             . '<wsa:Address>' . $this->cfg->apEndpoint . '</wsa:Address>'
             . '</wsa:EndpointReference>'
             . '<RequireBusinessLevelSignature>false</RequireBusinessLevelSignature>'
             . '<ServiceActivationDate>' . $today . '</ServiceActivationDate>'
             . '<ServiceExpirationDate>' . $expires . '</ServiceExpirationDate>'
             . '<Certificate>' . $this->cfg->apCertBase64 . '</Certificate>'
             . '<ServiceDescription>' . htmlspecialchars($docType['desc']) . '</ServiceDescription>'
             . '<TechnicalContactUrl>mailto:support@ethicfin.com</TechnicalContactUrl>'
             . '</Endpoint></ServiceEndpointList>'
             . '</Process></ProcessList>'
             . '</ServiceInformation>'
             . '</ServiceMetadata>';

        $result = $this->httpPut($url, $xml, ['Content-Type: application/xml'], $this->cfg->smpAdminUser, $this->cfg->smpAdminPass);

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
        $participantId = $this->cfg->peppolActorScheme . '::' . $this->cfg->uaeVatScheme . ':' . $vatTrn;
        $url           = $this->cfg->smpBaseUrl . '/' . rawurlencode($participantId);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_USERPWD        => $this->cfg->smpAdminUser . ':' . $this->cfg->smpAdminPass,
        ]);
        $success = !curl_errno($ch) && in_array(curl_getinfo($ch, CURLINFO_HTTP_CODE), [200, 204, 404]);
        curl_close($ch);

        return ['success' => $success];
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
            'sender'      => ['name' => $this->cfg->fromName, 'email' => $this->cfg->fromEmail],
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
        curl_exec($ch);
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
