<?php
$colorPalette = ['primary', 'azure', 'purple', 'yellow', 'red', 'green', 'blue'];
$statusCls    = [0 => 'red', 1 => 'green', 2 => 'yellow'];
$statusLabel  = [0 => 'Inactive', 1 => 'Active', 2 => 'Pending'];
$modeCls      = [0 => 'blue', 1 => 'purple'];
$modeLabel    = [0 => 'Managed', 1 => 'Self'];
?>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success" style="margin-bottom:16px"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <div class="page-pretitle">Admin</div>
      <h1 class="page-title">Participants</h1>
    </div>
    <div class="page-actions">
      <a href="<?= base_url('participants/create') ?>" class="btn btn-primary">+ Add Participant</a>
    </div>
  </div>
</div>

<!-- Stats row -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:16px">

  <div class="card"><div class="stat">
    <div class="stat-icon teal">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
      </svg>
    </div>
    <div class="stat-content">
      <div class="stat-label">Total</div>
      <div class="stat-value-row"><span class="stat-value"><?= $stats['total'] ?></span></div>
      <div class="stat-subtext">All participants</div>
    </div>
  </div></div>

  <div class="card"><div class="stat">
    <div class="stat-icon green">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
        <polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
    </div>
    <div class="stat-content">
      <div class="stat-label">Active</div>
      <div class="stat-value-row"><span class="stat-value"><?= $stats['active'] ?></span></div>
      <div class="stat-subtext">Live on API</div>
    </div>
  </div></div>

  <div class="card"><div class="stat">
    <div class="stat-icon yellow">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
    </div>
    <div class="stat-content">
      <div class="stat-label">Pending</div>
      <div class="stat-value-row"><span class="stat-value"><?= $stats['pending'] ?></span></div>
      <div class="stat-subtext">Awaiting review</div>
    </div>
  </div></div>

  <div class="card"><div class="stat">
    <div class="stat-icon blue">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <rect x="2" y="3" width="20" height="14" rx="2"/>
        <line x1="8" y1="21" x2="16" y2="21"/>
        <line x1="12" y1="17" x2="12" y2="21"/>
      </svg>
    </div>
    <div class="stat-content">
      <div class="stat-label">Production</div>
      <div class="stat-value-row"><span class="stat-value"><?= $stats['production'] ?></span></div>
      <div class="stat-subtext">Prod access granted</div>
    </div>
  </div></div>

</div>

<!-- Participants table card -->
<div class="card">
  <div class="card-header" style="gap:12px;flex-wrap:wrap">
    <div class="users-filters">
      <div class="search-box" style="width:260px">
        <svg class="s-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="7" cy="7" r="5"/><path d="M11 11l3.5 3.5"/>
        </svg>
        <input type="text" id="p-search" placeholder="Search by name or email…" aria-label="Search participants">
      </div>
      <select class="form-control" id="filter-status" style="width:150px;height:32px" aria-label="Filter by status">
        <option value="">All statuses</option>
        <option value="Active">Active</option>
        <option value="Pending">Pending</option>
        <option value="Inactive">Inactive</option>
      </select>
      <select class="form-control" id="filter-mode" style="width:150px;height:32px" aria-label="Filter by mode">
        <option value="">All modes</option>
        <option value="Self">Self-hosted</option>
        <option value="Managed">Managed</option>
      </select>
    </div>
  </div>

  <div class="card-body p-0">
    <?php if (empty($participants)): ?>
    <div style="padding:3rem;text-align:center;color:var(--text-muted);">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"
           style="margin-bottom:1rem;opacity:.4;">
        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
      </svg>
      <p>No participants yet. <a href="<?= base_url('participants/create') ?>">Add the first one.</a></p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th style="width:40px"><input type="checkbox" id="select-all" aria-label="Select all"></th>
            <th>Company</th>
            <th>Mode</th>
            <th>Status</th>
            <th>Environment</th>
            <th>Country</th>
            <th>Created</th>
            <th data-orderable="false"></th>
          </tr>
        </thead>
        <tbody id="p-rows">
          <?php foreach ($participants as $i => $p):
            $words  = array_values(array_filter(explode(' ', trim($p['name']))));
            $ini    = count($words) >= 2
                        ? strtoupper(substr($words[0], 0, 1) . substr($words[count($words)-1], 0, 1))
                        : strtoupper(substr($p['name'], 0, 2));
            $col    = $colorPalette[$i % count($colorPalette)];
            $sLabel = $statusLabel[$p['status']] ?? 'Unknown';
            $sCls   = $statusCls[$p['status']]   ?? 'gray';
            $mLabel = $modeLabel[$p['integration_mode']] ?? 'Managed';
            $mCls   = $modeCls[$p['integration_mode']]   ?? 'blue';
          ?>
          <?php
            $pJson = htmlspecialchars(json_encode([
              'id'               => $p['id'],
              'name'             => $p['name'],
              'email'            => $p['email'],
              'phone'            => $p['phone'] ?? '',
              'legal_form'       => $p['legal_form'] ?? '',
              'status'           => (int)$p['status'],
              'integration_mode' => (int)$p['integration_mode'],
              'country'          => $p['country'] ?? 'AE',
              'emirate'          => $p['emirate'] ?? '',
              'city'             => $p['city'] ?? '',
              'trn'              => $p['trn'] ?? '',
              'tin_id'           => $p['tin_id'] ?? '',
              'trade_license'    => $p['trade_license'] ?? '',
              'peppol_scheme'    => $p['peppol_scheme'] ?? '',
              'peppol_id'        => $p['peppol_id'] ?? '',
            ]), ENT_QUOTES, 'UTF-8');
          ?>
          <tr data-name="<?= htmlspecialchars(strtolower($p['name'])) ?>"
              data-email="<?= htmlspecialchars(strtolower($p['email'])) ?>"
              data-status="<?= $sLabel ?>"
              data-mode="<?= $mLabel ?>">
            <td><input type="checkbox" class="row-cb" aria-label="Select row"></td>
            <td>
              <div class="cell-customer">
                <div class="cell-avatar" style="background:var(--<?= $col ?>);color:white"><?= $ini ?></div>
                <div>
                  <div class="cell-strong"><?= htmlspecialchars($p['name']) ?></div>
                  <div style="font-size:11.5px;color:var(--text-muted)"><?= htmlspecialchars($p['email']) ?></div>
                </div>
              </div>
            </td>
            <td><span class="status status-<?= $mCls ?>"><?= $mLabel ?></span></td>
            <td><span class="status status-<?= $sCls ?>"><?= $sLabel ?></span></td>
            <td>
              <?php if ($p['production_access']): ?>
                <span class="status status-green">Production</span>
              <?php else: ?>
                <span class="status status-gray">Development</span>
              <?php endif; ?>
            </td>
            <td style="font-size:12.5px;color:var(--text-muted)"><?= htmlspecialchars($p['country'] ?? '—') ?></td>
            <td style="font-size:12.5px;color:var(--text-muted)"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
            <td>
              <button class="card-opt-btn" data-row-menu
                      data-id="<?= $p['id'] ?>"
                      data-participant="<?= $pJson ?>"
                      aria-label="More options">
                <svg viewBox="0 0 16 16" fill="currentColor">
                  <circle cx="8" cy="3" r="1.2"/>
                  <circle cx="8" cy="8" r="1.2"/>
                  <circle cx="8" cy="13" r="1.2"/>
                </svg>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($participants)): ?>
<script type="module">
// Minified bundle exports: openMenu → n, showModal → n, showToast → t
import { n as openMenu } from '<?= base_url('assets/js/menus-BJGD7GPP.js') ?>';
import { n as showModal } from '<?= base_url('assets/js/modal-MTuCfURV.js') ?>';
import { t as showToast } from '<?= base_url('assets/js/toast-CBtjS_PZ.js') ?>';

const BASE = '<?= base_url('participants') ?>';
const CSRF = '<?= csrf_hash() ?>';
const CSRF_NAME = '<?= csrf_token() ?>';

// ── Utilities ────────────────────────────────────────────────────────────
function esc(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function hint(id, msg) {
  const el = document.getElementById(id);
  if (el) { el.textContent = msg; el.style.display = ''; }
}
function clearHint(id) {
  const el = document.getElementById(id);
  if (el) { el.style.display = 'none'; }
}
function phoneOptions(sel) {
  return [
    ['+971','UAE +971'],['+60','Malaysia +60'],['+966','Saudi Arabia +966'],
    ['+91','India +91'],['+1','US/Canada +1'],['+44','UK +44'],
    ['+65','Singapore +65'],['+49','Germany +49'],['+33','France +33'],['+61','Australia +61'],
  ].map(([v,l]) => `<option value="${v}"${v===sel?' selected':''}>${l}</option>`).join('');
}
function legalOptions(sel) {
  return ['','LLC','FZ-LLC','PJSC','Sole Proprietorship','Branch','Other']
    .map(v => `<option value="${v}"${v===sel?' selected':''}>${v||'Select…'}</option>`).join('');
}
function countryOptions(sel) {
  return [['AE','UAE'],['MY','Malaysia'],['SA','Saudi Arabia'],['IN','India'],['Other','Other']]
    .map(([v,l]) => `<option value="${v}"${v===sel?' selected':''}>${l}</option>`).join('');
}

// ── Search & filter ──────────────────────────────────────────────────────
let filterText = '', filterStatus = '', filterMode = '';
function applyFilters() {
  const q = filterText.toLowerCase();
  document.querySelectorAll('#p-rows tr').forEach(row => {
    const match = (!q || row.dataset.name.includes(q) || row.dataset.email.includes(q))
               && (!filterStatus || row.dataset.status === filterStatus)
               && (!filterMode   || row.dataset.mode   === filterMode);
    row.style.display = match ? '' : 'none';
  });
}
document.getElementById('p-search').addEventListener('input',  e => { filterText   = e.target.value; applyFilters(); });
document.getElementById('filter-status').addEventListener('change', e => { filterStatus = e.target.value; applyFilters(); });
document.getElementById('filter-mode').addEventListener('change',   e => { filterMode   = e.target.value; applyFilters(); });

// ── Select all ───────────────────────────────────────────────────────────
document.getElementById('select-all').addEventListener('change', e => {
  document.querySelectorAll('#p-rows .row-cb').forEach(cb => { cb.checked = e.target.checked; });
});

// ── Edit modal ───────────────────────────────────────────────────────────
function openEditModal(p) {
  const phoneParts = (p.phone || '').split(/\s+/);
  const hasCode    = phoneParts[0]?.startsWith('+');
  const phoneCode  = hasCode ? phoneParts[0] : '+971';
  const phoneNum   = hasCode ? phoneParts.slice(1).join(' ') : (p.phone || '');

  showModal({
    title: `Edit — ${esc(p.name)}`,
    body: `
      <form id="edit-form" class="modal-form" novalidate>
        <div class="modal-form-row">
          <label>Company Name <span style="color:var(--red)">*</span></label>
          <input type="text" name="name" id="e-name" class="form-control" value="${esc(p.name)}">
          <small id="e-err-name" style="color:var(--red);display:none;font-size:12px"></small>
        </div>
        <div class="modal-form-row">
          <label>Email <span style="color:var(--red)">*</span></label>
          <input type="email" name="email" id="e-email" class="form-control" value="${esc(p.email)}">
          <small id="e-err-email" style="color:var(--red);display:none;font-size:12px"></small>
        </div>
        <div class="modal-form-row">
          <label>Phone</label>
          <div style="display:flex;gap:6px">
            <select name="phone_code" class="form-control" style="width:150px;flex-shrink:0">${phoneOptions(phoneCode)}</select>
            <input type="text" name="phone_number" class="form-control" value="${esc(phoneNum)}" placeholder="50 123 4567" style="flex:1">
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div class="modal-form-row">
            <label>Legal Form</label>
            <select name="legal_form" class="form-control">${legalOptions(p.legal_form)}</select>
          </div>
          <div class="modal-form-row">
            <label>Status</label>
            <select name="status" class="form-control">
              <option value="1"${p.status===1?' selected':''}>Active</option>
              <option value="2"${p.status===2?' selected':''}>Pending</option>
              <option value="0"${p.status===0?' selected':''}>Inactive</option>
            </select>
          </div>
          <div class="modal-form-row">
            <label>Integration Mode</label>
            <select name="integration_mode" class="form-control">
              <option value="1"${p.integration_mode===1?' selected':''}>Self-Integration</option>
              <option value="0"${p.integration_mode===0?' selected':''}>Managed</option>
            </select>
          </div>
          <div class="modal-form-row">
            <label>Country</label>
            <select name="country" class="form-control">${countryOptions(p.country)}</select>
          </div>
          <div class="modal-form-row">
            <label>TRN <small style="color:var(--text-muted)">(15 digits)</small></label>
            <input type="text" name="trn" id="e-trn" class="form-control" value="${esc(p.trn)}" maxlength="15" inputmode="numeric">
            <small id="e-err-trn" style="color:var(--red);display:none;font-size:12px"></small>
          </div>
          <div class="modal-form-row">
            <label>TIN <small style="color:var(--text-muted)">(10 digits)</small></label>
            <input type="text" name="tin_id" id="e-tin" class="form-control" value="${esc(p.tin_id)}" maxlength="10" inputmode="numeric">
            <small id="e-err-tin" style="color:var(--red);display:none;font-size:12px"></small>
          </div>
        </div>
      </form>`,
    actions: [
      { label: 'Cancel', variant: 'outline' },
      {
        label: 'Save Changes',
        variant: 'primary',
        action: ({ body }) => {
          const form  = body.querySelector('#edit-form');
          const name  = form.querySelector('#e-name').value.trim();
          const email = form.querySelector('#e-email').value.trim();
          const trn   = form.querySelector('#e-trn').value.trim();
          const tin   = form.querySelector('#e-tin').value.trim();

          // Client-side validation
          let ok = true;
          clearHint('e-err-name'); clearHint('e-err-email');
          clearHint('e-err-trn');  clearHint('e-err-tin');

          if (!name)  { hint('e-err-name',  'Company name is required.'); ok = false; }
          if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            hint('e-err-email', 'Enter a valid email address.'); ok = false;
          }
          if (trn && !/^\d{15}$/.test(trn)) { hint('e-err-trn', 'TRN must be exactly 15 digits.'); ok = false; }
          if (tin && !/^\d{10}$/.test(tin)) { hint('e-err-tin', 'TIN must be exactly 10 digits.');  ok = false; }
          if (!ok) return false;

          const fd = new FormData(form);
          fd.append(CSRF_NAME, CSRF);

          fetch(BASE + '/' + p.id + '/update', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
              if (data.success) {
                showToast('Participant updated successfully.', { variant: 'success' });
                setTimeout(() => location.reload(), 600);
              } else {
                const msg = data.errors
                  ? Object.values(data.errors).join(' ')
                  : (data.message || 'Update failed.');
                showToast(msg, { variant: 'danger' });
              }
            })
            .catch(() => showToast('Network error. Please try again.', { variant: 'danger' }));

          return true; // close modal immediately; toast shows result
        }
      }
    ]
  });
}

// ── Delete modal ─────────────────────────────────────────────────────────
function openDeleteModal(p, row) {
  showModal({
    title: 'Delete Participant',
    body: `
      <div style="text-align:center;padding:8px 0 4px">
        <div style="width:56px;height:56px;border-radius:50%;background:var(--red-lt,#fef2f2);display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2">
            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
            <path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
          </svg>
        </div>
        <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">
          Delete <strong>${esc(p.name)}</strong>?
        </div>
        <div style="font-size:13px;color:var(--text-muted)">
          This participant will be soft-deleted and removed from the list.<br>
          All credentials will remain in the database for audit purposes.
        </div>
      </div>`,
    actions: [
      { label: 'Cancel', variant: 'outline' },
      {
        label: 'Delete',
        variant: 'danger',
        action: () => {
          const fd = new FormData();
          fd.append(CSRF_NAME, CSRF);

          fetch(BASE + '/' + p.id + '/delete', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
              if (data.success) {
                row.style.transition = 'opacity .3s, transform .3s';
                row.style.opacity    = '0';
                row.style.transform  = 'translateX(20px)';
                setTimeout(() => {
                  row.remove();
                  showToast(`${p.name} deleted.`, { variant: 'success' });
                }, 300);
              } else {
                showToast(data.message || 'Delete failed.', { variant: 'danger' });
              }
            })
            .catch(() => showToast('Network error. Please try again.', { variant: 'danger' }));

          return true;
        }
      }
    ]
  });
}

// ── Kebab menu ───────────────────────────────────────────────────────────
document.getElementById('p-rows').addEventListener('click', e => {
  const btn = e.target.closest('[data-row-menu]');
  if (!btn) return;
  e.stopPropagation();
  const p   = JSON.parse(btn.dataset.participant);
  const row = btn.closest('tr');

  openMenu(btn, [
    {
      label: 'View',
      action: () => { window.location.href = BASE + '/' + p.id; }
    },
    {
      label: 'Edit',
      action: () => openEditModal(p)
    },
    '-',
    {
      label: 'Delete',
      action: () => openDeleteModal(p, row)
    }
  ]);
});
</script>
<?php endif; ?>
