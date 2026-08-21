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

<!-- New API Secret Banner (shown once after generate) -->
<?php if (session()->getFlashdata('new_secret')): ?>
<div class="alert" style="margin-top:20px;padding:16px 20px;border-radius:8px;background:var(--green-light,#dcfce7);border:1px solid var(--green,#16a34a);">
  <div style="font-weight:600;color:var(--green,#16a34a);margin-bottom:6px;">
    <?= htmlspecialchars(session()->getFlashdata('new_secret_env')) ?> API Secret<?= session()->getFlashdata('new_secret_name') ? ' — ' . htmlspecialchars(session()->getFlashdata('new_secret_name')) : '' ?> (shown once — copy it now)
  </div>
  <code style="font-size:15px;font-weight:700;letter-spacing:1px;word-break:break-all;">
    <?= htmlspecialchars(session()->getFlashdata('new_secret')) ?>
  </code>
  <div style="font-size:12px;color:var(--text-muted);margin-top:8px;">
    The client_secret cannot be recovered after this page is closed. The client_id is shown in the credentials card below.
  </div>
</div>
<?php endif; ?>

<!-- Pending API Key Requests -->
<?php if (!empty($pending_requests)): ?>
<div class="card" style="margin-top:20px;border:1px solid var(--yellow,#f59e0b);">
  <div class="card-header" style="background:rgba(245,158,11,.08);">
    <span class="card-title">
      <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--yellow,#f59e0b);margin-right:6px;"></span>
      Pending API Key Requests
    </span>
    <span class="badge badge-warning"><?= count($pending_requests) ?> pending</span>
  </div>
  <div class="card-body" style="padding:0;">
    <table class="table" style="margin:0;">
      <thead>
        <tr>
          <th>Key Name</th>
          <th>Description</th>
          <th>Environment</th>
          <th>Requested</th>
          <th style="width:180px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pending_requests as $req): ?>
        <tr>
          <td style="font-size:13px;font-weight:500;"><?= htmlspecialchars($req['key_name'] ?? '—') ?></td>
          <td style="font-size:12px;color:var(--text-muted);max-width:200px;"><?= htmlspecialchars($req['key_description'] ?? '—') ?></td>
          <td>
            <?php if ((int)$req['environment'] === 1): ?>
            <span class="badge badge-success">Production</span>
            <?php else: ?>
            <span class="badge badge-info">Development</span>
            <?php endif; ?>
          </td>
          <td style="font-size:12px;color:var(--text-muted);"><?= date('d M Y', strtotime($req['created_at'])) ?></td>
          <td style="display:flex;gap:6px;">
            <form method="post" action="<?= base_url('users/' . $user['id'] . '/credentials/' . $req['id'] . '/approve') ?>" class="approve-form">
              <?= csrf_field() ?>
              <button type="button" class="btn btn-primary btn-sm btn-approve">Approve & Generate</button>
            </form>
            <form method="post" action="<?= base_url('users/' . $user['id'] . '/credentials/' . $req['id'] . '/reject') ?>" class="reject-form">
              <?= csrf_field() ?>
              <button type="button" class="btn btn-secondary btn-sm btn-reject">Reject</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- API Credentials -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">

  <?php
  $envDefs = [
      'Development' => [
          'env'  => 0,
          'cred' => $dev_cred ?? null,
          'color'=> 'var(--accent,#6366f1)',
      ],
      'Production' => [
          'env'  => 1,
          'cred' => $prod_cred ?? null,
          'color'=> 'var(--green,#16a34a)',
      ],
  ];
  foreach ($envDefs as $label => $def):
      $cred = $def['cred'];
  ?>
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= $def['color'] ?>;margin-right:6px;"></span>
        <?= $label ?> API Credentials
      </span>
    </div>
    <div class="card-body">
      <?php if ($cred): ?>
      <table class="table table-sm" style="margin-bottom:16px;">
        <tr>
          <td style="color:var(--text-muted);font-size:13px;width:130px;">Client ID</td>
          <td><code style="font-size:12px;word-break:break-all;"><?= htmlspecialchars($cred['client_id']) ?></code></td>
        </tr>
        <tr>
          <td style="color:var(--text-muted);font-size:13px;">Client Secret</td>
          <td><code style="font-size:12px;">***<?= htmlspecialchars($cred['client_secret_preview'] ?? '') ?></code>
            <span style="font-size:11px;color:var(--text-muted);margin-left:6px;">(not recoverable)</span>
          </td>
        </tr>
        <tr>
          <td style="color:var(--text-muted);font-size:13px;">Last Used</td>
          <td style="font-size:12px;"><?= $cred['last_used_at'] ? date('d M Y, H:i', strtotime($cred['last_used_at'])) : 'Never' ?></td>
        </tr>
      </table>
      <?php else: ?>
      <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">No <?= strtolower($label) ?> credentials yet.</p>
      <?php endif; ?>

      <form method="post" action="<?= base_url('users/' . $user['id'] . '/generate-credentials') ?>" class="cred-gen-form">
        <input type="hidden" name="environment" value="<?= $def['env'] ?>">
        <button type="button" class="btn <?= $cred ? 'btn-warning' : 'btn-primary' ?> btn-sm btn-gen-cred">
          <?= $cred ? 'Regenerate' : 'Generate' ?> <?= $label ?> Credentials
        </button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>

</div>

<!-- Participants linked to this user -->
<div class="card" style="margin-top:20px;">
  <div class="card-header">
    <span class="card-title">Participants</span>
    <a href="<?= base_url('participants/create') ?>" class="btn btn-primary btn-sm">+ Add Participant</a>
  </div>
  <div class="card-body" style="padding:0;">
    <?php if (empty($participants)): ?>
    <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px;">
      No participants linked to this user yet.
    </div>
    <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Peppol ID</th>
          <th>TRN</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($participants as $p): ?>
        <tr>
          <td style="font-size:13px;"><?= htmlspecialchars($p['name'] ?? '-') ?></td>
          <td><code style="font-size:11px;"><?= htmlspecialchars($p['peppol_id'] ?? '-') ?></code></td>
          <td style="font-size:13px;"><?= htmlspecialchars($p['tax_id'] ?? '-') ?></td>
          <td>
            <?php
            $statusMap = [1=>'Active',0=>'Inactive',2=>'Pending',3=>'Deleted'];
            $statusBadge = ['Active'=>'badge-success','Inactive'=>'badge-secondary','Pending'=>'badge-warning','Deleted'=>'badge-danger'];
            $sl = $statusMap[$p['status'] ?? 0] ?? 'Unknown';
            ?>
            <span class="badge <?= $statusBadge[$sl] ?? '' ?>"><?= $sl ?></span>
          </td>
          <td>
            <a href="<?= base_url('participants/' . $p['id']) ?>" class="btn btn-secondary btn-xs">View</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
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

// Approve credential request
document.querySelectorAll('.btn-approve').forEach(function(btn) {
    btn.addEventListener('click', function() {
        if (confirm('Approve this request and generate API credentials? Any existing active key for the same environment will be revoked.')) {
            btn.closest('form').submit();
        }
    });
});

// Reject credential request
document.querySelectorAll('.btn-reject').forEach(function(btn) {
    btn.addEventListener('click', function() {
        if (confirm('Reject this API key request?')) {
            btn.closest('form').submit();
        }
    });
});

// Confirm before regenerating credentials
document.querySelectorAll('.btn-gen-cred').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var isRegen = btn.textContent.trim().startsWith('Regenerate');
        var msg = isRegen
            ? 'Regenerate credentials? The existing client_secret will stop working immediately.'
            : 'Generate API credentials for this user?';
        if (confirm(msg)) {
            btn.closest('form').submit();
        }
    });
});

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
