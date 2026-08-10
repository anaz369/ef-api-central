<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In | Ethicfin</title>
<link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
<script>(function(){try{var t=localStorage.getItem('theme');var d=window.matchMedia('(prefers-color-scheme: dark)').matches;var theme=t||(d?'dark':'light');document.documentElement.setAttribute('data-theme',theme);}catch(e){}})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/gentelella.css') ?>">
</head>
<body>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-brand" style="justify-content:center;margin-bottom:8px;">
      <img src="<?= base_url('assets/images/ethicfin-logo-white.png') ?>" alt="Ethicfin" style="height:36px;object-fit:contain;">
    </div>

    <div class="auth-title">Welcome back</div>
    <div class="auth-subtitle">Sign in to continue to your dashboard.</div>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger" style="margin-bottom:16px;padding:10px 14px;border-radius:8px;background:var(--red-light,#fee2e2);color:var(--red,#dc2626);font-size:13px;">
      <?= session()->getFlashdata('error') ?>
    </div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('login') ?>">
      <div class="form-group">
        <label class="form-label" for="email">Email</label>
        <div class="input-group">
          <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="10" rx="1.5"/><path d="M2 5l6 4 6-4"/></svg>
          <input type="email" id="email" name="email" class="form-control" placeholder="you@company.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-group">
          <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="7" width="10" height="7" rx="1.5"/><path d="M5 7V5a3 3 0 016 0v2"/></svg>
          <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;height:38px;margin-top:8px">
        Sign in
      </button>
    </form>

    <div style="margin-top:14px;text-align:center;font-size:13px;">
      <a href="#" style="color:var(--text-muted);text-decoration:none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Forgot password?</a>
    </div>
  </div>
</div>

</body>
</html>
