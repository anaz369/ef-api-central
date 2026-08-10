<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PEPPOL e-Invoicing – Ethicfin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f0f2f5; padding-bottom: 60px; }
        .page-header { background:#fff; border-bottom:1px solid #dde3ed; padding:14px 0; }
        .header-logo-fta   { height: 44px; width: auto; }
        .header-logo-mof   { height: 56px; width: auto; }
        .logo-separator    { width: 1px; height: 48px; background: #d0d5dd; margin: 0 4px; }
        .page-footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #1a3a6b;
            padding: 10px 0;
            z-index: 100;
        }
        .page-footer img { height: 28px; width: auto; }
        .page-footer .footer-text { color: rgba(255,255,255,0.7); font-size: 12px; }
        .card { border:1px solid #d8e0ee; border-radius:6px; }
        .icon-circle {
            width:72px; height:72px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 16px;
        }
        .icon-success { background:#e6f4ea; color:#2e7d32; }
        .icon-delink  { background:#fff3e0; color:#c0430a; }
        .peppol-id-box {
            background:#eaf0fb; border:1px solid #c8d3e8;
            border-radius:4px; padding:10px 14px;
            font-family:monospace; font-size:14px; color:#1a3a6b; font-weight:700;
            word-break:break-all;
        }
        table td, table th { font-size:13.5px; }
    </style>
</head>
<body>

<div class="page-header">
    <div class="container d-flex align-items-center justify-content-between">
        <div></div>
        <div class="d-flex align-items-center gap-3">
            <img src="<?= base_url('assets/uae/logo-fta.webp') ?>" alt="FTA" class="header-logo-fta">
            <div class="logo-separator"></div>
            <img src="<?= base_url('assets/uae/mof_logo.jpg') ?>" alt="Ministry of Finance" class="header-logo-mof">
        </div>
    </div>
</div>

<div class="container py-5" style="max-width:640px;">
    <div class="card shadow-sm">
        <div class="card-body p-5 text-center">

            <?php
            $action        = $action        ?? 'onboard';
            $alreadyLinked = $already_linked ?? false;
            $isDelink      = ($action === 'delink');
            $isReverify    = ($action === 'reverify');
            ?>

            <div class="icon-circle <?= $isDelink ? 'icon-delink' : 'icon-success' ?> mx-auto mb-3">
                <?php if ($isDelink): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                    <line x1="4" y1="4" x2="20" y2="20"/>
                </svg>
                <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.8"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <?php endif; ?>
            </div>

            <?php if ($isDelink): ?>
                <h4 class="fw-bold mb-2" style="color:#e65100;">Deregistration Successful</h4>
                <p class="text-muted mb-4">Your business has been deregistered from the UAE PEPPOL e-Invoicing network.</p>
            <?php elseif ($isReverify): ?>
                <h4 class="fw-bold mb-2" style="color:#1a3a6b;">Re-verification Successful</h4>
                <p class="text-muted mb-4">Your PEPPOL e-Invoicing registration has been successfully re-verified.</p>
            <?php elseif ($alreadyLinked): ?>
                <h4 class="fw-bold mb-2" style="color:#1a3a6b;">Already Registered</h4>
                <p class="text-muted mb-4">This TIN is already linked to the PEPPOL network.</p>
            <?php else: ?>
                <h4 class="fw-bold mb-2" style="color:#1a3a6b;">Registration Successful</h4>
                <p class="text-muted mb-4">Your business is now registered on the UAE PEPPOL e-Invoicing network.</p>
            <?php endif; ?>

            <table class="table table-bordered text-start mb-4">
                <tr>
                    <th style="width:42%;background:#f5f7fb;">Company</th>
                    <td><?= htmlspecialchars($entity_name_en ?? '') ?></td>
                </tr>
                <tr>
                    <th style="background:#f5f7fb;">VAT TRN</th>
                    <td><?= htmlspecialchars($vat_trn ?? '') ?></td>
                </tr>
                <?php if (!$isDelink): ?>
                <tr>
                    <th style="background:#f5f7fb;">PEPPOL Participant ID</th>
                    <td><div class="peppol-id-box"><?= htmlspecialchars($peppol_id ?? '') ?></div></td>
                </tr>
                <tr>
                    <th style="background:#f5f7fb;">Access Point</th>
                    <td>Ethicpro Intelligence Pvt Ltd (Ethicfin)</td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th style="background:#f5f7fb;">Date</th>
                    <td><?= date('d M Y') ?></td>
                </tr>
            </table>

            <?php if (!$isDelink && !empty($email)): ?>
            <p class="text-muted small">
                A confirmation email has been sent to <strong><?= htmlspecialchars($email) ?></strong>
            </p>
            <?php endif; ?>

            <?php if (!$isDelink && !$alreadyLinked): ?>
            <div class="alert alert-light border text-start small mt-3">
                <strong>What's next?</strong><br>
                Your business can now send and receive e-invoices via the PEPPOL network.
                Trading partners can address invoices to your PEPPOL Participant ID above.
            </div>
            <?php endif; ?>

            <hr class="my-3">
            <p class="text-muted small mb-0">
                For support: <a href="mailto:support@ethicfin.com" style="color:#1a3a6b;">support@ethicfin.com</a>
            </p>
        </div>
    </div>
</div>

<!-- Fixed Footer -->
<div class="page-footer">
    <div class="container d-flex align-items-center justify-content-center gap-3" style="max-width:1320px;">
        <img src="<?= base_url('assets/uae/ethicfin.png') ?>" alt="Ethicfin">
        <div style="width:1px;height:24px;background:rgba(255,255,255,0.3);"></div>
        <span class="footer-text">Accredited PEPPOL Access Point &bull; UAE</span>
    </div>
</div>
</body>
</html>
