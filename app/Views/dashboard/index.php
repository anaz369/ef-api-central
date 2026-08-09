<div class="page-header">
  <div class="page-header-row">
    <div>
      <div class="page-pretitle">Overview</div>
      <h1 class="page-title">Dashboard</h1>
    </div>
  </div>
</div>

<!-- STAT CARDS -->
<div class="row col-3">
  <div class="card">
    <div class="stat">
      <div class="stat-icon teal">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      </div>
      <div class="stat-content">
        <div class="stat-label">Participants</div>
        <div class="stat-value-row">
          <span class="stat-value"><?= $stats['participants'] ?? 0 ?></span>
        </div>
        <div class="stat-subtext"><?= $stats['active'] ?? 0 ?> active</div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="stat">
      <div class="stat-icon blue">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 21V3h14v18l-3-2-3 2-3-2-3 2-2-2z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
      </div>
      <div class="stat-content">
        <div class="stat-label">Invoices Sent</div>
        <div class="stat-value-row">
          <span class="stat-value"><?= $stats['invoices_sent'] ?? 0 ?></span>
        </div>
        <div class="stat-subtext">Total via Peppol</div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="stat">
      <div class="stat-icon purple" style="background:var(--purple-light,#ede9fe);color:var(--purple,#7c3aed)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
      </div>
      <div class="stat-content">
        <div class="stat-label">Production</div>
        <div class="stat-value-row">
          <span class="stat-value"><?= $stats['production'] ?? 0 ?></span>
        </div>
        <div class="stat-subtext">Live network access</div>
      </div>
    </div>
  </div>
</div>

<!-- RECENT ACTIVITY -->
<div class="row col-1" style="margin-top:24px">
  <div class="card">
    <div class="card-header">
      <div class="card-title">Recent Invoices</div>
    </div>
    <div class="card-body" style="padding:0">
      <table class="table">
        <thead>
          <tr>
            <th>Invoice #</th>
            <th>Company</th>
            <th>Receiver</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($recent_invoices)): ?>
            <?php foreach ($recent_invoices as $inv): ?>
            <tr>
              <td><?= htmlspecialchars($inv['invoice_number']) ?></td>
              <td><?= htmlspecialchars($inv['company_name']) ?></td>
              <td><?= htmlspecialchars($inv['receiver_id']) ?></td>
              <td><?= number_format($inv['amount'], 2) ?></td>
              <td><span class="badge badge-<?= $inv['status'] === 'sent' ? 'teal' : 'red' ?>"><?= $inv['status'] ?></span></td>
              <td><?= $inv['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:32px">No invoices yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
