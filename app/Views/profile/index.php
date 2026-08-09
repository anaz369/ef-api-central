<div class="page-header">
  <div>
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">Manage your account settings and security.</p>
  </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success" style="margin-bottom:20px;"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger" style="margin-bottom:20px;"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<!-- Account Completion Bar -->
<div class="card" style="margin-bottom:20px;">
  <div class="card-body" style="padding:20px 24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
      <span style="font-weight:600;font-size:14px;">Account Setup</span>
      <span style="font-weight:700;font-size:18px;color:<?= $pct === 100 ? 'var(--green,#16a34a)' : 'var(--accent)' ?>"><?= $pct ?>%</span>
    </div>
    <div style="background:var(--border);border-radius:999px;height:8px;overflow:hidden;margin-bottom:16px;">
      <div style="height:100%;width:<?= $pct ?>%;background:<?= $pct === 100 ? 'var(--green,#16a34a)' : 'var(--accent)' ?>;border-radius:999px;transition:width .4s;"></div>
    </div>
    <div style="display:flex;gap:24px;flex-wrap:wrap;">
      <?php
      $labels = [
          'profile_complete' => 'Profile Info',
          'password_changed' => 'Password Set',
          'mfa_enabled'      => 'MFA Enabled',
      ];
      foreach ($checks as $key => $done): ?>
      <div style="display:flex;align-items:center;gap:6px;font-size:13px;color:<?= $done ? 'var(--green,#16a34a)' : 'var(--text-muted)' ?>">
        <?php if ($done): ?>
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="8" r="7"/><polyline points="5 8 7 10 11 6"/></svg>
        <?php else: ?>
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="7"/></svg>
        <?php endif; ?>
        <?= htmlspecialchars($labels[$key]) ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

  <!-- Profile Info -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Profile Information</span>
    </div>
    <div class="card-body">
      <form method="post" action="<?= base_url('profile/update') ?>" id="profile-info-form">
        <div class="form-group">
          <label class="form-label">Email <span style="color:var(--text-muted);font-size:12px;">(cannot change)</span></label>
          <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:var(--input-disabled,var(--bg-secondary));cursor:not-allowed;">
        </div>
        <div class="form-group">
          <label class="form-label" for="name">Full Name *</label>
          <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="company_name">Company Name</label>
          <input type="text" id="company_name" name="company_name" class="form-control" value="<?= htmlspecialchars($user['company_name'] ?? '') ?>">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Role</label>
          <input type="text" class="form-control" value="<?= (int)$user['type'] === 1 ? 'Super Admin' : 'Admin' ?>" disabled style="background:var(--input-disabled,var(--bg-secondary));cursor:not-allowed;">
        </div>
        <div style="margin-top:16px;">
          <button type="button" id="btn-save-profile" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Security -->
  <div style="display:flex;flex-direction:column;gap:20px;">

    <!-- Change Password -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">Change Password</span>
      </div>
      <div class="card-body">
        <form method="post" action="<?= base_url('profile/change-password') ?>" id="change-pw-form">
          <div class="form-group">
            <label class="form-label" for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Min. 8 characters" required>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repeat new password" required>
          </div>
          <div style="margin-top:16px;">
            <button type="button" id="btn-change-pw" class="btn btn-secondary">Update Password</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MFA -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">Two-Factor Authentication</span>
        <?php if ((int)$user['totp_enabled']): ?>
        <span class="badge badge-success" style="margin-left:8px;">Enabled</span>
        <?php else: ?>
        <span class="badge badge-warning" style="margin-left:8px;">Not Set Up</span>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <?php if ((int)$user['totp_enabled']): ?>
          <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">
            Your account is protected with an authenticator app. To disable MFA, enter your password below.
          </p>
          <form method="post" action="<?= base_url('profile/mfa-disable') ?>" id="mfa-disable-form">
            <div class="form-group" style="margin-bottom:0;">
              <input type="password" name="password" class="form-control" placeholder="Enter your password to disable" required>
            </div>
            <div style="margin-top:12px;">
              <button type="button" id="btn-disable-mfa" class="btn btn-danger">Disable MFA</button>
            </div>
          </form>
        <?php else: ?>
          <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">
            Protect your account with an authenticator app like Google Authenticator or Authy.
          </p>
          <a href="<?= base_url('profile/mfa-setup') ?>" class="btn btn-primary">Set Up Authenticator</a>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<script>
// Bypass Gentelella form submit interception — form.submit() skips the submit event
document.getElementById('btn-save-profile').addEventListener('click', function() {
    document.getElementById('profile-info-form').submit();
});
document.getElementById('btn-change-pw')?.addEventListener('click', function() {
    document.getElementById('change-pw-form').submit();
});
var disableBtn = document.getElementById('btn-disable-mfa');
if (disableBtn) {
    disableBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to disable MFA?')) {
            document.getElementById('mfa-disable-form').submit();
        }
    });
}
</script>

<!-- Login History -->
<div class="card" style="margin-top:20px;">
  <div class="card-header">
    <span class="card-title">Recent Login History</span>
    <span style="font-size:12px;color:var(--text-muted);">Last 10 events</span>
  </div>
  <div class="card-body" style="padding:0;">
    <?php if (empty($login_logs)): ?>
    <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px;">No login history yet.</div>
    <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th>Date / Time</th>
          <th>IP Address</th>
          <th>Status</th>
          <th>Device</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($login_logs as $log): ?>
        <tr>
          <td style="font-size:13px;"><?= date('d M Y, H:i', strtotime($log['created_at'])) ?></td>
          <td><code style="font-size:12px;"><?= htmlspecialchars($log['ip_address']) ?></code></td>
          <td>
            <?php
            $statusColors = [
                'success'          => 'var(--green,#16a34a)',
                'failed'           => 'var(--red,#dc2626)',
                'totp_failed'      => 'var(--orange,#ea580c)',
                'password_changed' => 'var(--accent)',
            ];
            $statusLabels = [
                'success'          => 'Success',
                'failed'           => 'Failed',
                'totp_failed'      => 'MFA Failed',
                'password_changed' => 'Password Changed',
            ];
            $s = $log['status'];
            ?>
            <span style="color:<?= $statusColors[$s] ?? 'var(--text-muted)' ?>;font-size:12px;font-weight:600;">
              <?= htmlspecialchars($statusLabels[$s] ?? ucfirst($s)) ?>
            </span>
          </td>
          <td style="font-size:12px;color:var(--text-muted);max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($log['user_agent'] ?? '') ?>">
            <?= htmlspecialchars(substr($log['user_agent'] ?? 'Unknown', 0, 60)) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
