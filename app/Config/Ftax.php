<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * FTA / GSB onboarding configuration.
 *
 * Non-secret values are set here as defaults.
 * Secret values default to '' and are overridden via .env:
 *   ftax.gsbApiKey = YOUR_KEY
 */
class Ftax extends BaseConfig
{
    // -----------------------------------------------------------------------
    // Test mode — override in .env: ftax.testMode = false
    // -----------------------------------------------------------------------
    public bool $testMode = true;

    // -----------------------------------------------------------------------
    // FTA / GSB API — base URLs (non-secret)
    // -----------------------------------------------------------------------
    public string $ftaBaseUrl   = 'https://api.gsb.government.ae/gateway/validateTaxPayerDetails_FTAX/1.0';
    public string $gsbTokenUrl  = 'https://api.gsb.government.ae/invoke/pub.apigateway.oauth2/getAccessToken';
    public string $gsbScope     = '9b9346b3-5b18-11f0-a374-856a43324051';

    // -----------------------------------------------------------------------
    // Secrets — set via .env, never commit real values
    // -----------------------------------------------------------------------

    // Header 1: GW-APIKey (issued via UAE API Marketplace)
    public string $gsbApiKey = '';

    // Header 2: Authorization OAuth2 credentials
    public string $gsbClientId     = '';
    public string $gsbClientSecret = '';

    // Header 3: CustomAuth — FTAX token endpoint + credentials
    public string $ftaxTokenUrl     = '';
    public string $ftaxClientId     = '';
    public string $ftaxClientSecret = '';

    // SMP admin credentials
    public string $smpAdminUser = '';
    public string $smpAdminPass = '';

    // -----------------------------------------------------------------------
    // SMP / Peppol identity (non-secret, environment-specific)
    // -----------------------------------------------------------------------
    public string $smpBaseUrl        = 'https://smp.ethicfin.com';
    public string $apEndpoint        = 'https://as4.ethicfin.com/as4';
    public string $aspName           = 'Ethicfin';
    public string $aspAccreditationNo = 'UAE_ACCREDITATION_NUMBER'; // override in .env when confirmed
    public string $uaeVatScheme      = '0235';
    public string $peppolActorScheme = 'iso6523-actorid-upis';

    // -----------------------------------------------------------------------
    // Email (non-secret)
    // -----------------------------------------------------------------------
    public string $fromEmail = 'noreply@ethicfin.com';
    public string $fromName  = 'Ethicfin PEPPOL e-Invoicing';

    // -----------------------------------------------------------------------
    // AP public certificate BASE64-DER (non-secret — public cert)
    // -----------------------------------------------------------------------
    public string $apCertBase64 = 'MIIFtDCCA5ygAwIBAgIUIZADkyW0hedKq3uFB3jvadTeLW8wDQYJKoZIhvcNAQELBQAwazELMAkGA1UEBhMCQkUxGTAXBgNVBAoTEE9wZW5QRVBQT0wgQUlTQkwxFjAUBgNVBAsTDUZPUiBURVNUIE9OTFkxKTAnBgNVBAMTIFBFUFBPTCBBQ0NFU1MgUE9JTlQgVEVTVCBDQSAtIEczMB4XDTI2MDYyMjAwMDAwMFoXDTI4MDYxMDIzNTk1OVowYjELMAkGA1UEBhMCSU4xJjAkBgNVBAoMHUV0aGljcHJvIEludGVsbGlnZW5jZSBQdnQgTHRkMRcwFQYDVQQLDA5QRVBQT0wgVEVTVCBBUDESMBAGA1UEAwwJUE9QMDAxMTcwMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApvv8H2DRpnjT1Od0BRMosYzINboeNEmXnDoPI1uAziDpRFW1xjHfAZXvHx1brhHCLiYM5+aOFvVcFKNnveZZbJqgH97lgIgF9YLqnT/YCDbd/32KUfM1CVb7AOn0Fy57+AWuuqebkqPeY2mS3ddeHnGP1W8ScXrdxvS4QqHRIZDljFXqJX2KE8WC57OGMpNqpbhVcvyc9O1MXQQCFmGLd1zSYk7hoAl6GjJosHmqe+cf4ywh/7JH8FbO/DpsSoF14qhZ5sPzS+HTsIpiXXvwfQXpe1zBrTghqHbxANJnrfRsimtKr5QHF1Wu507euk35r7EM8v+1qyR46ReKQdmX3wIDAQABo4IBVzCCAVMwDgYDVR0PAQH/BAQDAgSwMBYGA1UdJQEB/wQMMAoGCCsGAQUFBwMCMB0GA1UdDgQWBBQwQYjtts6yblq65GpxtNhOeB/LuzAfBgNVHSMEGDAWgBSzzETvdq+Byd/zX6WeiHGtn6D3cDAMBgNVHRMBAf8EAjAAMIGKBggrBgEFBQcBAQR+MHwwKwYIKwYBBQUHMAGGH2h0dHA6Ly9vY3NwLm9uZS5ubC5kaWdpY2VydC5jb20wTQYIKwYBBQUHMAKGQWh0dHA6Ly9jYWNlcnRzLm9uZS5ubC5kaWdpY2VydC5jb20vUEVQUE9MQUNDRVNTUE9JTlRURVNUQ0EtRzMuY3J0ME4GA1UdHwRHMEUwQ6BBoD+GPWh0dHA6Ly9jcmwub25lLm5sLmRpZ2ljZXJ0LmNvbS9QRVBQT0xBQ0NFU1NQT0lOVFRFU1RDQS1HMy5jcmwwDQYJKoZIhvcNAQELBQADggIBABEZpfFlITvezuqqBhE0xxFnCEoVA1o7QiHwGSIOUF4rzq741SFNezKP0pzhLuKuPXFbqNp6w1DOh7gGuSW+IELMglfXqICpYPnPQNV3XJ9GmJCorUp8LzLIV1xrds5D1r+SavpSJyAVLLSsCim0e+hZF41jYJE1BOQKLPXq9dSbvtor1sb/JRHH0PkyVEGiTSUclC0/h+XPeuheDkr7sgHhx+M3Jw9ei2YQ/qrz2VxgdpNgT+FAWOxLytS9M66siRoW7+Tnfbjakas/xaWatc7PVhzYP2dCTgphCHdy1u5aC0qDFreOWdwC1/9TtwQrRKXe+9udlLKmpg9GVBh1aicnig0eC1fN0jQALaDsqgTlO9atySBZFT+pZndHRgBBfTfnil596jfVFzgbB/YK5MNwTJoWeKoQDoI3euoX//USyiFO8sOHprAQBLgYS07vjDWjdih9VFHSh6pJO8PsQLApkLaYNAL8qvi4x8YjjxgMWn1iGJFoYs2IMuOoyuPRaAx1+o9/iQjLC4kXMuWNw0QFHkecRAvALNFrP/LC1pdhqAgQ2HEJzptH9e/EF9bfuhdw+alnVT4OsyeMc8l9nMpPiPHOz9k41I41165gitbsBQ24S56dKBvnj+0Stq2stk1i5Ker10Su14gpKJzLf4wc3kkSG+ov6INR2n9Epmqr';

    // -----------------------------------------------------------------------
    // Peppol document types registered on SMP (non-secret)
    // Arrays cannot be set via .env — change here if needed
    // -----------------------------------------------------------------------
    public array $docTypes = [
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
}
