<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? $page_title . ' | Ethicfin' : 'Ethicfin' ?></title>
<link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">
<script>(function(){try{var t=localStorage.getItem('theme');var d=window.matchMedia('(prefers-color-scheme: dark)').matches;var theme=t||(d?'dark':'light');document.documentElement.setAttribute('data-theme',theme);}catch(e){}})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/gentelella.css') ?>">
<script type="module" src="<?= base_url('assets/js/rolldown-runtime-_5RX-BWT.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/toast-CBtjS_PZ.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/menus-BJGD7GPP.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/modal-MTuCfURV.js') ?>"></script>
<script type="module" src="<?= base_url('assets/js/main-v4-BH4Y7_l7.js') ?>"></script>
</head>
<body data-shell="admin" data-page="<?= $active_menu ?? 'dashboard' ?>" data-breadcrumb="<?= $breadcrumb ?? 'Dashboard' ?>">

<a class="skip-link" href="#main-content">Skip to main content</a>

<aside class="sidebar" aria-label="Primary navigation">
  <div class="sidebar-brand" style="padding:16px 20px;">
    <img src="<?= base_url('assets/images/ethicfin-logo-white.png') ?>" alt="Ethicfin" style="height:30px;max-width:160px;object-fit:contain;">
  </div>
  <nav class="sidebar-nav">

    <div class="nav-group">
      <div class="nav-label">Overview</div>
      <a class="nav-link <?= ($active_menu === 'dashboard') ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
        <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="4" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="10" width="7" height="11" rx="1.5"/></svg>
        <span class="nav-text">Dashboard</span>
      </a>
    </div>

    <div class="nav-group">
      <div class="nav-label">Participants</div>
      <a class="nav-link <?= ($active_menu === 'participants') ? 'active' : '' ?>" href="<?= base_url('participants') ?>">
        <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <span class="nav-text">Participants</span>
      </a>
    </div>

    <div class="nav-group">
      <div class="nav-label">Peppol</div>
      <a class="nav-link <?= ($active_menu === 'invoices') ? 'active' : '' ?>" href="<?= base_url('invoices') ?>">
        <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 21V3h14v18l-3-2-3 2-3-2-3 2-2-2z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
        <span class="nav-text">Invoices</span>
      </a>
      <a class="nav-link <?= ($active_menu === 'onboarding') ? 'active' : '' ?>" href="<?= base_url('onboarding') ?>">
        <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <span class="nav-text">Onboarding</span>
      </a>
    </div>

    <div class="nav-group">
      <div class="nav-label">Developer</div>
      <a class="nav-link <?= ($active_menu === 'apikeys') ? 'active' : '' ?>" href="<?= base_url('apikeys') ?>">
        <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 11-7.778 7.778 5.5 5.5 0 017.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
        <span class="nav-text">API Keys</span>
      </a>
      <a class="nav-link <?= ($active_menu === 'logs') ? 'active' : '' ?>" href="<?= base_url('logs') ?>">
        <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        <span class="nav-text">API Logs</span>
      </a>
    </div>

    <div class="nav-group">
      <div class="nav-label">System</div>
      <?php if ((int)(session()->get('type')) === 1): ?>
      <a class="nav-link <?= ($active_menu === 'users') ? 'active' : '' ?>" href="<?= base_url('users') ?>">
        <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        <span class="nav-text">Users</span>
      </a>
      <?php endif; ?>
      <a class="nav-link <?= ($active_menu === 'settings') ? 'active' : '' ?>" href="<?= base_url('settings') ?>">
        <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1"/></svg>
        <span class="nav-text">Settings</span>
      </a>
    </div>

  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="avatar"><?= strtoupper(substr(session()->get('name') ?? 'A', 0, 1)) ?><span class="online"></span></div>
      <div class="sidebar-user-info">
        <div class="name"><?= htmlspecialchars(session()->get('name') ?? 'Admin') ?></div>
        <div class="role"><?= htmlspecialchars(session()->get('role') ?? 'Administrator') ?></div>
      </div>
      <a href="<?= base_url('profile') ?>" class="more-btn" title="Profile" aria-label="Profile">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="5" r="3"/><path d="M2 14c0-3.3 2.7-5 6-5s6 1.7 6 5"/></svg>
      </a>
    </div>
  </div>
</aside>

<header class="topbar">
  <div class="topbar-left">
    <button class="sidebar-toggle" type="button" aria-label="Open menu" aria-controls="sidebar" aria-expanded="false">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <span class="current" aria-current="page"><?= isset($breadcrumb) ? $breadcrumb : 'Dashboard' ?></span>
    </nav>
  </div>
  <div class="search-box">
    <svg class="s-icon" width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="7" cy="7" r="5"/><path d="M11 11l3.5 3.5"/></svg>
    <input type="text" placeholder="Search…" aria-label="Search">
  </div>
  <div class="topbar-right">
    <button class="tb-btn theme-toggle" type="button" title="Toggle theme" aria-label="Toggle theme" aria-pressed="false">
      <svg class="theme-icon-light" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
      <svg class="theme-icon-dark" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
    </button>
    <button class="tb-btn tb-notifications" type="button" title="Notifications" aria-label="Notifications" aria-haspopup="dialog" aria-expanded="false">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3a6 6 0 00-6 6c0 6-3 7-3 7h18s-3-1-3-7a6 6 0 00-6-6z"/><path d="M10.5 21a1.5 1.5 0 003 0"/></svg>
    </button>
    <a class="tb-btn" href="<?= base_url('logout') ?>" title="Logout" aria-label="Logout">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </a>
    <button class="tb-avatar" type="button" aria-label="Account menu"><?= strtoupper(substr(session()->get('name') ?? 'A', 0, 1)) ?></button>
  </div>
</header>

<main id="main-content" tabindex="-1" class="main">
<div class="page-wrapper">
