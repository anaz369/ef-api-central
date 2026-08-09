<div class="page-header">
  <div>
    <h1 class="page-title">Add User</h1>
    <p class="page-subtitle">Create a new admin or ERP provider account. A temporary password will be generated.</p>
  </div>
</div>

<div id="form-errors" style="display:none;margin-bottom:20px;" class="alert alert-danger"></div>

<div style="max-width:560px;">
  <div class="card">
    <div class="card-body">
      <form id="user-create-form">

        <div class="form-group">
          <label class="form-label" for="name">Full Name *</label>
          <input type="text" id="name" name="name" class="form-control" required autofocus>
        </div>

        <div class="form-group">
          <label class="form-label" for="email">Email Address *
            <span style="font-size:12px;color:var(--text-muted);">(this will be their login username)</span>
          </label>
          <input type="email" id="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="company_name">Company Name</label>
          <input type="text" id="company_name" name="company_name" class="form-control"
                 placeholder="e.g. Acme ERP Solutions">
        </div>

        <div class="form-group">
          <label class="form-label">Role *</label>
          <div style="margin-top:6px;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;margin-bottom:8px;">
              <input type="radio" name="type" value="0" checked>
              Admin
              <span style="font-size:12px;color:var(--text-muted);">(ERP provider, sees only their participants)</span>
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
              <input type="radio" name="type" value="1">
              Super Admin
              <span style="font-size:12px;color:var(--text-muted);">(full access, EF Intelligence staff only)</span>
            </label>
          </div>
        </div>

        <div style="padding:12px 14px;border-radius:8px;background:var(--bg-secondary);border:1px solid var(--border);font-size:13px;color:var(--text-muted);margin:16px 0;">
          <strong>Note:</strong> A random temporary password will be generated and shown once after creation.
          The user must change it on first login.
        </div>

        <div style="display:flex;gap:12px;margin-top:8px;align-items:center;">
          <button type="button" id="btn-create-user" class="btn btn-primary">Create User</button>
          <a href="<?= base_url('users') ?>" class="btn btn-secondary">Cancel</a>
          <span id="create-spinner" style="display:none;font-size:13px;color:var(--text-muted);">Creating…</span>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('btn-create-user').addEventListener('click', function() {
    const form   = document.getElementById('user-create-form');
    const errBox = document.getElementById('form-errors');
    const btn    = this;
    const spin   = document.getElementById('create-spinner');

    // Basic client-side validation
    const name  = form.querySelector('[name="name"]').value.trim();
    const email = form.querySelector('[name="email"]').value.trim();
    if (!name || !email) {
        errBox.textContent = 'Name and Email are required.';
        errBox.style.display = '';
        return;
    }
    errBox.style.display = 'none';

    btn.disabled = true;
    spin.style.display = '';

    fetch('<?= base_url('users/create') ?>', {
        method: 'POST',
        body: new FormData(form),
    })
    .then(r => {
        if (!r.ok) {
            return r.text().then(t => { throw new Error('HTTP ' + r.status + ': ' + t.substring(0, 200)); });
        }
        return r.json();
    })
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            const msgs = Object.values(data.errors || {}).join('<br>');
            errBox.innerHTML = msgs || 'Validation failed.';
            errBox.style.display = '';
            btn.disabled = false;
            spin.style.display = 'none';
        }
    })
    .catch((err) => {
        console.error('Create user error:', err);
        errBox.textContent = 'Request failed: ' + err.message;
        errBox.style.display = '';
        btn.disabled = false;
        spin.style.display = 'none';
    });
});
</script>
