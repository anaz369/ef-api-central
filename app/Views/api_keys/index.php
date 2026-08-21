<?php
$envLabel = fn(int $e) => $e === 1 ? 'Production' : 'Development';
$statusColors = [
    'pending' => ['badge-warning',  'Pending'],
    'active'  => ['badge-success',  'Active'],
    'revoked' => ['badge-secondary','Revoked'],
];
?>

<div class="page-header">
  <div>
    <h1 class="page-title">API Keys</h1>
    <p class="page-subtitle">Manage your API credentials for the eInvoicing platform.</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn btn-primary" id="btn-request-key">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Request API Key
    </button>
  </div>
</div>

<!-- Flash messages -->
<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success" style="margin-bottom:16px"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger" style="margin-bottom:16px"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<!-- One-time secret banner (shown after admin approves) -->
<?php if ($new_secret): ?>
<div class="alert" style="margin-bottom:20px;padding:16px 20px;border-radius:8px;background:var(--green-light,#dcfce7);border:1px solid var(--green,#16a34a);">
  <div style="font-weight:600;color:var(--green,#16a34a);margin-bottom:6px;">
    Your <?= htmlspecialchars($new_secret_env ?? '') ?> API Secret<?= $new_secret_name ? ' for "' . htmlspecialchars($new_secret_name) . '"' : '' ?> is ready — copy it now, it will never be shown again.
  </div>
  <div style="display:flex;align-items:center;gap:10px;background:rgba(0,0,0,.05);padding:10px 14px;border-radius:6px;margin-top:8px;">
    <code id="new-secret-val" style="font-size:14px;font-weight:700;letter-spacing:1px;word-break:break-all;flex:1;"><?= htmlspecialchars($new_secret) ?></code>
    <button type="button" class="btn btn-sm" style="background:var(--green,#16a34a);color:#fff;border-color:transparent;flex-shrink:0;"
            onclick="navigator.clipboard.writeText(document.getElementById('new-secret-val').textContent).then(()=>this.textContent='Copied!')">
      Copy
    </button>
  </div>
  <div style="font-size:12px;color:var(--text-muted);margin-top:8px;">
    Use this together with your Client ID to request an access token. The client_id is visible in the table below.
  </div>
</div>
<?php endif; ?>

<!-- Keys list -->
<div class="card">
  <div class="card-body" style="padding:0;">
    <?php if (empty($keys)): ?>
    <div style="padding:48px;text-align:center;color:var(--text-muted);">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="opacity:.35;margin-bottom:12px;"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 11-7.778 7.778 5.5 5.5 0 017.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
      <p style="font-size:14px;margin-bottom:16px;">No API keys yet. Request your first key to start integrating.</p>
      <button type="button" class="btn btn-primary" id="btn-request-key-empty">+ Request API Key</button>
    </div>
    <?php else: ?>
    <table class="table" style="margin:0;">
      <thead>
        <tr>
          <th>Key Name</th>
          <th>Environment</th>
          <th>Status</th>
          <th>Client ID</th>
          <th>Last Used</th>
          <th>Requested</th>
          <th style="width:100px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($keys as $key):
          [$badgeCls, $badgeLabel] = $statusColors[$key['request_status']] ?? ['badge-secondary','Unknown'];
        ?>
        <tr>
          <td>
            <div style="font-size:13px;font-weight:500;"><?= htmlspecialchars($key['key_name'] ?? 'Admin-generated') ?></div>
            <?php if (!empty($key['key_description'])): ?>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;"><?= htmlspecialchars($key['key_description']) ?></div>
            <?php endif; ?>
          </td>
          <td>
            <?php if ((int)$key['environment'] === 1): ?>
            <span class="badge badge-success">Production</span>
            <?php else: ?>
            <span class="badge badge-info">Development</span>
            <?php endif; ?>
          </td>
          <td><span class="badge <?= $badgeCls ?>"><?= $badgeLabel ?></span></td>
          <td>
            <?php if ($key['request_status'] === 'active' && $key['client_id']): ?>
            <div style="display:flex;align-items:center;gap:6px;">
              <code style="font-size:11px;word-break:break-all;"><?= htmlspecialchars($key['client_id']) ?></code>
              <button type="button" class="btn btn-ghost btn-xs"
                      onclick="navigator.clipboard.writeText('<?= htmlspecialchars($key['client_id']) ?>').then(()=>showToast('Copied!','success'))"
                      title="Copy Client ID">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
              </button>
            </div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">Secret: ***<?= htmlspecialchars($key['client_secret_preview'] ?? '') ?></div>
            <?php elseif ($key['request_status'] === 'pending'): ?>
            <span style="font-size:12px;color:var(--text-muted);">Awaiting approval</span>
            <?php else: ?>
            <span style="font-size:12px;color:var(--text-muted);">—</span>
            <?php endif; ?>
          </td>
          <td style="font-size:12px;color:var(--text-muted);">
            <?= $key['last_used_at'] ? date('d M Y H:i', strtotime($key['last_used_at'])) : 'Never' ?>
          </td>
          <td style="font-size:12px;color:var(--text-muted);">
            <?= date('d M Y', strtotime($key['created_at'])) ?>
          </td>
          <td>
            <?php if ($key['request_status'] === 'active'): ?>
            <button type="button" class="btn btn-ghost btn-sm btn-revoke" data-id="<?= $key['id'] ?>">Revoke</button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<!-- Info box -->
<div class="card" style="margin-top:20px;">
  <div class="card-body">
    <div style="font-size:13px;color:var(--text-muted);line-height:1.7;">
      <strong style="color:var(--text);display:block;margin-bottom:6px;">How API Keys work</strong>
      <ul style="margin:0;padding-left:18px;">
        <li>Request a key with a name and description — our team will review and generate your credentials.</li>
        <li>Once approved, your <strong>Client ID</strong> is always visible here. Your <strong>Client Secret</strong> is shown <em>once only</em> at approval time.</li>
        <li>Use the Client ID + Secret to call <code>POST /api/auth/token</code> and get a Bearer token.</li>
        <li>If you lose your secret, revoke the key and request a new one.</li>
      </ul>
      <div style="margin-top:10px;">
        <a href="<?= base_url('api-catalog') ?>" class="btn btn-ghost btn-sm">View API Catalog →</a>
      </div>
    </div>
  </div>
</div>

<!-- ── Request API Key Modal ────────────────────────────────────────────── -->
<div id="modal-request-key" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">
  <div style="background:var(--bg-surface,#fff);border-radius:12px;width:100%;max-width:480px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,.25);">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border-color-light);display:flex;align-items:center;justify-content:space-between;">
      <div>
        <div style="font-size:16px;font-weight:600;color:var(--text);">Request API Key</div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">Our team will review and generate your credentials.</div>
      </div>
      <button type="button" id="modal-close" style="background:none;border:none;cursor:pointer;color:var(--text-muted);padding:4px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <form id="form-request-key" style="padding:20px 24px;">
      <div class="form-group">
        <label class="form-label" for="rk-name">Key Name <span style="color:var(--red,#dc2626);">*</span></label>
        <input type="text" id="rk-name" name="key_name" class="form-control" placeholder="e.g. ERP Integration Key" maxlength="100" required>
        <small class="form-hint">A short label to identify this key (e.g. which system will use it).</small>
      </div>
      <div class="form-group">
        <label class="form-label" for="rk-desc">Description</label>
        <textarea id="rk-desc" name="key_description" class="form-control" rows="2"
                  placeholder="e.g. Used by our Dubai HQ accounting system for invoice submission." maxlength="500"
                  style="resize:vertical;"></textarea>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label class="form-label">Environment</label>
        <div style="display:flex;gap:10px;margin-top:6px;">
          <label style="flex:1;border:1px solid var(--border-color-light);border-radius:8px;padding:12px;cursor:pointer;display:flex;align-items:flex-start;gap:10px;" id="env-dev-label">
            <input type="radio" name="environment" value="0" checked style="margin-top:2px;flex-shrink:0;">
            <div>
              <div style="font-size:13px;font-weight:500;color:var(--text);">Development</div>
              <div style="font-size:11px;color:var(--text-muted);">Peppol testbed — for integration testing</div>
            </div>
          </label>
          <label style="flex:1;border:1px solid var(--border-color-light);border-radius:8px;padding:12px;cursor:pointer;display:flex;align-items:flex-start;gap:10px;" id="env-prod-label">
            <input type="radio" name="environment" value="1" style="margin-top:2px;flex-shrink:0;">
            <div>
              <div style="font-size:13px;font-weight:500;color:var(--text);">Production</div>
              <div style="font-size:11px;color:var(--text-muted);">Live Peppol network — requires approval</div>
            </div>
          </label>
        </div>
      </div>
    </form>
    <div style="padding:16px 24px;border-top:1px solid var(--border-color-light);display:flex;justify-content:flex-end;gap:10px;">
      <button type="button" class="btn btn-secondary" id="modal-cancel">Cancel</button>
      <button type="button" class="btn btn-primary" id="btn-submit-request">
        <span id="btn-submit-text">Submit Request</span>
        <span id="btn-submit-spin" style="display:none;width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;display:none;"></span>
      </button>
    </div>
  </div>
</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
const APIKEYS_URL = '<?= base_url('apikeys') ?>';
const REQUEST_URL = '<?= base_url('apikeys/request') ?>';

function showToast(msg, type) {
    const d = document.createElement('div');
    d.style.cssText = 'position:fixed;top:20px;right:20px;padding:12px 20px;border-radius:8px;font-size:13px;font-weight:500;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,.15);';
    d.style.background = type === 'success' ? 'var(--green,#16a34a)' : 'var(--red,#dc2626)';
    d.style.color = '#fff';
    d.textContent = msg;
    document.body.appendChild(d);
    setTimeout(() => d.remove(), 3500);
}

// ── Modal open/close ─────────────────────────────────────────────────────
const modal = document.getElementById('modal-request-key');
function openModal() { modal.style.display = 'flex'; }
function closeModal() { modal.style.display = 'none'; document.getElementById('form-request-key').reset(); }

document.getElementById('btn-request-key')?.addEventListener('click', openModal);
document.getElementById('btn-request-key-empty')?.addEventListener('click', openModal);
document.getElementById('modal-close').addEventListener('click', closeModal);
document.getElementById('modal-cancel').addEventListener('click', closeModal);
modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });

// ── Submit request ───────────────────────────────────────────────────────
document.getElementById('btn-submit-request').addEventListener('click', function() {
    const form   = document.getElementById('form-request-key');
    const name   = form.querySelector('[name=key_name]').value.trim();
    if (!name) { showToast('Key name is required.', 'error'); return; }

    const btn  = this;
    const text = document.getElementById('btn-submit-text');
    btn.disabled = true;
    text.textContent = 'Submitting…';

    const data = new FormData(form);

    fetch(REQUEST_URL, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                closeModal();
                showToast(res.message, 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                showToast(res.message || 'Request failed.', 'error');
                btn.disabled = false;
                text.textContent = 'Submit Request';
            }
        })
        .catch(() => {
            showToast('Network error. Please try again.', 'error');
            btn.disabled = false;
            text.textContent = 'Submit Request';
        });
});

// ── Revoke ───────────────────────────────────────────────────────────────
document.querySelectorAll('.btn-revoke').forEach(function(btn) {
    btn.addEventListener('click', function() {
        if (!confirm('Revoke this API key? Any integration using it will stop working immediately.')) return;
        const id = btn.dataset.id;
        fetch(APIKEYS_URL + '/' + id + '/revoke', {
            method: 'POST',
            body:   new URLSearchParams({ '<?= csrf_token() ?>': '<?= csrf_hash() ?>' }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('API key revoked.', 'success');
                setTimeout(() => location.reload(), 900);
            } else {
                showToast(res.message || 'Failed to revoke.', 'error');
            }
        })
        .catch(() => showToast('Network error.', 'error'));
    });
});
</script>
