<div class="page-header">
  <div class="page-header-row">
    <div>
      <div class="page-pretitle">Participants</div>
      <h1 class="page-title">Add Participant</h1>
    </div>
    <div style="margin-left:auto;">
      <a href="<?= base_url('participants') ?>" class="btn btn-ghost">← Back</a>
    </div>
  </div>
</div>

<?php $errors = session()->getFlashdata('errors') ?? []; ?>
<?php if (!empty($errors)): ?>
<div class="alert alert-danger" style="margin-bottom:1.5rem;">
  <ul style="margin:0;padding-left:1.25rem;">
    <?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<div class="row col-1">
  <div class="card">

    <!-- Wizard steps header -->
    <div class="wizard-steps" id="steps">
      <div class="wizard-step active" data-step="0">
        <div class="num">1</div>
        <div><div class="label">Company</div><div class="sub">Basic details</div></div>
      </div>
      <div class="wizard-step" data-step="1">
        <div class="num">2</div>
        <div><div class="label">Tax & Address</div><div class="sub">Registration info</div></div>
      </div>
      <div class="wizard-step" data-step="2">
        <div class="num">3</div>
        <div><div class="label">Peppol</div><div class="sub">Network identity</div></div>
      </div>
      <div class="wizard-step" data-step="3">
        <div class="num">4</div>
        <div><div class="label">Review</div><div class="sub">Confirm & save</div></div>
      </div>
    </div>

    <form method="post" action="<?= base_url('participants/create') ?>" id="wizard-form">
      <?= csrf_field() ?>

      <!-- Step 1: Company -->
      <div class="wizard-panel active" data-panel="0" style="padding:1.5rem 1.5rem 0;">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Company Name <span style="color:var(--color-danger)">*</span></label>
            <input class="form-control" type="text" name="name" id="f_name"
                   value="<?= old('name') ?>" placeholder="e.g. Acme Trading LLC">
            <small class="form-hint error-hint" id="err_name" style="color:var(--color-danger);display:none">Company name is required.</small>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address <span style="color:var(--color-danger)">*</span></label>
            <input class="form-control" type="email" name="email" id="f_email"
                   value="<?= old('email') ?>" placeholder="contact@company.com">
            <small class="form-hint error-hint" id="err_email" style="color:var(--color-danger);display:none">Enter a valid email address.</small>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Phone</label>
            <div style="display:flex;gap:6px;">
              <select name="phone_code" id="f_phone_code" class="form-control"
                      style="width:130px;flex-shrink:0;">
                <?php
                  $phoneCodes = [
                    '+971' => '+971 UAE',
                    '+60'  => '+60 Malaysia',
                    '+966' => '+966 Saudi Arabia',
                    '+91'  => '+91 India',
                    '+1'   => '+1 US/Canada',
                    '+44'  => '+44 UK',
                    '+65'  => '+65 Singapore',
                    '+49'  => '+49 Germany',
                    '+33'  => '+33 France',
                    '+61'  => '+61 Australia',
                  ];
                  $oldCode = old('phone_code', '+971');
                  foreach ($phoneCodes as $code => $label):
                ?>
                <option value="<?= $code ?>" <?= $oldCode === $code ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
              </select>
              <input class="form-control" type="text" name="phone_number" id="f_phone_number"
                     value="<?= old('phone_number') ?>" placeholder="50 123 4567" style="flex:1;">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Legal Form</label>
            <select name="legal_form" id="f_legal_form" class="form-control">
              <option value="">Select…</option>
              <?php foreach (['LLC','FZ-LLC','PJSC','Sole Proprietorship','Branch','Other'] as $lf): ?>
              <option value="<?= $lf ?>" <?= old('legal_form') === $lf ? 'selected' : '' ?>><?= $lf ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Managed by User <span style="color:var(--text-muted);font-weight:400;font-size:12px;">(optional — leave blank if Ethicfin manages directly)</span></label>
          <select name="user_id" id="f_user_id" class="form-control">
            <option value="">— Ethicfin managed (no user) —</option>
            <?php foreach (($users ?? []) as $u): ?>
            <option value="<?= $u['id'] ?>" <?= old('user_id') == $u['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($u['name']) ?><?= $u['company_name'] ? ' (' . htmlspecialchars($u['company_name']) . ')' : '' ?>
            </option>
            <?php endforeach; ?>
          </select>
          <small class="form-hint">Select the ERP or client user who will manage this participant's invoices.</small>
        </div>

        <div class="form-group">
          <label class="form-label">Integration Mode</label>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:4px;">
            <label class="form-check" style="border:1px solid var(--border-color);padding:14px 16px;border-radius:var(--radius-sm);cursor:pointer;" id="mode-self-card">
              <input type="radio" name="integration_mode" id="f_mode_self" value="1"
                     <?= old('integration_mode','1') === '1' ? 'checked' : '' ?>>
              <span>
                <strong>Self-Integration</strong><br>
                <span style="color:var(--text-muted);font-size:12px;">They integrate via API using client_id &amp; secret</span>
              </span>
            </label>
            <label class="form-check" style="border:1px solid var(--border-color);padding:14px 16px;border-radius:var(--radius-sm);cursor:pointer;" id="mode-managed-card">
              <input type="radio" name="integration_mode" id="f_mode_managed" value="0"
                     <?= old('integration_mode') === '0' ? 'checked' : '' ?>>
              <span>
                <strong>Managed</strong><br>
                <span style="color:var(--text-muted);font-size:12px;">Ethicfin operates Peppol on their behalf</span>
              </span>
            </label>
          </div>
        </div>
      </div>

      <!-- Step 2: Tax & Address -->
      <div class="wizard-panel" data-panel="1" style="padding:1.5rem 1.5rem 0;">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">TRN <span style="color:var(--text-muted);font-weight:400;font-size:12px;">(Tax Registration Number — 15 digits)</span></label>
            <input class="form-control" type="text" name="trn" id="f_trn"
                   value="<?= old('trn') ?>" placeholder="e.g. 100123456700003"
                   maxlength="15" inputmode="numeric">
            <small class="form-hint error-hint" id="err_trn" style="color:var(--color-danger);display:none">TRN must be exactly 15 digits.</small>
          </div>
          <div class="form-group">
            <label class="form-label">TIN <span style="color:var(--text-muted);font-weight:400;font-size:12px;">(Tax Identification Number — 10 digits)</span></label>
            <input class="form-control" type="text" name="tin_id" id="f_tin"
                   value="<?= old('tin_id') ?>" placeholder="e.g. 1234567890"
                   maxlength="10" inputmode="numeric">
            <small class="form-hint error-hint" id="err_tin" style="color:var(--color-danger);display:none">TIN must be exactly 10 digits.</small>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Trade License Number</label>
            <input class="form-control" type="text" name="trade_license" id="f_trade_license"
                   value="<?= old('trade_license') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Country</label>
            <select name="country" id="f_country" class="form-control">
              <?php foreach (['AE'=>'UAE','MY'=>'Malaysia','SA'=>'Saudi Arabia','IN'=>'India','Other'=>'Other'] as $code => $label): ?>
              <option value="<?= $code ?>" <?= old('country','AE') === $code ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Emirate / State</label>
            <select name="emirate" id="f_emirate" class="form-control">
              <option value="">Select…</option>
              <?php foreach (['Abu Dhabi','Dubai','Sharjah','Ajman','Umm Al Quwain','Ras Al Khaimah','Fujairah'] as $em): ?>
              <option value="<?= $em ?>" <?= old('emirate') === $em ? 'selected' : '' ?>><?= $em ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">City</label>
            <input class="form-control" type="text" name="city" id="f_city"
                   value="<?= old('city') ?>" placeholder="e.g. Dubai">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Street Name <span style="color:var(--color-danger)">*</span></label>
            <input class="form-control" type="text" name="address_line1" id="f_street"
                   value="<?= old('address_line1') ?>" placeholder="e.g. Office 101, Business Bay">
            <small class="form-hint error-hint" id="err_street" style="color:var(--color-danger);display:none">Street name is required.</small>
          </div>
          <div class="form-group">
            <label class="form-label">Postal Zone <span style="color:var(--color-danger)">*</span></label>
            <input class="form-control" type="text" name="postal_zone" id="f_postal"
                   value="<?= old('postal_zone') ?>" placeholder="e.g. 00000">
            <small class="form-hint error-hint" id="err_postal" style="color:var(--color-danger);display:none">Postal zone is required.</small>
          </div>
        </div>
      </div>

      <!-- Step 3: Peppol -->
      <div class="wizard-panel" data-panel="2" style="padding:1.5rem 1.5rem 0;">
        <div class="form-help" style="margin-bottom:1.25rem;">
          Peppol status is auto-detected from the FTA onboarding registry using the TRN entered in step 2. No manual entry required.
        </div>

        <!-- Hidden fields populated by AJAX lookup -->
        <input type="hidden" name="peppol_participant_id" id="f_peppol_participant_id">
        <input type="hidden" name="peppol_id"             id="f_peppol_id">
        <input type="hidden" name="peppol_scheme"         id="f_peppol_scheme">
        <input type="hidden" name="peppol_status"         id="f_peppol_status">

        <!-- Lookup result panel -->
        <div id="peppol-checking" style="display:none;text-align:center;padding:1.5rem 0;color:var(--text-muted);">
          <div style="display:inline-block;width:18px;height:18px;border:2px solid var(--border-color);border-top-color:var(--primary);border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:6px;"></div>
          Checking Peppol registry…
        </div>
        <div id="peppol-result" style="display:none;margin-bottom:1rem;"></div>
        <div id="peppol-no-trn" class="alert" style="background:var(--bg-surface-secondary,#f8fafc);border:1px solid var(--border-color);margin-bottom:1rem;">
          <svg class="alert-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 5v3M8 10.5v.5"/></svg>
          <div class="alert-body">No TRN entered — go back to step 2 to enter TRN for auto-detection.</div>
        </div>

        <div class="alert" style="background:var(--primary-lt,#eff6ff);border:1px solid var(--primary-border,#bfdbfe);border-radius:var(--radius-sm);">
          <svg class="alert-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 5v3M8 10.5v.5"/></svg>
          <div class="alert-body">After creating the participant, development API credentials are generated and the participant can start sending test invoices immediately.</div>
        </div>
      </div>

      <!-- Step 4: Review -->
      <div class="wizard-panel" data-panel="3" style="padding:1.5rem 1.5rem 0;">
        <div class="alert alert-success" style="margin-bottom:1.25rem;">
          <svg class="alert-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M5 8l2 2 4-4"/></svg>
          <div class="alert-body"><strong>Ready to create.</strong> Review the details below before saving.</div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
          <table style="font-size:.875rem;border-collapse:collapse;width:100%;">
            <tr><td colspan="2" style="font-weight:600;padding:.5rem 0;border-bottom:1px solid var(--border-color);margin-bottom:.5rem;">Company</td></tr>
            <tr><td style="color:var(--text-muted);padding:.4rem 0;width:45%;">Name</td><td id="r_name" style="font-weight:500;">—</td></tr>
            <tr><td style="color:var(--text-muted);padding:.4rem 0;">Email</td><td id="r_email">—</td></tr>
            <tr><td style="color:var(--text-muted);padding:.4rem 0;">Phone</td><td id="r_phone">—</td></tr>
            <tr><td style="color:var(--text-muted);padding:.4rem 0;">Legal Form</td><td id="r_legal_form">—</td></tr>
            <tr><td style="color:var(--text-muted);padding:.4rem 0;">Mode</td><td id="r_mode">—</td></tr>
          </table>
          <table style="font-size:.875rem;border-collapse:collapse;width:100%;">
            <tr><td colspan="2" style="font-weight:600;padding:.5rem 0;border-bottom:1px solid var(--border-color);">Tax & Location</td></tr>
            <tr><td style="color:var(--text-muted);padding:.4rem 0;width:45%;">TRN</td><td id="r_trn">—</td></tr>
            <tr><td style="color:var(--text-muted);padding:.4rem 0;">TIN</td><td id="r_tin">—</td></tr>
            <tr><td style="color:var(--text-muted);padding:.4rem 0;">Trade License</td><td id="r_trade_license">—</td></tr>
            <tr><td style="color:var(--text-muted);padding:.4rem 0;">Country</td><td id="r_country">—</td></tr>
            <tr><td style="color:var(--text-muted);padding:.4rem 0;">Emirate</td><td id="r_emirate">—</td></tr>
            <tr><td style="color:var(--text-muted);padding:.4rem 0;">City</td><td id="r_city">—</td></tr>
            <tr><td style="color:var(--text-muted);padding:.4rem 0;">Street</td><td id="r_street">—</td></tr>
            <tr><td style="color:var(--text-muted);padding:.4rem 0;">Postal Zone</td><td id="r_postal">—</td></tr>
            <tr><td style="color:var(--text-muted);padding:.4rem 0;">Peppol ID</td><td id="r_peppol">—</td></tr>
            <tr><td style="color:var(--text-muted);padding:.4rem 0;">Peppol Status</td><td id="r_peppol_status">—</td></tr>
          </table>
        </div>
      </div>

      <!-- Wizard footer actions -->
      <div class="form-actions" style="padding:12px 20px;border-top:1px solid var(--border-color-light);margin:1.5rem 0 0;">
        <button type="button" class="btn btn-outline" id="prev-btn" disabled style="opacity:.5">← Back</button>
        <div style="margin-left:auto;display:flex;gap:6px;">
          <a href="<?= base_url('participants') ?>" class="btn btn-outline">Cancel</a>
          <button type="button" class="btn btn-primary" id="next-btn">Next →</button>
        </div>
      </div>

    </form>
  </div>
</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
<script type="module">
let step = 0;
const total = 4;
const nextBtn  = document.getElementById('next-btn');
const prevBtn  = document.getElementById('prev-btn');
const stepsEls = document.querySelectorAll('.wizard-step');
const panelsEls= document.querySelectorAll('.wizard-panel');

// ── Helpers ────────────────────────────────────────────────────────────────
function val(id)   { return (document.getElementById(id)?.value || '').trim(); }
function show(id)  { const el = document.getElementById(id); if (el) el.style.display = ''; }
function hide(id)  { const el = document.getElementById(id); if (el) el.style.display = 'none'; }
function err(inputId, hintId, msg) {
  const inp = document.getElementById(inputId);
  const hint = document.getElementById(hintId);
  if (inp) inp.style.borderColor = 'var(--color-danger)';
  if (hint) { hint.textContent = msg; hint.style.display = ''; }
  if (inp) inp.focus();
}
function clearErr(inputId, hintId) {
  const inp = document.getElementById(inputId);
  const hint = document.getElementById(hintId);
  if (inp) inp.style.borderColor = '';
  if (hint) hint.style.display = 'none';
}

// ── Radio mode cards ───────────────────────────────────────────────────────
function syncModeCards() {
  const selfCard    = document.getElementById('mode-self-card');
  const managedCard = document.getElementById('mode-managed-card');
  const selfRadio   = document.getElementById('f_mode_self');
  const primary     = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#2563eb';
  selfCard.style.borderColor    = selfRadio.checked  ? primary : '';
  selfCard.style.background     = selfRadio.checked  ? 'var(--primary-lt,#eff6ff)' : '';
  managedCard.style.borderColor = !selfRadio.checked ? primary : '';
  managedCard.style.background  = !selfRadio.checked ? 'var(--primary-lt,#eff6ff)' : '';
}
document.querySelectorAll('input[name="integration_mode"]').forEach(r => r.addEventListener('change', syncModeCards));
syncModeCards();

// Clear error styling on input
['f_name','f_email','f_trn','f_tin','f_street','f_postal'].forEach(id => {
  document.getElementById(id)?.addEventListener('input', () => {
    const map = {f_name:'err_name', f_email:'err_email', f_trn:'err_trn', f_tin:'err_tin', f_street:'err_street', f_postal:'err_postal'};
    clearErr(id, map[id]);
  });
});

// ── Validation ─────────────────────────────────────────────────────────────
function validateStep(s) {
  if (s === 0) {
    const name  = val('f_name');
    const email = val('f_email');
    let ok = true;
    if (!name) {
      err('f_name', 'err_name', 'Company name is required.');
      ok = false;
    }
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      err('f_email', 'err_email', 'Enter a valid email address.');
      if (ok) ok = false;
    }
    return ok;
  }
  if (s === 1) {
    const trn    = val('f_trn');
    const tin    = val('f_tin');
    const street = val('f_street');
    const postal = val('f_postal');
    let ok = true;
    if (trn && !/^\d{15}$/.test(trn)) {
      err('f_trn', 'err_trn', 'TRN must be exactly 15 digits.');
      ok = false;
    }
    if (tin && !/^\d{10}$/.test(tin)) {
      err('f_tin', 'err_tin', 'TIN must be exactly 10 digits.');
      if (ok) ok = false;
    }
    if (!street) {
      err('f_street', 'err_street', 'Street name is required.');
      if (ok) ok = false;
    }
    if (!postal) {
      err('f_postal', 'err_postal', 'Postal zone is required.');
      if (ok) ok = false;
    }
    return ok;
  }
  return true;
}

// ── Peppol lookup ──────────────────────────────────────────────────────────
function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

async function checkPeppol() {
  const trn       = val('f_trn');
  const noTrnEl   = document.getElementById('peppol-no-trn');
  const checkEl   = document.getElementById('peppol-checking');
  const resultEl  = document.getElementById('peppol-result');

  // Reset hidden fields
  ['f_peppol_participant_id','f_peppol_id','f_peppol_scheme','f_peppol_status'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });

  if (!trn || !/^\d{15}$/.test(trn)) {
    noTrnEl.style.display  = '';
    checkEl.style.display  = 'none';
    resultEl.style.display = 'none';
    return;
  }

  noTrnEl.style.display  = 'none';
  resultEl.style.display = 'none';
  checkEl.style.display  = '';

  try {
    const resp = await fetch(`<?= base_url('participants/lookup-peppol') ?>?trn=${encodeURIComponent(trn)}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await resp.json();
    checkEl.style.display = 'none';

    if (data.found) {
      document.getElementById('f_peppol_participant_id').value = data.peppol_participant_id;
      document.getElementById('f_peppol_id').value             = data.peppol_id;
      document.getElementById('f_peppol_scheme').value         = data.peppol_scheme;
      document.getElementById('f_peppol_status').value         = data.status;

      const colors   = { active: ['green','#22c55e'], pending: ['yellow','#f59e0b'], delinked: ['red','#ef4444'] };
      const [cn, ch] = colors[data.status] || ['secondary','#6b7280'];

      resultEl.innerHTML = `
        <div style="border:1px solid ${ch};border-radius:var(--radius-sm);padding:1rem 1.25rem;background:var(--${cn}-lt,#f9fafb);">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:.5rem;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="${ch}" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <strong>Peppol registration found</strong>
            <span class="status status-${cn}" style="margin-left:auto;">${capitalize(data.status)}</span>
          </div>
          <div style="font-size:13px;line-height:1.7;">
            <span style="color:var(--text-muted)">Peppol ID:</span> <code>${data.peppol_id}</code><br>
            <span style="color:var(--text-muted)">Entity:</span> ${data.entity_name || '—'}
          </div>
        </div>`;
    } else {
      resultEl.innerHTML = `
        <div style="border:1px solid var(--yellow,#f59e0b);border-radius:var(--radius-sm);padding:1rem 1.25rem;background:var(--yellow-lt,#fffbeb);">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:.35rem;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--yellow,#f59e0b)" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <strong>No Peppol registration found</strong>
          </div>
          <div style="font-size:13px;color:var(--text-muted);">This TRN has no Peppol record in the FTA registry. The participant will need to complete FTA onboarding after account creation.</div>
        </div>`;
    }
    resultEl.style.display = '';
  } catch {
    checkEl.style.display  = 'none';
    resultEl.innerHTML     = `<div class="alert">Could not reach Peppol registry. You can proceed and link later from the participant view.</div>`;
    resultEl.style.display = '';
  }
}

// ── Review ─────────────────────────────────────────────────────────────────
function populateReview() {
  const phoneCode = val('f_phone_code');
  const phoneNum  = val('f_phone_number');
  const phone     = phoneNum ? phoneCode + ' ' + phoneNum : '—';
  const countryEl = document.getElementById('f_country');
  const pid       = val('f_peppol_id');
  const pStatus   = val('f_peppol_status');

  document.getElementById('r_name').textContent          = val('f_name')          || '—';
  document.getElementById('r_email').textContent         = val('f_email')         || '—';
  document.getElementById('r_phone').textContent         = phone;
  document.getElementById('r_legal_form').textContent    = val('f_legal_form')    || '—';
  document.getElementById('r_mode').textContent          = document.getElementById('f_mode_self').checked ? 'Self-Integration' : 'Managed';
  document.getElementById('r_trn').textContent           = val('f_trn')           || '—';
  document.getElementById('r_tin').textContent           = val('f_tin')           || '—';
  document.getElementById('r_trade_license').textContent = val('f_trade_license') || '—';
  document.getElementById('r_country').textContent       = countryEl.options[countryEl.selectedIndex]?.text || '—';
  document.getElementById('r_emirate').textContent       = val('f_emirate')       || '—';
  document.getElementById('r_city').textContent          = val('f_city')          || '—';
  document.getElementById('r_street').textContent        = val('f_street')        || '—';
  document.getElementById('r_postal').textContent        = val('f_postal')        || '—';
  document.getElementById('r_peppol').textContent        = pid || '—';
  document.getElementById('r_peppol_status').textContent = pStatus ? capitalize(pStatus) : (pid ? '—' : 'Not onboarded');
}

// ── Render step ────────────────────────────────────────────────────────────
function render() {
  stepsEls.forEach((el, i) => {
    el.classList.toggle('active', i === step);
    el.classList.toggle('done',   i < step);
  });
  panelsEls.forEach((el, i) => el.classList.toggle('active', i === step));
  prevBtn.disabled      = step === 0;
  prevBtn.style.opacity = step === 0 ? '.5' : '1';
  // Keep type="button" always — submit manually to bypass Gentelella's form
  // submit event interception (it calls preventDefault on the submit event).
  nextBtn.textContent = step === total - 1 ? 'Create Participant' : 'Next →';
}

// ── Navigation ─────────────────────────────────────────────────────────────
nextBtn.addEventListener('click', () => {
  if (!validateStep(step)) return;
  if (step < total - 1) {
    step++;
    if (step === 2) checkPeppol();          // Step 3: auto-detect Peppol
    if (step === total - 1) populateReview();
    render();
  } else {
    // Directly call form.submit() — skips the 'submit' event entirely so
    // Gentelella cannot intercept and cancel it.
    document.getElementById('wizard-form').submit();
  }
});
prevBtn.addEventListener('click', () => { if (step > 0) { step--; render(); } });
</script>
