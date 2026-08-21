<?php
$baseUrl = rtrim(base_url(), '/');
$apiBase = $baseUrl;
?>

<style>
/* ── API Catalog Layout ─────────────────────────────────────────────────── */
.catalog-wrap {
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 24px;
  align-items: start;
}
.catalog-toc {
  position: sticky;
  top: 80px;
  background: var(--bg-surface, #fff);
  border: 1px solid var(--border-color-light);
  border-radius: var(--radius, 8px);
  padding: 16px 0;
}
.toc-section {
  padding: 6px 16px 2px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .08em;
  color: var(--text-muted);
}
.toc-link {
  display: block;
  padding: 6px 16px;
  font-size: 13px;
  color: var(--text-muted);
  text-decoration: none;
  border-left: 2px solid transparent;
  transition: all .15s;
}
.toc-link:hover, .toc-link.active {
  color: var(--primary, #2563eb);
  border-left-color: var(--primary, #2563eb);
  background: var(--primary-lt, #eff6ff);
}
.toc-link.sub { padding-left: 28px; font-size: 12px; }

/* ── Section anchors ────────────────────────────────────────────────────── */
.api-section { margin-bottom: 32px; }
.api-section-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 16px;
  padding-bottom: 10px;
  border-bottom: 2px solid var(--border-color-light);
  display: flex;
  align-items: center;
  gap: 10px;
}
.api-section-title svg { color: var(--primary, #2563eb); }

/* ── Endpoint card ──────────────────────────────────────────────────────── */
.endpoint-card {
  border: 1px solid var(--border-color-light);
  border-radius: var(--radius, 8px);
  margin-bottom: 20px;
  overflow: hidden;
}
.endpoint-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 18px;
  background: var(--bg-surface-secondary, #f8fafc);
  border-bottom: 1px solid var(--border-color-light);
  flex-wrap: wrap;
}
.method-badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 10px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .06em;
}
.method-post { background: #dcfce7; color: #15803d; }
.method-get  { background: #dbeafe; color: #1d4ed8; }
.endpoint-path {
  font-family: monospace;
  font-size: 14px;
  font-weight: 600;
  color: var(--text);
  flex: 1;
}
.endpoint-desc {
  padding: 12px 18px;
  font-size: 13px;
  color: var(--text-muted);
  border-bottom: 1px solid var(--border-color-light);
  line-height: 1.6;
}

/* ── Code tabs ──────────────────────────────────────────────────────────── */
.code-tabs { padding: 14px 18px; }
.tab-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: var(--text-muted);
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.tab-label .ct { font-size: 11px; color: var(--text-muted); }
.tab-row { display: flex; gap: 0; border-bottom: 1px solid var(--border-color-light); margin-bottom: 10px; }
.tab-btn {
  padding: 6px 14px;
  font-size: 12px;
  font-weight: 500;
  border: none;
  background: none;
  cursor: pointer;
  color: var(--text-muted);
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
  transition: all .15s;
}
.tab-btn.active { color: var(--primary, #2563eb); border-bottom-color: var(--primary, #2563eb); }
.tab-pane { display: none; }
.tab-pane.active { display: block; }
pre.code-block {
  background: var(--bg-surface-secondary, #f8fafc);
  border: 1px solid var(--border-color-light);
  border-radius: 6px;
  padding: 14px 16px;
  font-size: 12.5px;
  font-family: 'JetBrains Mono', 'Fira Code', monospace;
  line-height: 1.6;
  overflow-x: auto;
  margin: 0;
  position: relative;
}
.copy-code-btn {
  position: absolute;
  top: 8px;
  right: 8px;
  padding: 3px 8px;
  font-size: 11px;
  background: var(--bg-surface, #fff);
  border: 1px solid var(--border-color-light);
  border-radius: 4px;
  cursor: pointer;
  color: var(--text-muted);
}
.copy-code-btn:hover { color: var(--primary); }

/* ── Response codes ─────────────────────────────────────────────────────── */
.response-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.response-table th { text-align: left; padding: 6px 10px; font-size: 11px; text-transform: uppercase; letter-spacing:.06em; color: var(--text-muted); border-bottom: 1px solid var(--border-color-light); }
.response-table td { padding: 8px 10px; border-bottom: 1px solid var(--border-color-light); }
.code-201 { color: #15803d; font-weight: 700; font-family: monospace; }
.code-400 { color: #b45309; font-weight: 700; font-family: monospace; }
.code-401 { color: #dc2626; font-weight: 700; font-family: monospace; }
.code-422 { color: #7c3aed; font-weight: 700; font-family: monospace; }
.code-502 { color: #dc2626; font-weight: 700; font-family: monospace; }
.code-200 { color: #15803d; font-weight: 700; font-family: monospace; }

/* ── Try API panel ──────────────────────────────────────────────────────── */
.try-toggle {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 18px;
  background: var(--primary-lt, #eff6ff);
  border-top: 1px solid var(--border-color-light);
  cursor: pointer;
  user-select: none;
}
.try-toggle:hover { background: var(--primary-lt-hover, #dbeafe); }
.try-toggle-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--primary, #2563eb);
  display: flex;
  align-items: center;
  gap: 8px;
}
.try-panel {
  display: none;
  padding: 18px;
  border-top: 1px solid var(--border-color-light);
  background: var(--bg-surface, #fff);
}
.try-panel.open { display: block; }
.try-field { margin-bottom: 14px; }
.try-field label { display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 5px; text-transform: uppercase; letter-spacing: .05em; }
.try-field input, .try-field textarea, .try-field select {
  width: 100%;
  font-family: monospace;
  font-size: 12.5px;
  padding: 8px 10px;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--bg-surface-secondary, #f8fafc);
  color: var(--text);
  box-sizing: border-box;
}
.try-field textarea { resize: vertical; min-height: 200px; }
.try-actions { display: flex; align-items: center; gap: 10px; margin-top: 14px; }
.try-result { margin-top: 14px; display: none; }
.try-result.show { display: block; }
.try-result-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 6px;
  font-size: 12px;
  font-weight: 600;
}
.result-status { padding: 2px 8px; border-radius: 4px; font-family: monospace; }
.result-ok   { background: #dcfce7; color: #15803d; }
.result-err  { background: #fee2e2; color: #dc2626; }
.try-spinner { display: none; width: 16px; height: 16px; border: 2px solid var(--border-color); border-top-color: var(--primary); border-radius: 50%; animation: spin .7s linear infinite; }

/* ── Base URL card ──────────────────────────────────────────────────────── */
.base-url-row {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg-surface-secondary, #f8fafc);
  border: 1px solid var(--border-color-light);
  border-radius: 6px;
  padding: 10px 14px;
  font-family: monospace;
  font-size: 13px;
}
.base-url-row .scheme { color: var(--text-muted); }
.base-url-row .host   { color: var(--primary); font-weight: 600; }

@keyframes spin { to { transform: rotate(360deg); } }

/* ── Info alert ─────────────────────────────────────────────────────────── */
.api-info-box {
  background: var(--primary-lt, #eff6ff);
  border: 1px solid var(--primary-border, #bfdbfe);
  border-radius: 6px;
  padding: 12px 16px;
  font-size: 13px;
  line-height: 1.6;
  margin-bottom: 16px;
}
.api-info-box strong { color: var(--primary, #2563eb); }
</style>

<!-- Page header -->
<div class="page-header">
  <div>
    <h1 class="page-title">API Catalog</h1>
    <p class="page-subtitle">Reference documentation for the Ethicfin eInvoicing API</p>
  </div>
  <div class="page-actions">
    <span class="status status-green" style="padding:6px 14px;">
      <span style="width:7px;height:7px;border-radius:50%;background:currentColor;display:inline-block;margin-right:6px;"></span>
      API Live
    </span>
  </div>
</div>

<div class="catalog-wrap">

  <!-- ── Left TOC ───────────────────────────────────────────────────────── -->
  <div class="catalog-toc">
    <div class="toc-section">Getting Started</div>
    <a class="toc-link" href="#overview">Overview</a>
    <a class="toc-link" href="#authentication">Authentication</a>
    <a class="toc-link" href="#errors">Error Format</a>

    <div class="toc-section" style="margin-top:10px;">Endpoints</div>
    <a class="toc-link" href="#auth-token">Get Access Token</a>
    <a class="toc-link" href="#submit-invoice">Submit Invoice</a>

    <div class="toc-section" style="margin-top:10px;">Invoice Modes</div>
    <a class="toc-link sub" href="#mode-1">Mode 1 — Invoice</a>
    <a class="toc-link sub" href="#mode-3">Mode 3 — Credit Note</a>
    <a class="toc-link sub" href="#mode-4">Mode 4 — SB Invoice</a>
    <a class="toc-link sub" href="#mode-5">Mode 5 — SB Credit Note</a>
  </div>

  <!-- ── Main content ───────────────────────────────────────────────────── -->
  <div>

    <!-- OVERVIEW -->
    <div class="api-section" id="overview">
      <div class="api-section-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        Overview
      </div>
      <div class="card">
        <div class="card-body">
          <p style="font-size:14px;line-height:1.7;color:var(--text);margin-bottom:16px;">
            The <strong>Ethicfin eInvoicing API</strong> allows ERP systems and third-party applications to programmatically submit UAE PINT-AE compliant eInvoices and credit notes to the Peppol network. All documents are validated against the PINT AE specification before transmission.
          </p>

          <div style="margin-bottom:16px;">
            <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:8px;">Base URL</div>
            <div class="base-url-row">
              <span class="scheme">https://</span>
              <span class="host">einvoicing.ethicfin.com</span>
              <button class="copy-code-btn" style="position:static;" onclick="navigator.clipboard.writeText('https://einvoicing.ethicfin.com').then(()=>showToastMsg('Copied!'))">Copy</button>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:8px;">
            <?php foreach ([
              ['Format', 'JSON (application/json)', '📋'],
              ['Auth',   'OAuth2 Client Credentials', '🔐'],
              ['Version','v1 (current)', '🏷'],
            ] as [$k, $v, $ico]): ?>
            <div style="background:var(--bg-surface-secondary,#f8fafc);border:1px solid var(--border-color-light);border-radius:6px;padding:12px;">
              <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:4px;"><?= $k ?></div>
              <div style="font-size:13px;font-weight:500;color:var(--text);"><?= $ico ?> <?= $v ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- AUTHENTICATION -->
    <div class="api-section" id="authentication">
      <div class="api-section-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        Authentication
      </div>
      <div class="card">
        <div class="card-body">
          <p style="font-size:13.5px;line-height:1.7;color:var(--text);margin-bottom:12px;">
            The API uses <strong>OAuth 2.0 Client Credentials</strong> flow. Use your <code>client_id</code> and <code>client_secret</code> to obtain a short-lived Bearer token, then include it in every API request.
          </p>
          <div class="api-info-box">
            <strong>Flow:</strong> POST /api/auth/token → receive <code>access_token</code> → include as <code>Authorization: Bearer {token}</code> header on all invoice requests. Tokens expire after <strong>1 hour</strong>.
          </div>
          <div style="font-size:12px;color:var(--text-muted);">
            Your <code>client_id</code> and <code>client_secret</code> are issued per participant and can be found in your participant's API credentials section managed by your Ethicfin account manager.
          </div>
        </div>
      </div>
    </div>

    <!-- ERROR FORMAT -->
    <div class="api-section" id="errors">
      <div class="api-section-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Error Format
      </div>
      <div class="card">
        <div class="card-body">
          <p style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">All errors return a consistent JSON structure:</p>
          <div style="position:relative;">
            <pre class="code-block"><code>{
  "success": false,
  "error":   "ERROR_CODE",
  "message": "Human-readable description of what went wrong."
}</code></pre>
          </div>
          <table class="response-table" style="margin-top:14px;">
            <thead><tr><th>Code</th><th>Meaning</th></tr></thead>
            <tbody>
              <tr><td class="code-401">401</td><td>Missing or invalid Bearer token</td></tr>
              <tr><td class="code-422">422</td><td>Validation error — check the <code>message</code> field</td></tr>
              <tr><td class="code-502">502</td><td>Peppol transmission failed (document built OK but AS4 send failed)</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── ENDPOINT: POST /api/auth/token ─────────────────────────────── -->
    <div class="api-section" id="auth-token">
      <div class="api-section-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 11-7.778 7.778 5.5 5.5 0 017.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
        Get Access Token
      </div>

      <div class="endpoint-card">
        <div class="endpoint-header">
          <span class="method-badge method-post">POST</span>
          <span class="endpoint-path">/api/auth/token</span>
          <span style="font-size:12px;color:var(--text-muted);margin-left:auto;">No authentication required</span>
        </div>
        <div class="endpoint-desc">
          Exchange your <code>client_id</code> and <code>client_secret</code> for a Bearer access token. The token is valid for <strong>3600 seconds (1 hour)</strong>.
        </div>

        <div class="code-tabs">
          <!-- Request -->
          <div style="margin-bottom:16px;">
            <div class="tab-label">
              <span>Request Body</span>
              <span class="ct">application/json</span>
            </div>
            <div class="tab-row" id="auth-req-tabs">
              <button class="tab-btn active" onclick="switchTab('auth-req','example',this)">Example</button>
              <button class="tab-btn" onclick="switchTab('auth-req','schema',this)">Schema</button>
            </div>
            <div id="auth-req-example" class="tab-pane active">
              <div style="position:relative;">
                <pre class="code-block"><code id="auth-req-ex-code">{
  "client_id":     "ef_abc123xyz",
  "client_secret": "your_client_secret_here",
  "grant_type":    "client_credentials"
}</code></pre>
                <button class="copy-code-btn" onclick="copyCode('auth-req-ex-code',this)">Copy</button>
              </div>
            </div>
            <div id="auth-req-schema" class="tab-pane">
              <table class="response-table">
                <thead><tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
                <tbody>
                  <tr><td><code>client_id</code></td><td>string</td><td>✓</td><td>Your API client ID</td></tr>
                  <tr><td><code>client_secret</code></td><td>string</td><td>✓</td><td>Your API client secret</td></tr>
                  <tr><td><code>grant_type</code></td><td>string</td><td>✓</td><td>Must be <code>client_credentials</code></td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Response -->
          <div>
            <div class="tab-label"><span>Response</span></div>
            <div class="tab-row" id="auth-res-tabs">
              <button class="tab-btn active" onclick="switchTab('auth-res','200',this)">200 Success</button>
              <button class="tab-btn" onclick="switchTab('auth-res','401',this)">401 Error</button>
            </div>
            <div id="auth-res-200" class="tab-pane active">
              <div style="position:relative;">
                <pre class="code-block"><code id="auth-res-200-code">{
  "success":      true,
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "token_type":   "Bearer",
  "expires_in":   3600
}</code></pre>
                <button class="copy-code-btn" onclick="copyCode('auth-res-200-code',this)">Copy</button>
              </div>
            </div>
            <div id="auth-res-401" class="tab-pane">
              <pre class="code-block"><code>{
  "success": false,
  "error":   "INVALID_CREDENTIALS",
  "message": "Invalid client_id or client_secret."
}</code></pre>
            </div>
            <div style="margin-top:14px;">
              <table class="response-table">
                <thead><tr><th>Code</th><th>Description</th></tr></thead>
                <tbody>
                  <tr><td class="code-200">200</td><td>Token issued successfully</td></tr>
                  <tr><td class="code-401">401</td><td>Invalid credentials</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Try it -->
        <div class="try-toggle" onclick="toggleTry('try-auth')">
          <span class="try-toggle-label">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            Try it
          </span>
          <svg id="try-auth-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div class="try-panel" id="try-auth">
          <div class="try-field">
            <label>client_id</label>
            <input type="text" id="try-auth-cid" placeholder="ef_abc123xyz">
          </div>
          <div class="try-field">
            <label>client_secret</label>
            <input type="password" id="try-auth-sec" placeholder="your_client_secret">
          </div>
          <div class="try-actions">
            <button class="btn btn-primary btn-sm" onclick="runAuthToken()">Get Token</button>
            <div class="try-spinner" id="try-auth-spin"></div>
          </div>
          <div class="try-result" id="try-auth-result">
            <div class="try-result-header">
              <span>Response</span>
              <span class="result-status" id="try-auth-status"></span>
            </div>
            <div style="position:relative;">
              <pre class="code-block" style="max-height:200px;overflow-y:auto;"><code id="try-auth-body"></code></pre>
              <button class="copy-code-btn" onclick="copyCode('try-auth-body',this)">Copy</button>
            </div>
            <div id="try-auth-save-msg" style="display:none;margin-top:8px;font-size:12px;color:var(--green,#16a34a);font-weight:600;">
              ✓ Token saved — it will be auto-filled in the invoice endpoint below.
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── ENDPOINT: POST /api/invoices ───────────────────────────────── -->
    <div class="api-section" id="submit-invoice">
      <div class="api-section-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 21V3h14v18l-3-2-3 2-3-2-3 2-2-2z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
        Submit Invoice
      </div>

      <div class="endpoint-card">
        <div class="endpoint-header">
          <span class="method-badge method-post">POST</span>
          <span class="endpoint-path">/api/invoices</span>
          <span style="font-size:12px;color:var(--text-muted);margin-left:auto;">Requires Bearer token</span>
        </div>
        <div class="endpoint-desc">
          Submit a PINT AE compliant invoice or credit note to the Peppol network. Supports 4 document modes. The document is validated before transmission — if validation fails a <code>422</code> is returned with details; if AS4 transmission fails a <code>502</code> is returned.
          <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
            <?php foreach ([
              'Mode 1 — Invoice (380)',
              'Mode 3 — Credit Note (381)',
              'Mode 4 — SB Invoice (389)',
              'Mode 5 — SB Credit Note (261)',
            ] as $m): ?>
            <span class="status status-blue" style="font-size:11px;"><?= $m ?></span>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="code-tabs">

          <!-- Mode selector -->
          <div style="margin-bottom:14px;">
            <div class="tab-label"><span>Select Mode</span></div>
            <div class="tab-row" id="inv-mode-tabs">
              <?php foreach ([1=>'Mode 1',3=>'Mode 3',4=>'Mode 4',5=>'Mode 5'] as $m=>$l): ?>
              <button class="tab-btn <?= $m===1?'active':'' ?>"
                      onclick="switchTab('inv-mode','<?= $m ?>',this); switchExample(<?= $m ?>)">
                <?= $l ?>
              </button>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Request examples per mode -->
          <?php
          $examples = [
            1 => '{
  "mode": 1,
  "sender_peppol_id":   "iso6523-actorid-upis::0235:100000000000001",
  "receiver_peppol_id": "iso6523-actorid-upis::0235:100000000000003",
  "payment_account":    "AE070331234567890123456",
  "basicdetails": [{
    "inv_no":   "INV-2026-001",
    "inv_date": "2026-08-21",
    "due_date": "2026-09-21",
    "currency": "AED",
    "branch_id": 1
  }],
  "location": [{
    "country_code": "AE",
    "street":       "Office 101, Business Bay",
    "city":         "Dubai",
    "state":        "Dubai",
    "postal_zone":  "00000"
  }],
  "customer_details": [{
    "VAT_number":   "100000000000003",
    "VAT_name":     "Buyer Company LLC",
    "street":       "Buyer Street",
    "city":         "Abu Dhabi",
    "state":        "Abu Dhabi",
    "postal_zone":  "00000",
    "country_code": "AE"
  }],
  "itemdetails": [{
    "item_name":          "Consulting Services",
    "description":        "Professional services rendered",
    "unit":               "EA",
    "quantity":           10,
    "price":              100.00,
    "vat_perc":           "5",
    "tax_category":       "S",
    "allowance":          [],
    "exempt_reason":      "",
    "exempt_reason_code": ""
  }],
  "payment_code": 30
}',
            3 => '{
  "mode": 3,
  "sender_peppol_id":        "iso6523-actorid-upis::0235:100000000000001",
  "receiver_peppol_id":     "iso6523-actorid-upis::0235:100000000000003",
  "canceled_invoice_number": "INV-2026-001",
  "canceled_invoice_date":   "2026-08-21",
  "basicdetails": [{
    "inv_no":   "CN-2026-001",
    "inv_date": "2026-08-21",
    "currency": "AED",
    "branch_id": 1
  }],
  "location": [{
    "country_code": "AE",
    "street":       "Office 101, Business Bay",
    "city":         "Dubai",
    "state":        "Dubai",
    "postal_zone":  "00000"
  }],
  "customer_details": [{
    "VAT_number":   "100000000000003",
    "VAT_name":     "Buyer Company LLC",
    "street":       "Buyer Street",
    "city":         "Abu Dhabi",
    "state":        "Abu Dhabi",
    "postal_zone":  "00000",
    "country_code": "AE"
  }],
  "itemdetails": [{
    "item_name":    "Credit for Consulting Services",
    "description":  "Correction of INV-2026-001",
    "unit":         "EA",
    "quantity":     10,
    "price":        100.00,
    "vat_perc":     "5",
    "tax_category": "S",
    "allowance":    []
  }],
  "payment_code": 30
}',
            4 => '{
  "mode": 4,
  "sender_peppol_id": "iso6523-actorid-upis::0235:100000000000001",
  "receiver_scheme":  "0235",
  "basicdetails": [{
    "inv_no":   "SBI-2026-001",
    "inv_date": "2026-08-21",
    "due_date": "2026-09-21",
    "currency": "AED",
    "branch_id": 1
  }],
  "location": [{
    "country_code": "AE",
    "street":       "Office 101, Business Bay",
    "city":         "Dubai",
    "state":        "Dubai",
    "postal_zone":  "00000"
  }],
  "customer_details": [{
    "VAT_number":   "100000000000003",
    "VAT_name":     "Supplier Company LLC",
    "street":       "Supplier Street",
    "city":         "Dubai",
    "state":        "Dubai",
    "postal_zone":  "00000",
    "country_code": "AE"
  }],
  "itemdetails": [{
    "item_name":    "Goods Purchased",
    "unit":         "EA",
    "quantity":     5,
    "price":        200.00,
    "vat_perc":     "5",
    "tax_category": "S",
    "allowance":    []
  }],
  "payment_code": 30
}',
            5 => '{
  "mode": 5,
  "sender_peppol_id":        "iso6523-actorid-upis::0235:100000000000001",
  "receiver_scheme":         "0235",
  "canceled_invoice_number": "SBI-2026-001",
  "canceled_invoice_date":   "2026-08-21",
  "discrepancy_reason_code": "3",
  "basicdetails": [{
    "inv_no":   "SBCN-2026-001",
    "inv_date": "2026-08-21",
    "currency": "AED",
    "branch_id": 1
  }],
  "location": [{
    "country_code": "AE",
    "street":       "Office 101, Business Bay",
    "city":         "Dubai",
    "state":        "Dubai",
    "postal_zone":  "00000"
  }],
  "customer_details": [{
    "VAT_number":   "100000000000003",
    "VAT_name":     "Supplier Company LLC",
    "street":       "Supplier Street",
    "city":         "Dubai",
    "state":        "Dubai",
    "postal_zone":  "00000",
    "country_code": "AE"
  }],
  "itemdetails": [{
    "item_name":    "Credit for Goods Purchased",
    "unit":         "EA",
    "quantity":     5,
    "price":        200.00,
    "vat_perc":     "5",
    "tax_category": "S",
    "allowance":    []
  }],
  "payment_code": 30
}',
          ];
          ?>

          <?php foreach ($examples as $mode => $exJson): ?>
          <div id="inv-mode-<?= $mode ?>" class="tab-pane <?= $mode===1?'active':'' ?>">
            <div class="tab-label">
              <span>Request Body — Mode <?= $mode ?></span>
              <span class="ct">application/json</span>
            </div>
            <div style="position:relative;">
              <pre class="code-block" style="max-height:340px;overflow-y:auto;"><code id="inv-ex-<?= $mode ?>"><?= htmlspecialchars($exJson) ?></code></pre>
              <button class="copy-code-btn" onclick="copyCode('inv-ex-<?= $mode ?>',this)">Copy</button>
            </div>
          </div>
          <?php endforeach; ?>

          <!-- Response -->
          <div style="margin-top:16px;">
            <div class="tab-label"><span>Response</span></div>
            <div class="tab-row" id="inv-res-tabs">
              <button class="tab-btn active" onclick="switchTab('inv-res','201',this)">201 Sent</button>
              <button class="tab-btn" onclick="switchTab('inv-res','422',this)">422 Validation</button>
              <button class="tab-btn" onclick="switchTab('inv-res','502',this)">502 Peppol Error</button>
            </div>
            <div id="inv-res-201" class="tab-pane active">
              <div style="position:relative;">
                <pre class="code-block"><code id="inv-res-201-code">{
  "success": true,
  "data": {
    "invoice_id":     42,
    "invoice_number": "INV-2026-001",
    "mode":           1,
    "status":         "sent",
    "peppol_response": {
      "success": true,
      "message": "Document transmitted successfully."
    }
  }
}</code></pre>
                <button class="copy-code-btn" onclick="copyCode('inv-res-201-code',this)">Copy</button>
              </div>
            </div>
            <div id="inv-res-422" class="tab-pane">
              <pre class="code-block"><code>{
  "success": false,
  "error":   "VALIDATION_ERROR",
  "message": "Missing required field: basicdetails."
}</code></pre>
            </div>
            <div id="inv-res-502" class="tab-pane">
              <pre class="code-block"><code>{
  "success": false,
  "error":   "PEPPOL_ERROR",
  "message": "AS4 transmission failed: receiver endpoint not found in SMP."
}</code></pre>
            </div>
            <div style="margin-top:14px;">
              <table class="response-table">
                <thead><tr><th>Code</th><th>Description</th></tr></thead>
                <tbody>
                  <tr><td class="code-201">201</td><td>Invoice accepted and transmitted to Peppol</td></tr>
                  <tr><td class="code-401">401</td><td>Invalid or expired Bearer token</td></tr>
                  <tr><td class="code-422">422</td><td>Validation failed — check <code>message</code> for details</td></tr>
                  <tr><td class="code-502">502</td><td>Document built OK but Peppol AS4 send failed</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Try it -->
        <div class="try-toggle" onclick="toggleTry('try-inv')">
          <span class="try-toggle-label">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            Try it
          </span>
          <svg id="try-inv-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div class="try-panel" id="try-inv">
          <div class="try-field">
            <label>Authorization — Bearer Token <span id="try-inv-token-hint" style="color:var(--green,#16a34a);font-size:11px;font-weight:400;display:none;">✓ auto-filled from auth</span></label>
            <input type="text" id="try-inv-token" placeholder="Paste your Bearer token here (or use Try it above to get one)">
          </div>
          <div class="try-field">
            <label>Request Body (JSON)</label>
            <textarea id="try-inv-body"><?= htmlspecialchars($examples[1]) ?></textarea>
          </div>
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;flex-wrap:wrap;">
            <span style="font-size:12px;color:var(--text-muted);">Quick fill:</span>
            <?php foreach ([1=>'Mode 1 Invoice',3=>'Mode 3 Credit Note',4=>'Mode 4 SB Invoice',5=>'Mode 5 SB Credit Note'] as $m=>$l): ?>
            <button class="btn btn-ghost btn-sm" onclick="fillMode(<?= $m ?>)"><?= $l ?></button>
            <?php endforeach; ?>
          </div>
          <div class="try-actions">
            <button class="btn btn-primary btn-sm" onclick="runInvoice()">Send Invoice</button>
            <div class="try-spinner" id="try-inv-spin"></div>
          </div>
          <div class="try-result" id="try-inv-result">
            <div class="try-result-header">
              <span>Response</span>
              <span class="result-status" id="try-inv-status"></span>
            </div>
            <pre class="code-block" style="max-height:280px;overflow-y:auto;"><code id="try-inv-body-out"></code></pre>
          </div>
        </div>
      </div>
    </div>

    <!-- MODE REFERENCE CARDS -->
    <div class="api-section" id="mode-1">
      <div class="api-section-title" style="font-size:15px;">
        <span class="method-badge method-post" style="font-size:12px;">MODE 1</span>
        Standard Invoice (InvoiceTypeCode 380)
      </div>
      <div class="card"><div class="card-body" style="font-size:13px;line-height:1.7;color:var(--text);">
        <p>A standard B2B invoice from a seller to a buyer. Requires <code>sender_peppol_id</code> (which participant in your account is sending), <code>receiver_peppol_id</code>, and <code>payment_account</code> (IBAN). Used for the majority of commercial transactions.</p>
        <p style="margin-top:8px;"><strong>Required fields unique to Mode 1:</strong> <code>sender_peppol_id</code>, <code>receiver_peppol_id</code>, <code>payment_account</code>, <code>due_date</code></p>
      </div></div>
    </div>

    <div class="api-section" id="mode-3">
      <div class="api-section-title" style="font-size:15px;">
        <span class="method-badge method-post" style="font-size:12px;">MODE 3</span>
        Credit Note (InvoiceTypeCode 381)
      </div>
      <div class="card"><div class="card-body" style="font-size:13px;line-height:1.7;color:var(--text);">
        <p>A credit note referencing a previously issued invoice. Used to cancel or partially reduce a prior invoice. Does not require <code>due_date</code>.</p>
        <p style="margin-top:8px;"><strong>Required fields unique to Mode 3:</strong> <code>sender_peppol_id</code>, <code>receiver_peppol_id</code>, <code>canceled_invoice_number</code>, <code>canceled_invoice_date</code></p>
      </div></div>
    </div>

    <div class="api-section" id="mode-4">
      <div class="api-section-title" style="font-size:15px;">
        <span class="method-badge method-post" style="font-size:12px;">MODE 4</span>
        Self-Billing Invoice (InvoiceTypeCode 389)
      </div>
      <div class="card"><div class="card-body" style="font-size:13px;line-height:1.7;color:var(--text);">
        <p>A self-billing invoice where the buyer issues the invoice on behalf of the seller. Uses <code>receiver_scheme</code> instead of a full Peppol ID.</p>
        <p style="margin-top:8px;"><strong>Required fields unique to Mode 4:</strong> <code>sender_peppol_id</code>, <code>receiver_scheme</code> (e.g. <code>"0235"</code>)</p>
      </div></div>
    </div>

    <div class="api-section" id="mode-5">
      <div class="api-section-title" style="font-size:15px;">
        <span class="method-badge method-post" style="font-size:12px;">MODE 5</span>
        Self-Billing Credit Note (InvoiceTypeCode 261)
      </div>
      <div class="card"><div class="card-body" style="font-size:13px;line-height:1.7;color:var(--text);">
        <p>A credit note for a self-billing invoice. Combines the requirements of Mode 3 (credit note reference) and Mode 4 (self-billing scheme).</p>
        <p style="margin-top:8px;"><strong>Required fields unique to Mode 5:</strong> <code>sender_peppol_id</code>, <code>receiver_scheme</code>, <code>canceled_invoice_number</code>, <code>canceled_invoice_date</code>, <code>discrepancy_reason_code</code> (optional)</p>
      </div></div>
    </div>

  </div><!-- /main content -->
</div><!-- /catalog-wrap -->

<script>
const API_BASE = '<?= $apiBase ?>';

// ── Stored token from Try Auth ───────────────────────────────────────────
let _savedToken = '';

// ── Tab switcher ─────────────────────────────────────────────────────────
function switchTab(group, key, btn) {
  document.querySelectorAll(`[id^="${group}-"]`).forEach(el => {
    if (el.classList.contains('tab-pane')) el.classList.remove('active');
  });
  const target = document.getElementById(`${group}-${key}`);
  if (target) target.classList.add('active');

  const tabRow = btn.closest('.tab-row');
  tabRow?.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

function switchExample(mode) {
  document.querySelectorAll('[id^="inv-mode-"]').forEach(el => el.classList.remove('active'));
  const el = document.getElementById('inv-mode-' + mode);
  if (el) el.classList.add('active');
}

// ── Try panel toggle ─────────────────────────────────────────────────────
function toggleTry(id) {
  const panel   = document.getElementById(id);
  const chevron = document.getElementById(id + '-chevron');
  const open    = panel.classList.toggle('open');
  if (chevron) chevron.style.transform = open ? 'rotate(180deg)' : '';
}

// ── Copy code ────────────────────────────────────────────────────────────
function copyCode(id, btn) {
  const text = document.getElementById(id)?.textContent || '';
  navigator.clipboard.writeText(text.trim()).then(() => {
    const orig = btn.textContent;
    btn.textContent = '✓';
    setTimeout(() => btn.textContent = orig, 1500);
  });
}

function showToastMsg(msg) {
  const d = document.createElement('div');
  d.style.cssText = 'position:fixed;top:20px;right:20px;padding:10px 18px;background:var(--primary,#2563eb);color:#fff;border-radius:8px;font-size:13px;font-weight:500;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,.15);';
  d.textContent = msg;
  document.body.appendChild(d);
  setTimeout(() => d.remove(), 2000);
}

// ── Quick fill invoice mode ──────────────────────────────────────────────
const modeExamples = {
<?php foreach ($examples as $mode => $ex): ?>
  <?= $mode ?>: <?= json_encode($ex) ?>,
<?php endforeach; ?>
};

function fillMode(mode) {
  const ta = document.getElementById('try-inv-body');
  if (ta) ta.value = modeExamples[mode] || '';
}

// ── Try: GET TOKEN ───────────────────────────────────────────────────────
async function runAuthToken() {
  const cid  = document.getElementById('try-auth-cid').value.trim();
  const csec = document.getElementById('try-auth-sec').value.trim();
  const spin = document.getElementById('try-auth-spin');
  const res  = document.getElementById('try-auth-result');
  const body = document.getElementById('try-auth-body');
  const stat = document.getElementById('try-auth-status');
  const save = document.getElementById('try-auth-save-msg');

  if (!cid || !csec) { showToastMsg('Enter client_id and client_secret'); return; }

  spin.style.display = 'inline-block';
  res.classList.remove('show');
  save.style.display = 'none';

  try {
    const r = await fetch(API_BASE + '/api/auth/token', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ client_id: cid, client_secret: csec, grant_type: 'client_credentials' }),
    });
    const data = await r.json();
    spin.style.display = 'none';

    stat.textContent = r.status + ' ' + (r.ok ? 'OK' : 'Error');
    stat.className   = 'result-status ' + (r.ok ? 'result-ok' : 'result-err');
    body.textContent = JSON.stringify(data, null, 2);
    res.classList.add('show');

    if (r.ok && data.access_token) {
      _savedToken = data.access_token;
      const tokenInput = document.getElementById('try-inv-token');
      if (tokenInput) tokenInput.value = _savedToken;
      const hint = document.getElementById('try-inv-token-hint');
      if (hint) hint.style.display = '';
      save.style.display = '';
    }
  } catch(e) {
    spin.style.display = 'none';
    stat.textContent = 'Network Error';
    stat.className   = 'result-status result-err';
    body.textContent = e.message;
    res.classList.add('show');
  }
}

// ── Try: SUBMIT INVOICE ──────────────────────────────────────────────────
async function runInvoice() {
  const token  = document.getElementById('try-inv-token').value.trim();
  const bodyTa = document.getElementById('try-inv-body').value.trim();
  const spin   = document.getElementById('try-inv-spin');
  const res    = document.getElementById('try-inv-result');
  const out    = document.getElementById('try-inv-body-out');
  const stat   = document.getElementById('try-inv-status');

  if (!token)  { showToastMsg('Enter your Bearer token'); return; }
  if (!bodyTa) { showToastMsg('Enter the request body'); return; }

  let parsed;
  try { parsed = JSON.parse(bodyTa); }
  catch(e) { showToastMsg('Invalid JSON in request body'); return; }

  spin.style.display = 'inline-block';
  res.classList.remove('show');

  try {
    const r = await fetch(API_BASE + '/api/invoices', {
      method: 'POST',
      headers: {
        'Content-Type':  'application/json',
        'Accept':        'application/json',
        'Authorization': 'Bearer ' + token,
      },
      body: JSON.stringify(parsed),
    });
    const data = await r.json();
    spin.style.display = 'none';

    stat.textContent = r.status + ' ' + (r.ok ? 'Created' : 'Error');
    stat.className   = 'result-status ' + (r.ok ? 'result-ok' : 'result-err');
    out.textContent  = JSON.stringify(data, null, 2);
    res.classList.add('show');
  } catch(e) {
    spin.style.display = 'none';
    stat.textContent = 'Network Error';
    stat.className   = 'result-status result-err';
    out.textContent  = e.message;
    res.classList.add('show');
  }
}

// ── Sticky TOC highlight on scroll ──────────────────────────────────────
const tocLinks = document.querySelectorAll('.toc-link[href^="#"]');
const sections = [...tocLinks].map(l => document.querySelector(l.getAttribute('href'))).filter(Boolean);

window.addEventListener('scroll', () => {
  let current = '';
  sections.forEach(s => { if (window.scrollY >= s.offsetTop - 120) current = '#' + s.id; });
  tocLinks.forEach(l => l.classList.toggle('active', l.getAttribute('href') === current));
}, { passive: true });
</script>
