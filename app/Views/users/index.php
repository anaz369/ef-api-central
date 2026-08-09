<div class="page-header">
  <div>
    <h1 class="page-title">Users</h1>
    <p class="page-subtitle">Manage admin and ERP provider accounts.</p>
  </div>
  <div class="page-actions">
    <button type="button" id="btn-go-create-user" class="btn btn-primary">
      <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="2" x2="8" y2="14"/><line x1="2" y1="8" x2="14" y2="8"/></svg>
      Add User
    </button>
  </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success" style="margin-bottom:20px;"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger" style="margin-bottom:20px;"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="card">
  <div class="card-body" style="padding:0;">
    <?php if (empty($users)): ?>
    <div style="padding:48px;text-align:center;color:var(--text-muted);">No users found.</div>
    <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Company</th>
          <th>Role</th>
          <th>MFA</th>
          <th>Status</th>
          <th>Last Login</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td>
            <a href="<?= base_url('users/' . $u['id']) ?>" style="font-weight:500;">
              <?= htmlspecialchars($u['name']) ?>
            </a>
            <?php if ((int)$u['id'] === (int)session()->get('user_id')): ?>
            <span style="font-size:11px;color:var(--text-muted);margin-left:4px;">(you)</span>
            <?php endif; ?>
          </td>
          <td style="font-size:13px;"><?= htmlspecialchars($u['email']) ?></td>
          <td style="font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($u['company_name'] ?? '—') ?></td>
          <td>
            <?php if ((int)$u['type'] === 1): ?>
            <span class="badge badge-primary">Super Admin</span>
            <?php else: ?>
            <span class="badge">Admin</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ((int)$u['totp_enabled']): ?>
            <span style="color:var(--green,#16a34a);font-size:12px;font-weight:600;">Enabled</span>
            <?php else: ?>
            <span style="color:var(--text-muted);font-size:12px;">Off</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ((int)$u['is_active']): ?>
            <span class="badge badge-success">Active</span>
            <?php else: ?>
            <span class="badge badge-danger">Inactive</span>
            <?php endif; ?>
            <?php if ((int)$u['force_password_change']): ?>
            <span class="badge badge-warning" style="margin-left:4px;">Temp PW</span>
            <?php endif; ?>
          </td>
          <td style="font-size:12px;color:var(--text-muted);">
            <?= $u['last_login_at'] ? date('d M Y', strtotime($u['last_login_at'])) : 'Never' ?>
          </td>
          <td>
            <a href="<?= base_url('users/' . $u['id']) ?>" class="btn btn-sm btn-secondary">View</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<script>
document.getElementById('btn-go-create-user').addEventListener('click', function(e) {
    e.stopImmediatePropagation();
    window.location.href = '<?= base_url('users/create') ?>';
});
</script>
