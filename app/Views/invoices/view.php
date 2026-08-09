<?php
use App\Models\InvoiceModel;
use App\Models\InvoiceTransmissionModel;

$txInfo  = InvoiceModel::$txLabels[$invoice['transmission_status']] ?? ['Unknown','gray'];
$dirMap  = [0 => ['Inbound','azure'], 1 => ['Outbound','purple']];
$envMap  = [0 => ['Development','blue'], 1 => ['Production','green']];
$dirInfo = $dirMap[$invoice['direction']] ?? ['—','gray'];
$envInfo = $envMap[$invoice['environment']] ?? ['—','gray'];
$docType = InvoiceModel::$docTypeLabels[$invoice['document_type']] ?? 'Invoice';
?>
<div class="page-header">
  <div class="page-header-row">
    <div>
      <div class="page-pretitle"><a href="<?= base_url('invoices') ?>">Invoices</a></div>
      <h1 class="page-title"><?= htmlspecialchars($invoice['invoice_number']) ?></h1>
    </div>
    <div class="page-actions">
      <a href="<?= base_url('invoices') ?>" class="btn btn-outline">← All Invoices</a>
    </div>
  </div>
</div>

<!-- Row 1: Summary card + Party card -->
<div class="row col-4-8">

  <!-- Summary -->
  <div class="card">
    <div class="card-body" style="text-align:center;padding:20px 16px">
      <div style="font-size:26px;font-weight:700;margin-bottom:6px">
        <?= $invoice['currency'] ?> <?= number_format((float)$invoice['total'], 2) ?>
      </div>
      <div style="font-size:13px;color:var(--text-muted);margin-bottom:12px"><?= $docType ?></div>
      <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap">
        <span class="status status-<?= $txInfo[1] ?>"><?= $txInfo[0] ?></span>
        <span class="status status-<?= $dirInfo[1] ?>"><?= $dirInfo[0] ?></span>
        <span class="status status-<?= $envInfo[1] ?>" style="font-size:10px"><?= $envInfo[0] ?></span>
      </div>
    </div>
    <div style="border-top:1px solid var(--border-color-light)">
      <?php
      $details = [
        ['Invoice #',   $invoice['invoice_number']],
        ['Issue Date',  $invoice['issue_date'] ? date('d M Y', strtotime($invoice['issue_date'])) : '—'],
        ['Due Date',    $invoice['due_date']   ? date('d M Y', strtotime($invoice['due_date']))   : '—'],
        ['Currency',    $invoice['currency']],
        ['Participant', $participant['name'] ?? '—'],
      ];
      foreach ($details as [$label, $value]): ?>
      <div style="display:flex;justify-content:space-between;padding:9px 16px;border-bottom:1px solid var(--border-color-light);font-size:13px">
        <span style="color:var(--text-muted)"><?= $label ?></span>
        <span style="font-weight:500"><?= htmlspecialchars($value) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Parties + Amounts -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">Parties</div>
    </div>
    <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;padding:1rem 1.25rem">
      <div>
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:6px">Supplier</div>
        <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($invoice['supplier_name'] ?? '—') ?></div>
        <?php if ($invoice['supplier_peppol_id']): ?>
        <div style="font-family:monospace;font-size:12px;color:var(--text-muted);margin-top:3px"><?= htmlspecialchars($invoice['supplier_peppol_id']) ?></div>
        <?php endif; ?>
      </div>
      <div>
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:6px">Buyer</div>
        <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($invoice['buyer_name'] ?? '—') ?></div>
        <?php if ($invoice['buyer_peppol_id']): ?>
        <div style="font-family:monospace;font-size:12px;color:var(--text-muted);margin-top:3px"><?= htmlspecialchars($invoice['buyer_peppol_id']) ?></div>
        <?php endif; ?>
      </div>
    </div>
    <div style="border-top:1px solid var(--border-color-light)">
      <div class="card-header"><div class="card-title">Amounts</div></div>
      <div style="padding:.5rem 1.25rem 1rem">
        <?php
        $amounts = [
          ['Subtotal',        $invoice['subtotal']],
          ['Discount',        '-' . $invoice['discount_amount']],
          ['Charge',          $invoice['charge_amount']],
          ['Tax (VAT)',        $invoice['tax_amount']],
        ];
        foreach ($amounts as [$label, $value]): ?>
        <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px;border-bottom:1px solid var(--border-color-light)">
          <span style="color:var(--text-muted)"><?= $label ?></span>
          <span><?= $invoice['currency'] ?> <?= is_numeric($value) ? number_format((float)$value, 2) : $value ?></span>
        </div>
        <?php endforeach; ?>
        <div style="display:flex;justify-content:space-between;padding:8px 0 0;font-size:15px;font-weight:700">
          <span>Total</span>
          <span><?= $invoice['currency'] ?> <?= number_format((float)$invoice['total'], 2) ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Transmission timeline -->
<div class="card" style="margin-top:16px">
  <div class="card-header">
    <div>
      <div class="card-title">Transmission Timeline</div>
      <div class="card-subtitle"><?= count($transmissions) ?> event<?= count($transmissions) !== 1 ? 's' : '' ?></div>
    </div>
  </div>
  <div class="card-body">
    <?php if (empty($transmissions)): ?>
    <p style="color:var(--text-muted);font-size:.9rem">No transmission events yet.</p>
    <?php else: ?>
    <div class="timeline">
      <?php foreach ($transmissions as $tx):
        $eventLabel = InvoiceTransmissionModel::$eventLabels[$tx['event']] ?? 'Event';
        $levelColor = InvoiceTransmissionModel::$levelColors[$tx['level']] ?? 'blue';
        $cls = 'is-' . $levelColor;
      ?>
      <div class="timeline-item <?= $cls ?>">
        <div class="ti-time"><?= date('d M H:i', strtotime($tx['created_at'])) ?></div>
        <div class="ti-title"><strong><?= $eventLabel ?></strong></div>
        <?php if ($tx['message']): ?>
        <div class="ti-desc"><?= htmlspecialchars($tx['message']) ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
