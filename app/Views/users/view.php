<div class="page-header">
  <div>
    <h1 class="page-title"><?= htmlspecialchars($user['name']) ?></h1>
    <p class="page-subtitle"><?= htmlspecialchars($user['email']) ?></p>
  </div>
  <div class="page-actions">
    <a href="<?= base_url('users') ?>" class="btn btn-secondary">← Back to Users</a>
  </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success" style="margin-bottom:20px;"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger" style="margin-bottom:20px;"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<!-- Temporary password banner (shown once on creation or reset) -->
<?php if (session()->getFlashdata('new_password')): ?>
<div class="alert" style="margin-bottom:20px;padding:16px 20px;border-radius:8px;background:var(--green-light,#dcfce7);border:1px solid var(--green,#16a34a);">
  <div style="font-weight:600;color:var(--green,#16a34a);margin-bottom:6px;">Temporary Password (shown once)</div>
  <code style="font-size:16px;font-weight:700;letter-spacing:2px;"><?= htmlspecialchars(session()->getFlashdata('new_password')) ?></code>
  <div style="font-size:12px;color:var(--text-muted);margin-top:8px;">
    Share this with the user securely. They will be asked to change it on first login.
  </div>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

  <!-- Edit User -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">User Details</span>
    </div>
    <div class="card-body">
      <form id="updateForm" method="post" action="<?= base_url('users/' . $user['id'] . '/update') ?>">
        <div class="form-group">
          <label class="form-label">Email <span style="color:var(--text-muted);font-size:12px;">(cannot change)</span></label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
        </div>
        <div class="form-group">
          <label class="form-label" for="name">Full Name *</label>
          <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="company_name">Company Name</label>
          <input type="text" id="company_name" name="company_name" class="form-control" value="<?= htmlspecialchars($user['company_name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Role</label>
          <select name="type" class="form-control">
            <option value="0" <?= (int)$user['type'] === 0 ? 'selected' : '' ?>>Admin</option>
            <option value="1" <?= (int)$user['type'] === 1 ? 'selected' : '' ?>>Super Admin</option>
          </select>
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Status</label>
          <select name="is_active" class="form-control">
            <option value="1" <?= (int)$user['is_active'] ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= !(int)$user['is_active'] ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>
        <div style="margin-top:16px;">
          <button type="button" class="btn btn-primary" onclick="submitUpdate()">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Security Actions -->
  <div style="display:flex;flex-direction:column;gap:20px;">

    <!-- Status & MFA Info -->
    <div class="card">
      <div class="card-header"><span class="card-title">Account Status</span></div>
      <div class="card-body">
        <table class="table table-sm" style="margin-bottom:0;">
          <tr>
            <td style="color:var(--text-muted);font-size:13px;width:140px;">Status</td>
            <td>
              <?php if ((int)$user['is_active']): ?>
              <span class="badge badge-success">Active</span>
              <?php else: ?>
              <span class="badge badge-danger">Inactive</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td style="color:var(--text-muted);font-size:13px;">MFA</td>
            <td>
              <?php if ((int)$user['totp_enabled']): ?>
              <span style="color:var(--green,#16a34a);font-size:13px;font-weight:600;">Enabled</span>
              <?php else: ?>
              <span style="color:var(--text-muted);font-size:13px;">Not set up</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td style="color:var(--text-muted);font-size:13px;">Password</td>
            <td>
              <?php if ((int)$user['force_password_change']): ?>
              <span class="badge badge-warning">Temp — not yet changed</span>
              <?php else: ?>
              <span style="font-size:13px;color:var(--text-muted);">Set by user</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td style="color:var(--text-muted);font-size:13px;">Last Login</td>
            <td style="font-size:13px;">
              <?= $user['last_login_at'] ? date('d M Y, H:i', strtotime($user['last_login_at'])) : 'Never' ?>
            </td>
          </tr>
          <tr>
            <td style="color:var(--text-muted);font-size:13px;">Created</td>
            <td style="font-size:13px;"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
          </tr>
        </table>
      </div>
    </div>

    <!-- Reset Password -->
    <?php if ((int)$user['id'] !== (int)session()->get('user_id')): ?>
    <div class="card">
      <div class="card-header"><span class="card-title">Reset Password</span></div>
      <div class="card-body">
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">
          Generate a new temporary password. MFA will also be disabled. The user must change it on next login.
        </p>
        <form method="post" action="<?= base_url('users/' . $user['id'] . '/reset-password') ?>" id="reset-pw-form">
          <button type="button" id="btn-reset-pw" class="btn btn-warning">Reset Password</button>
        </form>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- Login History -->
<div class="card" style="margin-top:20px;">
  <div class="card-header">
    <span class="card-title">Login History</span>
    <span style="font-size:12px;color:var(--text-muted);">Last 20 events</span>
  </div>
  <div class="card-body" style="padding:0;">
    <?php if (empty($login_logs)): ?>
    <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px;">No login history for this user.</div>
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
        <?php
        $statusColors = ['success'=>'var(--green,#16a34a)','failed'=>'var(--red,#dc2626)','totp_failed'=>'var(--orange,#ea580c)','password_changed'=>'var(--accent)'];
        $statusLabels = ['success'=>'Success','failed'=>'Failed','totp_failed'=>'MFA Failed','password_changed'=>'Password Changed'];
        foreach ($login_logs as $log): ?>
        <tr>
          <td style="font-size:13px;"><?= date('d M Y, H:i', strtotime($log['created_at'])) ?></td>
          <td><code style="font-size:12px;"><?= htmlspecialchars($log['ip_address']) ?></code></td>
          <td>
            <span style="color:<?= $statusColors[$log['status']] ?? 'var(--text-muted)' ?>;font-size:12px;font-weight:600;">
              <?= htmlspecialchars($statusLabels[$log['status']] ?? ucfirst($log['status'])) ?>
            </span>
          </td>
          <td style="font-size:12px;color:var(--text-muted);max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
            <?= htmlspecialchars(substr($log['user_agent'] ?? 'Unknown', 0, 70)) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<script>
function submitUpdate() {
    const form = document.getElementById('updateForm');
    const data = new FormData(form);

    fetch(form.action, {method:'POST', body: data})
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast(res.message, 'success');
            } else {
                const msg = res.message || Object.values(res.errors || {}).join('<br>');
                showToast(msg, 'error');
            }
        })
        .catch(() => showToast('Request failed.', 'error'));
}

// Bypass Gentelella form submit interception
var resetBtn = document.getElementById('btn-reset-pw');
if (resetBtn) {
    resetBtn.addEventListener('click', function() {
        if (confirm('Reset password and disable MFA for this user?')) {
            document.getElementById('reset-pw-form').submit();
        }
    });
}

function showToast(msg, type) {
    const d = document.createElement('div');
    d.style.cssText = 'position:fixed;top:20px;right:20px;padding:12px 20px;border-radius:8px;font-size:13px;font-weight:500;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,.15);';
    d.style.background = type === 'success' ? 'var(--green,#16a34a)' : 'var(--red,#dc2626)';
    d.style.color = '#fff';
    d.innerHTML = msg;
    document.body.appendChild(d);
    setTimeout(() => d.remove(), 3500);
}
</script>
