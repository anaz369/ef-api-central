<?php
$methodColors = ['POST' => 'purple', 'GET' => 'blue', 'PUT' => 'yellow', 'DELETE' => 'red'];
$envMap = [0 => ['Dev','blue'], 1 => ['Prod','green']];
?>
<div class="page-header">
  <div class="page-header-row">
    <div>
      <div class="page-pretitle">Developer</div>
      <h1 class="page-title">API Logs</h1>
    </div>
  </div>
</div>

<!-- Filters -->
<div class="card">
  <form method="get" action="<?= base_url('logs') ?>" style="display:flex;gap:10px;align-items:flex-end;padding:14px 16px;flex-wrap:wrap;">
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
    <div class="form-group" style="margin:0;min-width:110px;">
      <label class="form-label" style="font-size:12px">Status</label>
      <select name="status" class="form-control" style="height:34px;font-size:13px">
        <option value="">All</option>
        <option value="1" <?= ($filters['status'] === '1') ? 'selected' : '' ?>>Success</option>
        <option value="0" <?= ($filters['status'] === '0') ? 'selected' : '' ?>>Failed</option>
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
    <a href="<?= base_url('logs') ?>" class="btn btn-ghost btn-sm" style="height:34px">Reset</a>
    <span style="margin-left:auto;font-size:12px;color:var(--text-muted);line-height:34px"><?= count($logs) ?> records</span>
  </form>
</div>

<!-- Table -->
<div class="card" style="margin-top:12px">
  <?php if (empty($logs)): ?>
  <div style="text-align:center;padding:3rem;color:var(--text-muted);">
    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="opacity:.3;margin-bottom:1rem;"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    <p>No API logs yet.</p>
  </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-vcenter">
      <thead>
        <tr>
          <th style="width:140px">Time</th>
          <th style="width:60px">Method</th>
          <th>Endpoint</th>
          <th>Participant</th>
          <th style="width:60px">Code</th>
          <th style="width:70px">Status</th>
          <th style="width:60px">Env</th>
          <th style="width:80px">IP</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($logs as $log):
          $methodColor = $methodColors[$log['method']] ?? 'secondary';
          [$envLabel, $envColor] = $envMap[$log['environment']] ?? ['—','gray'];
          $isSuccess = (int)$log['status'] === 1;
          $code = (int)($log['response_code'] ?? 0);
          $codeColor = $code >= 200 && $code < 300 ? 'green' : ($code >= 400 ? 'red' : 'yellow');
        ?>
        <tr>
          <td style="font-size:12px;color:var(--text-muted)">
            <?= date('d M H:i:s', strtotime($log['created_at'])) ?>
          </td>
          <td>
            <span class="status status-<?= $methodColor ?>" style="font-size:10px;font-family:monospace"><?= $log['method'] ?></span>
          </td>
          <td style="font-family:monospace;font-size:12px"><?= htmlspecialchars($log['endpoint']) ?></td>
          <td style="font-size:13px"><?= htmlspecialchars($log['participant_name'] ?? '—') ?></td>
          <td>
            <span class="status status-<?= $codeColor ?>" style="font-size:11px;font-family:monospace"><?= $code ?: '—' ?></span>
          </td>
          <td>
            <span class="status status-<?= $isSuccess ? 'green' : 'red' ?>">
              <?= $isSuccess ? 'OK' : 'Fail' ?>
            </span>
          </td>
          <td>
            <span class="status status-<?= $envColor ?>" style="font-size:10px"><?= $envLabel ?></span>
          </td>
          <td style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
