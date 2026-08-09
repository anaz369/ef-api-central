<div class="page-header">
  <div>
    <h1 class="page-title"><?= $is_forced ? 'Set Your Password' : 'Change Password' ?></h1>
    <p class="page-subtitle">
      <?= $is_forced
          ? 'Welcome! You must set a new password before continuing.'
          : 'Choose a strong password for your account.' ?>
    </p>
  </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger" style="margin-bottom:20px;"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<?php if ($is_forced): ?>
<div style="margin-bottom:20px;padding:12px 16px;border-radius:8px;background:var(--blue-light,#dbeafe);color:var(--blue,#1d4ed8);font-size:13px;">
  This is a temporary password. Please set a permanent password to continue.
</div>
<?php endif; ?>

<div style="max-width:480px;">
  <div class="card">
    <div class="card-body">
      <form method="post" action="<?= base_url('profile/change-password') ?>" id="change-pw-form">
        <div class="form-group">
          <label class="form-label" for="new_password">New Password *</label>
          <input type="password" id="new_password" name="new_password"
                 class="form-control" placeholder="Min. 8 characters" required autofocus>
        </div>
        <div class="form-group">
          <label class="form-label" for="confirm_password">Confirm Password *</label>
          <input type="password" id="confirm_password" name="confirm_password"
                 class="form-control" placeholder="Repeat new password" required>
        </div>
        <div style="margin-top:20px;display:flex;gap:12px;">
          <button type="button" id="btn-change-pw" class="btn btn-primary">
            <?= $is_forced ? 'Set Password &amp; Continue' : 'Change Password' ?>
          </button>
          <?php if (!$is_forced): ?>
          <a href="<?= base_url('profile') ?>" class="btn btn-secondary">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('btn-change-pw').addEventListener('click', function() {
    document.getElementById('change-pw-form').submit();
});
</script>
