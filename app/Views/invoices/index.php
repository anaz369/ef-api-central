<?php
use App\Models\InvoiceModel;

$txMap  = InvoiceModel::$txLabels;
$dirMap = [0 => ['Inbound','azure'], 1 => ['Outbound','purple']];
$envMap = [0 => ['Dev','blue'], 1 => ['Production','green']];
?>
<div class="page-header">
  <div class="page-header-row">
    <div>
      <div class="page-pretitle">Peppol</div>
      <h1 class="page-title">Invoices</h1>
    </div>
  </div>
</div>

<!-- Stats row -->
<div class="row col-5" style="margin-bottom:0">
  <?php
  $statCards = [
    ['Total',    $stats['total'],    'blue',   'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ['Outbound', $stats['outbound'], 'purple', 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8'],
    ['Inbound',  $stats['inbound'],  'azure',  'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
    ['Pending',  $stats['pending'],  'yellow', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['Failed',   $stats['failed'],   'red',    'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
  ];
  foreach ($statCards as [$label, $value, $color, $icon]): ?>
  <div class="card">
    <div class="card-body" style="padding:16px 20px;display:flex;align-items:center;gap:14px;">
      <div style="width:42px;height:42px;border-radius:var(--radius-sm);background:var(--<?= $color ?>-lt,#eff6ff);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--<?= $color ?>,#2563eb)" stroke-width="1.5"><path d="<?= $icon ?>"/></svg>
      </div>
      <div>
        <div style="font-size:22px;font-weight:700;line-height:1"><?= number_format($value) ?></div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:2px"><?= $label ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="card" style="margin-top:16px">
  <form method="get" action="<?= base_url('invoices') ?>" style="display:flex;gap:10px;align-items:flex-end;padding:14px 16px;flex-wrap:wrap;">
    <div class="form-group" style="margin:0;min-width:180px;">
      <label class="form-label" style="font-size:12px">Participant</label>
      <select name="participant_id" class="form-control" style="height:34px;font-size:13px">
        <option value="">All Participants</option>
        <?php foreach ($participants as $p): ?>
        <option value="<?= $p['id'] ?>" <?= ($filters['participant_id'] == $p['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($p['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group" style="margin:0;min-width:130px;">
      <label class="form-label" style="font-size:12px">Direction</label>
      <select name="direction" class="form-control" style="height:34px;font-size:13px">
        <option value="">All</option>
        <option value="1" <?= ($filters['direction'] === '1') ? 'selected' : '' ?>>Outbound</option>
        <option value="0" <?= ($filters['direction'] === '0') ? 'selected' : '' ?>>Inbound</option>
      </select>
    </div>
    <div class="form-group" style="margin:0;min-width:130px;">
      <label class="form-label" style="font-size:12px">Status</label>
      <select name="tx_status" class="form-control" style="height:34px;font-size:13px">
        <option value="">All</option>
        <?php foreach (InvoiceModel::$txLabels as $k => [$label]): ?>
        <option value="<?= $k ?>" <?= ($filters['transmission_status'] == $k && $filters['transmission_status'] !== '') ? 'selected' : '' ?>>
          <?= $label ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group" style="margin:0;min-width:120px;">
      <label class="form-label" style="font-size:12px">Environment</label>
      <select name="environment" class="form-control" style="height:34px;font-size:13px">
        <option value="">All</option>
        <option value="0" <?= ($filters['environment'] === '0') ? 'selected' : '' ?>>Development</option>
        <option value="1" <?= ($filters['environment'] === '1') ? 'selected' : '' ?>>Production</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm" style="height:34px">Filter</button>
    <a href="<?= base_url('invoices') ?>" class="btn btn-ghost btn-sm" style="height:34px">Reset</a>
  </form>
</div>

<!-- Table -->
<div class="card" style="margin-top:12px">
  <?php if (empty($invoices)): ?>
  <div style="text-align:center;padding:3rem;color:var(--text-muted);">
    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="opacity:.3;margin-bottom:1rem;"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    <p>No invoices found.</p>
  </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-vcenter">
      <thead>
        <tr>
          <th>Invoice #</th>
          <th>Participant</th>
          <th>Direction</th>
          <th>Supplier → Buyer</th>
          <th style="text-align:right">Total</th>
          <th>Status</th>
          <th>Env</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($invoices as $inv):
          [$txLabel, $txColor] = $txMap[$inv['transmission_status']] ?? ['Unknown', 'gray'];
          [$dirLabel, $dirColor] = $dirMap[$inv['direction']] ?? ['—', 'gray'];
          [$envLabel, $envColor] = $envMap[$inv['environment']] ?? ['—', 'gray'];
        ?>
        <tr>
          <td style="font-family:monospace;font-size:13px;font-weight:500"><?= htmlspecialchars($inv['invoice_number']) ?></td>
          <td style="font-size:13px"><?= htmlspecialchars($inv['participant_name'] ?? '—') ?></td>
          <td><span class="status status-<?= $dirColor ?>"><?= $dirLabel ?></span></td>
          <td style="font-size:12.5px;color:var(--text-muted)">
            <?= htmlspecialchars($inv['supplier_name'] ?? '—') ?>
            <span style="margin:0 4px;opacity:.4">→</span>
            <?= htmlspecialchars($inv['buyer_name'] ?? '—') ?>
          </td>
          <td style="text-align:right;font-weight:600;font-size:13px">
            <?= $inv['currency'] ?> <?= number_format((float)$inv['total'], 2) ?>
          </td>
          <td><span class="status status-<?= $txColor ?>"><?= $txLabel ?></span></td>
          <td><span class="status status-<?= $envColor ?>" style="font-size:10px"><?= $envLabel ?></span></td>
          <td style="font-size:12px;color:var(--text-muted)"><?= date('d M Y', strtotime($inv['created_at'])) ?></td>
          <td>
            <a href="<?= base_url('invoices/' . $inv['id']) ?>" class="btn btn-ghost btn-sm">View</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
