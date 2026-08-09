<div class="page-header">
  <div>
    <h1 class="page-title">Set Up Authenticator App</h1>
    <p class="page-subtitle">Scan the QR code with your authenticator app, then enter the 6-digit code to confirm.</p>
  </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger" style="margin-bottom:20px;"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div style="max-width:520px;">
  <div class="card">
    <div class="card-body" style="padding:28px;">

      <!-- Step 1 -->
      <div style="margin-bottom:24px;">
        <div style="font-weight:600;font-size:14px;margin-bottom:8px;">
          <span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background:var(--accent);color:#fff;font-size:12px;margin-right:8px;">1</span>
          Install an authenticator app
        </div>
        <p style="font-size:13px;color:var(--text-muted);margin:0;padding-left:30px;">
          Google Authenticator, Authy, or Microsoft Authenticator — available on iOS & Android.
        </p>
      </div>

      <!-- Step 2 -->
      <div style="margin-bottom:24px;">
        <div style="font-weight:600;font-size:14px;margin-bottom:12px;">
          <span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background:var(--accent);color:#fff;font-size:12px;margin-right:8px;">2</span>
          Scan this QR code
        </div>
        <div style="padding-left:30px;">
          <div id="qrcode" style="display:inline-block;padding:12px;background:#fff;border-radius:8px;border:1px solid var(--border);"></div>
          <div style="margin-top:12px;font-size:12px;color:var(--text-muted);">
            Can't scan? Enter this key manually:<br>
            <code style="font-size:13px;font-weight:600;letter-spacing:2px;color:var(--text-primary);"><?= htmlspecialchars(chunk_split($secret, 4, ' ')) ?></code>
          </div>
        </div>
      </div>

      <!-- Step 3 — Verify -->
      <div>
        <div style="font-weight:600;font-size:14px;margin-bottom:12px;">
          <span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background:var(--accent);color:#fff;font-size:12px;margin-right:8px;">3</span>
          Enter the 6-digit code to confirm
        </div>
        <form method="post" action="<?= base_url('profile/mfa-setup') ?>" id="mfa-setup-form" style="padding-left:30px;">
          <div class="form-group" style="margin-bottom:16px;">
            <input type="text" name="totp_code"
                   class="form-control"
                   placeholder="000000"
                   maxlength="6"
                   inputmode="numeric"
                   autocomplete="one-time-code"
                   style="font-size:22px;letter-spacing:6px;text-align:center;font-weight:600;max-width:180px;"
                   autofocus required>
          </div>
          <div style="display:flex;gap:12px;">
            <button type="button" id="btn-activate-mfa" class="btn btn-primary">Activate MFA</button>
            <a href="<?= base_url('profile') ?>" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<script>
document.getElementById('btn-activate-mfa').addEventListener('click', function() {
    document.getElementById('mfa-setup-form').submit();
});
</script>

<!-- qrcodejs from CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
new QRCode(document.getElementById('qrcode'), {
    text: <?= json_encode($otp_url) ?>,
    width: 180,
    height: 180,
    colorDark: '#000000',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.M
});
</script>
