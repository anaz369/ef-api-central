<?php
// ── Display helpers ──────────────────────────────────────────────────────
$words = array_values(array_filter(explode(' ', trim($participant['name']))));
$ini   = count($words) >= 2
       ? strtoupper(substr($words[0], 0, 1) . substr($words[count($words)-1], 0, 1))
       : strtoupper(substr($participant['name'], 0, 2));

$palette    = ['primary','azure','purple','yellow','red','green','blue'];
$col        = $palette[$participant['id'] % count($palette)];

$statusMap  = [0 => ['Inactive','red'], 1 => ['Active','green'], 2 => ['Pending','yellow'], 3 => ['Deleted','red']];
$statusInfo = $statusMap[$participant['status']] ?? ['Unknown','gray'];

// ── Onboarding completion steps ──────────────────────────────────────────
$steps = [
  ['icon' => 'M5 13l4 4L19 7',                                         'label' => 'Account Created',           'sub' => 'Participant registered in system',       'done' => true],
  ['icon' => 'M21 2l-2 2m-7.61 7.61a5.5 5.5 0 11-7.78 7.78 5.5 5.5 0 017.77-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4', 'label' => 'Development Credentials',   'sub' => 'Test API keys issued',                  'done' => $dev_cred !== null],
  ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'label' => 'Tax ID Verified',           'sub' => 'TRN / TIN validated',                   'done' => (bool)$participant['tin_verified']],
  ['icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z', 'label' => 'Production Access Granted', 'sub' => 'Payment confirmed, live access unlocked', 'done' => (bool)$participant['production_access']],
  ['icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z', 'label' => 'Production Credentials',   'sub' => 'Live API keys issued',                  'done' => $prod_cred !== null],
  ['icon' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9', 'label' => 'Peppol ID Registered',     'sub' => 'SMP registration complete',             'done' => (bool)$participant['peppol_verified'] || ($peppol_info !== null && $peppol_info['status'] === 'active')],
];
$doneCount  = count(array_filter($steps, fn($s) => $s['done']));
$pct        = round($doneCount / count($steps) * 100);
$dashOffset = round(213.6 * (1 - $pct / 100));
$nextIdx    = null;
foreach ($steps as $i => $s) { if (!$s['done']) { $nextIdx = $i; break; } }
?>

<!-- ── Page header ────────────────────────────────────────────────────── -->
<div class="page-header">
  <div class="page-header-row">
    <div>
      <div class="page-pretitle">Participants</div>
      <h1 class="page-title"><?= htmlspecialchars($participant['name']) ?></h1>
    </div>
    <div class="page-actions">
      <a href="<?= base_url('participants') ?>" class="btn btn-outline">← All Participants</a>
      <?php if (!$participant['production_access']): ?>
      <form method="post" action="<?= base_url('participants/' . $participant['id'] . '/grant-production') ?>" style="display:inline">
        <?= csrf_field() ?>
        <button type="button" class="btn btn-primary"
                onclick="if(confirm('Grant production access to this participant?')) this.closest('form').submit()">
          Grant Production Access
        </button>
      </form>
      <?php else: ?>
      <span class="status status-green" style="padding:6px 14px;">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:4px"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        Production Active
      </span>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ── Flash banners ──────────────────────────────────────────────────── -->
<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success" style="margin-bottom:16px"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger" style="margin-bottom:16px"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<?php if ($new_secret): ?>
<div class="alert" style="background:var(--green-lt,#ecfdf5);border:1px solid var(--green,#22c55e);border-radius:var(--radius);padding:1.25rem;margin-bottom:16px">
  <div style="font-weight:600;margin-bottom:.5rem;color:var(--green)">
    <?= htmlspecialchars($new_env) ?> credentials generated — copy the secret now, it will not be shown again.
  </div>
  <div style="display:flex;align-items:center;gap:10px;background:rgba(0,0,0,.06);padding:.75rem 1rem;border-radius:6px;">
    <span style="font-family:monospace;font-size:.9rem;word-break:break-all;flex:1">
      <strong>Client Secret:</strong> <?= htmlspecialchars($new_secret) ?>
    </span>
    <button type="button" class="btn btn-sm"
            style="flex-shrink:0;background:var(--green);color:#fff;border-color:transparent"
            data-copy="<?= htmlspecialchars($new_secret) ?>"
            title="Copy secret">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
      Copy
    </button>
  </div>
</div>
<?php endif; ?>

<!-- ── Row 1: Avatar summary + Company details ───────────────────────── -->
<div class="row col-4-8" style="margin-bottom:0">

  <!-- Avatar + completion ring -->
  <div class="card">
    <div class="card-body" style="text-align:center;padding:24px 16px">
      <div style="width:80px;height:80px;border-radius:50%;background:var(--<?= $col ?>);margin:0 auto 12px;display:flex;align-items:center;justify-content:center;color:white;font-size:28px;font-weight:600">
        <?= $ini ?>
      </div>
      <div style="font-size:15px;font-weight:600;color:var(--text)"><?= htmlspecialchars($participant['name']) ?></div>
      <div style="font-size:12.5px;color:var(--text-muted);margin-top:3px"><?= htmlspecialchars($participant['email']) ?></div>
      <div style="margin-top:10px;display:flex;gap:6px;justify-content:center;flex-wrap:wrap">
        <span class="status status-<?= $statusInfo[1] ?>"><?= $statusInfo[0] ?></span>
        <?php if ($participant['integration_mode'] == 1): ?>
          <span class="status status-purple">Self-Integration</span>
        <?php else: ?>
          <span class="status status-blue">Managed</span>
        <?php endif; ?>
      </div>
    </div>

    <div style="border-top:1px solid var(--border-color-light)">
      <div class="profile-ring-box" style="padding:16px">
        <div class="ring-wrap">
          <svg viewBox="0 0 80 80">
            <circle class="ring-bg" cx="40" cy="40" r="34"/>
            <circle class="ring-fill" cx="40" cy="40" r="34" stroke-dashoffset="<?= $dashOffset ?>"/>
          </svg>
          <div class="ring-center"><?= $pct ?><span>%</span></div>
        </div>
        <div class="note">Onboarding completion</div>
        <?php if ($nextIdx !== null): ?>
        <a href="#completion-steps" class="ring-link">Next: <?= $steps[$nextIdx]['label'] ?> →</a>
        <?php else: ?>
        <span class="ring-link" style="color:var(--green)">All steps complete ✓</span>
        <?php endif; ?>
      </div>
    </div>

    <div style="border-top:1px solid var(--border-color-light);padding:14px 16px">
      <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:10px">Account Status</div>
      <form method="post" action="<?= base_url('participants/' . $participant['id'] . '/status') ?>" style="display:flex;gap:6px;">
        <?= csrf_field() ?>
        <select name="status" class="form-control" style="flex:1;height:32px;font-size:13px;">
          <option value="1" <?= $participant['status'] == 1 ? 'selected' : '' ?>>Active</option>
          <option value="0" <?= $participant['status'] == 0 ? 'selected' : '' ?>>Inactive</option>
          <option value="2" <?= $participant['status'] == 2 ? 'selected' : '' ?>>Pending</option>
        </select>
        <button type="button" class="btn btn-primary btn-sm"
                onclick="this.closest('form').submit()">Save</button>
      </form>
    </div>
  </div>

  <!-- Company details -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Company Details</div>
        <div class="card-subtitle">Registration and contact information</div>
      </div>
    </div>
    <div class="card-body p-0">
      <div style="display:grid;grid-template-columns:1fr 1fr">
        <?php
        $details = [
          ['Email',          $participant['email']],
          ['Phone',          $participant['phone'] ?: '—'],
          ['Country',        $participant['country'] ?: '—'],
          ['Emirate',        $participant['emirate'] ?: '—'],
          ['City',           $participant['city'] ?: '—'],
          ['Legal Form',     $participant['legal_form'] ?: '—'],
          ['TRN',            $participant['trn'] ?: '—'],
          ['TIN',            $participant['tin_id'] ?: '—'],
          ['Trade License',  $participant['trade_license'] ?: '—'],
          ['Joined',         date('d M Y', strtotime($participant['created_at']))],
        ];
        foreach ($details as [$label, $value]):
        ?>
        <div style="padding:11px 20px;border-bottom:1px solid var(--border-color-light);border-right:1px solid var(--border-color-light)">
          <div style="font-size:11px;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px"><?= $label ?></div>
          <div style="font-size:13.5px;font-weight:500;color:var(--text)"><?= htmlspecialchars($value) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>

<!-- ── Peppol Identity card ───────────────────────────────────────────── -->
<?php
$peppolLinked   = $participant['peppol_participant_id'] !== null;
$peppolStatusMap = [
    'active'   => ['Active',   'green'],
    'pending'  => ['Pending',  'yellow'],
    'delinked' => ['Delinked', 'red'],
];
$peppolStatusInfo = $peppol_info ? ($peppolStatusMap[$peppol_info['status']] ?? ['Unknown','gray']) : null;
?>
<div class="card" style="margin-top:16px" id="peppol-identity-card">
  <div class="card-header">
    <div>
      <div class="card-title">Peppol Identity</div>
      <div class="card-subtitle">FTA onboarding registry link</div>
    </div>
    <?php if ($peppolLinked && $peppolStatusInfo): ?>
      <span class="status status-<?= $peppolStatusInfo[1] ?>"><?= $peppolStatusInfo[0] ?></span>
    <?php elseif ($peppolLinked): ?>
      <span class="status status-gray">Unknown</span>
    <?php else: ?>
      <span class="status status-gray">Not Linked</span>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <?php if ($peppolLinked && $peppol_info): ?>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div>
          <div style="font-size:11px;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.04em;margin-bottom:3px">Peppol ID</div>
          <div style="font-family:monospace;font-size:13.5px;font-weight:500"><?= htmlspecialchars($peppol_info['peppol_id']) ?></div>
        </div>
        <div>
          <div style="font-size:11px;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.04em;margin-bottom:3px">Registered Entity</div>
          <div style="font-size:13.5px;font-weight:500"><?= htmlspecialchars($peppol_info['entity_name_en'] ?? '—') ?></div>
        </div>
        <?php if (!empty($peppol_info['linked_at'])): ?>
        <div>
          <div style="font-size:11px;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.04em;margin-bottom:3px">Linked On</div>
          <div style="font-size:13.5px"><?= date('d M Y', strtotime($peppol_info['linked_at'])) ?></div>
        </div>
        <?php endif; ?>
      </div>
      <div style="margin-top:1rem;padding-top:.75rem;border-top:1px solid var(--border-color-light);display:flex;justify-content:flex-end;">
        <button type="button" class="btn btn-ghost btn-sm" id="btn-recheck-peppol">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
          Refresh Status
        </button>
      </div>
    <?php else: ?>
      <div style="padding:.75rem 0;">
        <?php if ($participant['trn']): ?>
          <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:1rem;">
            No Peppol record linked yet. Click below to check the FTA registry using TRN <strong><?= htmlspecialchars($participant['trn']) ?></strong>.
          </p>
          <button type="button" class="btn btn-primary btn-sm" id="btn-recheck-peppol">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Check &amp; Link Peppol
          </button>
        <?php else: ?>
          <p style="color:var(--text-muted);font-size:.9rem;">No TRN on file — add a TRN first to enable Peppol auto-linking.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <div id="peppol-link-msg" style="display:none;margin-top:.75rem;"></div>
  </div>
</div>

<!-- ── Row 2: Completion timeline ────────────────────────────────────── -->
<div id="completion-steps" class="card" style="margin-top:16px">
  <div class="card-header">
    <div>
      <div class="card-title">Onboarding Journey</div>
      <div class="card-subtitle"><?= $doneCount ?> of <?= count($steps) ?> steps completed</div>
    </div>
    <span class="status status-<?= $pct === 100 ? 'green' : ($pct >= 50 ? 'blue' : 'yellow') ?>">
      <?= $pct ?>% complete
    </span>
  </div>
  <div class="card-body">
    <div class="timeline">
      <?php
      $tlColors = ['is-green', 'is-blue', 'is-yellow', 'is-primary', 'is-purple', ''];
      foreach ($steps as $i => $step):
        $isNext = ($i === $nextIdx);
        if ($step['done']) {
          $cls = 'is-green';
        } elseif ($isNext) {
          $cls = 'is-yellow';
        } else {
          $cls = '';
        }
      ?>
      <div class="timeline-item <?= $cls ?>">
        <div class="ti-time">
          <?php if ($step['done']): ?>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
            Done
          <?php elseif ($isNext): ?>
            Next step
          <?php else: ?>
            Pending
          <?php endif; ?>
        </div>
        <div class="ti-title">
          <strong><?= htmlspecialchars($step['label']) ?></strong>
          <?php if ($isNext && !$step['done']): ?>
            <span style="font-size:11px;font-weight:400;margin-left:6px;color:var(--yellow);">← action required</span>
          <?php endif; ?>
        </div>
        <div class="ti-desc"><?= htmlspecialchars($step['sub']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ── Row 3: API Credentials ─────────────────────────────────────────── -->
<div class="row col-2" style="margin-top:16px" id="credentials">

  <!-- Development credentials -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Development Credentials</div>
        <div class="card-subtitle">For Peppol testbed &amp; integration testing</div>
      </div>
      <span class="status status-blue">Testbed</span>
    </div>
    <div class="card-body">
      <?php if ($dev_cred): ?>
        <div class="form-group">
          <label class="form-label">Client ID</label>
          <div style="display:flex;gap:6px;">
            <input type="text" class="form-control" id="dev-client-id"
                   value="<?= htmlspecialchars($dev_cred['client_id']) ?>"
                   readonly style="font-family:monospace;font-size:.82rem;">
            <button type="button" class="btn btn-ghost btn-sm"
                    data-copy="<?= htmlspecialchars($dev_cred['client_id']) ?>"
                    title="Copy Client ID">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
            </button>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Client Secret</label>
          <input type="text" class="form-control"
                 value="<?= htmlspecialchars($dev_cred['client_secret_preview']) ?>"
                 readonly style="font-family:monospace;color:var(--text-muted);">
          <small class="form-hint">Hashed — last 6 chars shown for identification only.</small>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px">
          <span style="font-size:12px;color:var(--text-muted)">
            Last used: <?= $dev_cred['last_used_at'] ? date('d M Y H:i', strtotime($dev_cred['last_used_at'])) : 'Never' ?>
          </span>
          <form method="post" action="<?= base_url('participants/' . $participant['id'] . '/generate-credentials') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="environment" value="0">
            <button type="button" class="btn btn-ghost btn-sm"
                    onclick="if(confirm('This will revoke the existing development credentials. Continue?')) this.closest('form').submit()">
              Regenerate
            </button>
          </form>
        </div>
      <?php else: ?>
        <div style="padding:1.5rem 0;text-align:center;color:var(--text-muted);">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="opacity:.4;margin-bottom:.75rem;"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 11-7.78 7.78 5.5 5.5 0 017.77-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
          <p style="font-size:.9rem;margin-bottom:1rem;">No development credentials yet.</p>
          <form method="post" action="<?= base_url('participants/' . $participant['id'] . '/generate-credentials') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="environment" value="0">
            <button type="button" class="btn btn-primary"
                    onclick="this.closest('form').submit()">Generate Dev Credentials</button>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Production credentials -->
  <div class="card" <?= !$participant['production_access'] ? 'style="opacity:.5;pointer-events:none;"' : '' ?>>
    <div class="card-header">
      <div>
        <div class="card-title">Production Credentials</div>
        <div class="card-subtitle">For the live Peppol network</div>
      </div>
      <?php if ($participant['production_access']): ?>
        <span class="status status-green">Live</span>
      <?php else: ?>
        <span class="status status-gray">Locked</span>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <?php if (!$participant['production_access']): ?>
        <div style="padding:1.5rem 0;text-align:center;color:var(--text-muted);">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="opacity:.4;margin-bottom:.75rem;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          <p style="font-size:.9rem;">Grant production access first to unlock credentials.</p>
        </div>
      <?php elseif ($prod_cred): ?>
        <div class="form-group">
          <label class="form-label">Client ID</label>
          <div style="display:flex;gap:6px;">
            <input type="text" class="form-control"
                   value="<?= htmlspecialchars($prod_cred['client_id']) ?>"
                   readonly style="font-family:monospace;font-size:.82rem;">
            <button type="button" class="btn btn-ghost btn-sm"
                    data-copy="<?= htmlspecialchars($prod_cred['client_id']) ?>"
                    title="Copy Client ID">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
            </button>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Client Secret</label>
          <input type="text" class="form-control"
                 value="<?= htmlspecialchars($prod_cred['client_secret_preview']) ?>"
                 readonly style="font-family:monospace;color:var(--text-muted);">
          <small class="form-hint">Hashed — last 6 chars shown for identification only.</small>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px">
          <span style="font-size:12px;color:var(--text-muted)">
            Last used: <?= $prod_cred['last_used_at'] ? date('d M Y H:i', strtotime($prod_cred['last_used_at'])) : 'Never' ?>
          </span>
          <form method="post" action="<?= base_url('participants/' . $participant['id'] . '/generate-credentials') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="environment" value="1">
            <button type="button" class="btn btn-ghost btn-sm"
                    onclick="if(confirm('This will revoke the existing production credentials. Continue?')) this.closest('form').submit()">
              Regenerate
            </button>
          </form>
        </div>
      <?php else: ?>
        <div style="padding:1.5rem 0;text-align:center;color:var(--text-muted);">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="opacity:.4;margin-bottom:.75rem;"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 11-7.78 7.78 5.5 5.5 0 017.77-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
          <p style="font-size:.9rem;margin-bottom:1rem;">Production access granted. Generate credentials now.</p>
          <form method="post" action="<?= base_url('participants/' . $participant['id'] . '/generate-credentials') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="environment" value="1">
            <button type="button" class="btn btn-primary"
                    onclick="this.closest('form').submit()">Generate Prod Credentials</button>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<script type="module">
import { t as showToast } from '<?= base_url('assets/js/toast-CBtjS_PZ.js') ?>';

document.querySelectorAll('[data-copy]').forEach(btn => {
  btn.addEventListener('click', () => {
    navigator.clipboard.writeText(btn.dataset.copy).then(() => {
      showToast('Copied to clipboard', { variant: 'success' });
    }).catch(() => {
      showToast('Copy failed — please copy manually', { variant: 'warning' });
    });
  });
});

// ── Peppol re-check / link ─────────────────────────────────────────────────
const recheckBtn = document.getElementById('btn-recheck-peppol');
if (recheckBtn) {
  recheckBtn.addEventListener('click', async () => {
    const msgEl = document.getElementById('peppol-link-msg');
    recheckBtn.disabled    = true;
    recheckBtn.textContent = 'Checking…';

    try {
      const resp = await fetch('<?= base_url('participants/' . $participant['id'] . '/link-peppol') ?>', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type':     'application/x-www-form-urlencoded',
        },
        body: '<?= csrf_token() ?>=' + encodeURIComponent('<?= csrf_hash() ?>'),
      });
      const data = await resp.json();

      if (data.success) {
        showToast('Peppol identity linked — refreshing…', { variant: 'success' });
        setTimeout(() => location.reload(), 1200);
      } else {
        msgEl.innerHTML    = `<div class="alert alert-warning" style="margin:0">${data.message}</div>`;
        msgEl.style.display = '';
        recheckBtn.disabled    = false;
        recheckBtn.textContent = 'Check &amp; Link Peppol';
      }
    } catch {
      msgEl.innerHTML    = `<div class="alert alert-danger" style="margin:0">Request failed. Please try again.</div>`;
      msgEl.style.display = '';
      recheckBtn.disabled    = false;
      recheckBtn.textContent = 'Check &amp; Link Peppol';
    }
  });
}
</script>
