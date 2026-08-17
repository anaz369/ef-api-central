<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ($mode ?? 'onboard') === 'reverify' ? 'طلب إعادة التحقق / إلغاء الربط' : 'طلب الربط' ?> – Ethicfin</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        body { background: #f0f2f5; padding-bottom: 60px; font-family: 'Tajawal', sans-serif; }
        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; background: #1a3a6b; padding: 10px 0; z-index: 100; }
        .page-footer img { height: 28px; width: auto; }
        .page-footer .footer-text { color: rgba(255,255,255,0.7); font-size: 13px; }
        .page-header { background: #fff; border-bottom: 1px solid #dde3ed; padding: 14px 0; }
        .header-logo-fta  { height: 44px; width: auto; }
        .header-logo-mof  { height: 56px; width: auto; }
        .logo-separator   { width: 1px; height: 48px; background: #d0d5dd; margin: 0 4px; }
        .card { border: 1px solid #d8e0ee; border-radius: 6px; }
        .card-title { color: #1a3a6b; font-size: 1.05rem; font-weight: 600; margin-bottom: 20px; }
        .section-title { font-size: 0.95rem; font-weight: 700; color: #1a3a6b; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #e0e7f3; }
        .form-label { font-size: 13px; font-weight: 600; color: #444; margin-bottom: 4px; }
        .form-control, .form-select { font-size: 13.5px; border-color: #c8d3e8; border-radius: 4px; }
        .form-control:focus, .form-select:focus { border-color: #1a3a6b; box-shadow: 0 0 0 0.15rem rgba(26,58,107,0.15); }
        .form-control:disabled, .form-control[readonly] { background: #f0f3f8; color: #555; }
        .btn-verify { background: #1a3a6b; color: #fff; border: none; padding: 10px 28px; font-size: 14px; font-weight: 600; border-radius: 4px; white-space: nowrap; font-family: 'Tajawal', sans-serif; }
        .btn-verify:hover { background: #142d56; color: #fff; }
        .btn-verify:disabled { background: #7a9bcc; }
        .btn-submit { background: #b8860b; color: #fff; border: none; padding: 10px 32px; font-size: 14px; font-weight: 600; border-radius: 4px; font-family: 'Tajawal', sans-serif; }
        .btn-submit:hover { background: #9a700a; color: #fff; }
        .btn-submit:disabled { background: #d4aa60; }
        #entity-details { display: none; }
        .action-box { background: #f0f4fb; border: 1px solid #c8d3e8; border-radius: 4px; padding: 14px 18px; }
        .spinner-border-sm { width: 1rem; height: 1rem; border-width: 0.15em; }
        .page-title { color: #1a3a6b; font-size: 1.35rem; font-weight: 700; margin-bottom: 24px; }
        #processing-overlay { display: none; position: fixed; inset: 0; background: rgba(16,36,72,0.82); backdrop-filter: blur(3px); z-index: 9999; align-items: center; justify-content: center; }
        #processing-overlay.active { display: flex; }
        .proc-card { background: #fff; border-radius: 10px; padding: 44px 52px; text-align: center; max-width: 400px; width: 90%; box-shadow: 0 8px 40px rgba(0,0,0,0.25); }
        .proc-spinner { width: 56px; height: 56px; border: 5px solid #dde8f8; border-top-color: #1a3a6b; border-radius: 50%; animation: proc-spin 0.85s linear infinite; margin: 0 auto 22px; }
        @keyframes proc-spin { to { transform: rotate(360deg); } }
        .proc-title { color: #1a3a6b; font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; font-family: 'Tajawal', sans-serif; }
        .proc-step  { color: #666; font-size: 13px; min-height: 20px; transition: opacity 0.3s; font-family: 'Tajawal', sans-serif; }
        .proc-note  { color: #bbb; font-size: 11px; margin-top: 18px; font-family: 'Tajawal', sans-serif; }
    </style>
</head>
<body>

<div class="page-header">
    <div class="container d-flex align-items-center justify-content-between">
        <div></div>
        <div class="d-flex align-items-center gap-3">
            <img src="<?= base_url('assets/uae/logo-fta.webp') ?>" alt="هيئة الضرائب الاتحادية" class="header-logo-fta">
            <div class="logo-separator"></div>
            <img src="<?= base_url('assets/uae/mof_logo.jpg') ?>" alt="وزارة المالية" class="header-logo-mof">
        </div>
    </div>
</div>

<div class="container py-4" style="max-width:1320px;">

    <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><strong>خطأ:</strong> <?= htmlspecialchars($error) ?></div>

    <?php else: ?>

    <h4 class="page-title">
        <?= ($mode ?? 'onboard') === 'reverify' ? 'طلب إعادة التحقق / إلغاء الربط' : 'طلب الربط بشبكة بيبول للفوترة الإلكترونية' ?>
    </h4>

    <!-- Applicant Details -->
    <div class="card shadow-sm mb-3">
        <div class="card-body p-4">
            <div class="section-title">بيانات مقدم الطلب</div>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">عنوان البريد الإلكتروني المسجل في إماراتاكس <span class="text-danger">*</span></label>
                    <input type="email" id="inp-email" class="form-control" placeholder="example@email.com">
                </div>
                <div class="col-md-4">
                    <label class="form-label">رمز الدولة</label>
                    <select id="inp-country" class="form-select">
                        <option value="+971" selected>+971 الإمارات العربية المتحدة</option>
                        <option value="+966">+966 المملكة العربية السعودية</option>
                        <option value="+965">+965 الكويت</option>
                        <option value="+973">+973 البحرين</option>
                        <option value="+968">+968 عُمان</option>
                        <option value="+974">+974 قطر</option>
                        <option value="+91">+91 الهند</option>
                        <option value="+44">+44 المملكة المتحدة</option>
                        <option value="+1">+1 الولايات المتحدة</option>
                        <option value="+60">+60 ماليزيا</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">رقم الجوال المسجل <span class="text-muted">(اختياري)</span></label>
                    <input type="text" id="inp-mobile" class="form-control" placeholder="05X XXX XXXX">
                </div>
                <div class="col-md-5">
                    <label class="form-label">رقم التعريف الضريبي (TIN) <span class="text-danger">*</span></label>
                    <?php if (($mode ?? 'onboard') === 'reverify'): ?>
                    <input type="text" id="inp-tin" class="form-control" value="<?= htmlspecialchars($tin ?? '') ?>" disabled>
                    <?php else: ?>
                    <input type="text" id="inp-tin" class="form-control">
                    <?php endif; ?>
                </div>
                <?php if (($mode ?? 'onboard') === 'reverify'): ?>
                <div class="col-md-7 d-flex align-items-end">
                    <div class="action-box w-100">
                        <div class="form-label mb-2">اختر الإجراء</div>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action_radio" id="act-reverify" value="reverify" checked>
                                <label class="form-check-label fw-semibold" for="act-reverify">إعادة التحقق</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action_radio" id="act-delink" value="delink">
                                <label class="form-check-label fw-semibold" for="act-delink">إلغاء الربط</label>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div id="verify-error" class="alert alert-danger mt-3" style="display:none;"></div>

            <div class="d-flex justify-content-start mt-3">
                <button type="button" id="btn-verify" class="btn-verify" onclick="doVerify()">
                    <span id="verify-spinner" class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                    التحقق من بيانات مقدم الطلب
                </button>
            </div>
        </div>
    </div>

    <!-- Entity Details (shown after verify) -->
    <div id="entity-details" class="card shadow-sm mb-3">
        <div class="card-body p-4">
            <div class="section-title">بيانات المنشأة</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">النوع القانوني</label>
                    <input type="text" id="disp-legalType" class="form-control" disabled>
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <label class="form-label">الاسم القانوني للمنشأة بالإنجليزية</label>
                    <input type="text" id="disp-entityNameEn" class="form-control" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">الاسم القانوني للمنشأة بالعربية</label>
                    <input type="text" id="disp-entityNameAr" class="form-control" disabled dir="rtl">
                </div>
                <div class="col-md-6">
                    <label class="form-label">رقم التسجيل الضريبي للقيمة المضافة (VAT TRN)</label>
                    <input type="text" id="disp-vatTrn" class="form-control" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">تاريخ سريان التسجيل</label>
                    <input type="text" id="disp-effectiveDate" class="form-control" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">تاريخ التقديم</label>
                    <input type="text" id="disp-submissionDate" class="form-control" disabled>
                </div>
            </div>

            <div class="mt-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="chk-declare" onchange="toggleSubmit()">
                    <label class="form-check-label" for="chk-declare" style="font-size:13px;font-weight:600;">
                        أُقرّ بأن جميع المعلومات المقدمة صحيحة ودقيقة وكاملة وفق أفضل ما لديّ من معرفة واعتقاد.
                    </label>
                </div>
            </div>

            <form id="confirm-form" method="post"
                  action="<?= base_url(($mode ?? 'onboard') === 'reverify' ? 'uae/confirm-reverify-delink' : 'uae/confirm-onboard') ?>">
                <input type="hidden" name="locale"           value="ar">
                <input type="hidden" name="emarataxToken"    value="<?= htmlspecialchars($emarataxToken ?? '') ?>">
                <input type="hidden" id="hid-email"          name="email">
                <input type="hidden" id="hid-mobile"         name="mobile">
                <input type="hidden" id="hid-country"        name="country_code">
                <input type="hidden" id="hid-tin"            name="tin">
                <input type="hidden" id="hid-vat_trn"        name="vat_trn">
                <input type="hidden" id="hid-entity_name_en" name="entity_name_en">
                <input type="hidden" id="hid-entity_name_ar" name="entity_name_ar">
                <input type="hidden" id="hid-legal_type"     name="legal_type">
                <input type="hidden" id="hid-effective_date" name="effective_date">
                <?php if (($mode ?? 'onboard') === 'reverify'): ?>
                <input type="hidden" id="hid-selected_action" name="selected_action" value="reverify">
                <?php endif; ?>
                <div class="d-flex justify-content-start mt-3">
                    <button type="submit" id="btn-submit" class="btn-submit" disabled>إرسال</button>
                </div>
            </form>
        </div>
    </div>

    <?php endif; ?>
</div>

<script>
const MODE  = '<?= ($mode ?? 'onboard') === 'reverify' ? 'reverify' : 'onboard' ?>';
const EMARATAX_TOKEN = '<?= htmlspecialchars($emarataxToken ?? '') ?>';

// Remove authcode / emarataxToken from the browser URL bar after page load
if (window.history && window.history.replaceState) {
    window.history.replaceState({}, document.title, window.location.pathname);
}

document.querySelectorAll('input[name="action_radio"]').forEach(function(r) {
    r.addEventListener('change', function() {
        var hid = document.getElementById('hid-selected_action');
        if (hid) hid.value = this.value;
    });
});

function doVerify() {
    var email   = document.getElementById('inp-email').value.trim();
    var tin     = document.getElementById('inp-tin').value.trim();
    var mobile  = document.getElementById('inp-mobile').value.trim();
    var country = document.getElementById('inp-country').value;

    if (!email || !tin) {
        showVerifyError('يرجى إدخال رقم التعريف الضريبي وعنوان البريد الإلكتروني.');
        return;
    }
    if (!/^\d{15}$/.test(tin)) {
        showVerifyError('يجب أن يتكون رقم التعريف الضريبي (TIN) من 15 رقماً بالضبط.');
        return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showVerifyError('يرجى إدخال عنوان بريد إلكتروني صحيح.');
        return;
    }

    setVerifyLoading(true);
    hideVerifyError();
    document.getElementById('entity-details').style.display = 'none';

    fetch('<?= base_url('uae/ajax-verify') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ tin: tin, email: email, mobile: country + mobile, emarataxToken: EMARATAX_TOKEN })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        setVerifyLoading(false);
        if (!res.success) { showVerifyError(res.message || 'فشل التحقق.'); return; }
        populateEntityDetails(res.data, email, mobile, country, tin);
    })
    .catch(function() { setVerifyLoading(false); showVerifyError('خطأ في الاتصال. يرجى المحاولة مرة أخرى.'); });
}

function populateEntityDetails(data, email, mobile, country, tin) {
    document.getElementById('disp-legalType').value      = data.legalType      || '';
    document.getElementById('disp-entityNameEn').value   = data.entityNameEn   || '';
    document.getElementById('disp-entityNameAr').value   = data.entityNameAr   || '';
    document.getElementById('disp-vatTrn').value          = data.vatTrn         || '';
    document.getElementById('disp-effectiveDate').value  = data.effectiveDate  || '';
    document.getElementById('disp-submissionDate').value = data.submissionDate || '';
    document.getElementById('hid-email').value           = email;
    document.getElementById('hid-mobile').value          = country + mobile;
    document.getElementById('hid-country').value         = country;
    document.getElementById('hid-tin').value             = data.TIN || tin;
    document.getElementById('hid-vat_trn').value         = data.vatTrn         || '';
    document.getElementById('hid-entity_name_en').value  = data.entityNameEn   || '';
    document.getElementById('hid-entity_name_ar').value  = data.entityNameAr   || '';
    document.getElementById('hid-legal_type').value      = data.legalType      || '';
    document.getElementById('hid-effective_date').value  = data.effectiveDate  || '';
    document.getElementById('chk-declare').checked       = false;
    document.getElementById('btn-submit').disabled       = true;
    document.getElementById('entity-details').style.display = 'block';
    document.getElementById('entity-details').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function toggleSubmit() {
    document.getElementById('btn-submit').disabled = !document.getElementById('chk-declare').checked;
}
function setVerifyLoading(loading) {
    document.getElementById('btn-verify').disabled = loading;
    document.getElementById('verify-spinner').classList.toggle('d-none', !loading);
}
function showVerifyError(msg) {
    var el = document.getElementById('verify-error');
    el.textContent = msg; el.style.display = 'block';
}
function hideVerifyError() {
    document.getElementById('verify-error').style.display = 'none';
}

var _procSteps = ['جارٍ التحقق من البيانات مع سجل بيبول\u2026','جارٍ تسجيل المشارك في الشبكة\u2026','جارٍ إنشاء نقطة نهاية بيبول الخاصة بك\u2026','جارٍ إتمام التسجيل\u2026'];
var _stepIdx = 0, _stepTimer = null;

function showProcessingOverlay() {
    _stepIdx = 0;
    document.getElementById('processing-overlay').classList.add('active');
    document.getElementById('btn-submit').disabled = true;
    _tickStep();
}
function _tickStep() {
    var el = document.getElementById('proc-step');
    if (!el || _stepIdx >= _procSteps.length) return;
    el.style.opacity = '0';
    setTimeout(function() { el.textContent = _procSteps[_stepIdx++]; el.style.opacity = '1'; _stepTimer = setTimeout(_tickStep, 2000); }, 200);
}

document.getElementById('confirm-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    showProcessingOverlay();
    setTimeout(function() { form.submit(); }, 100);
});
</script>

<div id="processing-overlay">
    <div class="proc-card">
        <div class="proc-spinner"></div>
        <div class="proc-title">جارٍ إنشاء معرّف بيبول الخاص بك&hellip;</div>
        <div class="proc-step" id="proc-step">جارٍ التحقق من البيانات مع سجل بيبول&hellip;</div>
        <div class="proc-note">يرجى عدم إغلاق هذه النافذة أو تحديثها</div>
    </div>
</div>

<div class="page-footer">
    <div class="container d-flex align-items-center justify-content-center gap-3" style="max-width:1320px;">
        <img src="<?= base_url('assets/uae/ethicfin.png') ?>" alt="Ethicfin">
        <div style="width:1px;height:24px;background:rgba(255,255,255,0.3);"></div>
        <span class="footer-text">نقطة وصول بيبول معتمدة &bull; الإمارات العربية المتحدة</span>
    </div>
</div>
</body>
</html>
