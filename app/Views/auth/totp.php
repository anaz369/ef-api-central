<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Two-Factor Authentication | API Central</title>
<script>(function(){try{var t=localStorage.getItem('theme');var d=window.matchMedia('(prefers-color-scheme: dark)').matches;var theme=t||(d?'dark':'light');document.documentElement.setAttribute('data-theme',theme);}catch(e){}})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/gentelella.css') ?>">
</head>
<body>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-brand">
      <div class="brand-icon">A</div>
      <div class="brand-name">API Central <small style="font-weight:400;color:var(--text-muted);font-size:13px;margin-left:2px">v1</small></div>
    </div>

    <div class="auth-title">Two-Factor Authentication</div>
    <div class="auth-subtitle">Enter the 6-digit code from your authenticator app.</div>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger" style="margin-bottom:16px;padding:10px 14px;border-radius:8px;background:var(--red-light,#fee2e2);color:var(--red,#dc2626);font-size:13px;">
      <?= session()->getFlashdata('error') ?>
    </div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('login/totp') ?>">
      <div class="form-group">
        <label class="form-label" for="totp_code">Authenticator Code</label>
        <input type="text" id="totp_code" name="totp_code"
               class="form-control"
               placeholder="000000"
               maxlength="6"
               autocomplete="one-time-code"
               inputmode="numeric"
               style="font-size:24px;letter-spacing:8px;text-align:center;font-weight:600;"
               autofocus required>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;height:38px;margin-top:8px">
        Verify
      </button>
    </form>

    <div style="margin-top:14px;text-align:center;font-size:13px;color:var(--text-muted)">
      <a href="<?= base_url('login') ?>" style="color:var(--text-muted)">← Back to login</a>
    </div>
  </div>
</div>

</body>
</html>
