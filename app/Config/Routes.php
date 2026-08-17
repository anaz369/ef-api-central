<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Main');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
//$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/',              'Login::index');
$routes->get('login',          'Login::index');
$routes->post('login',         'Login::index');
$routes->get('login/totp',     'Login::totp');
$routes->post('login/totp',    'Login::totp');
$routes->get('logout',         'Logout::index');
$routes->get('dashboard',      'Dashboard::index');
$routes->post('file/upload',   'Main::upload');

// Profile
$routes->get('profile',                    'Profile::index');
$routes->post('profile/update',            'Profile::update');
$routes->get('profile/change-password',    'Profile::changePassword');
$routes->post('profile/change-password',   'Profile::changePassword');
$routes->get('profile/mfa-setup',          'Profile::mfaSetup');
$routes->post('profile/mfa-setup',         'Profile::mfaSetup');
$routes->post('profile/mfa-disable',       'Profile::mfaDisable');

// Users (super admin only)
$routes->get('users',                          'Admin\Users::index');
$routes->get('users/create',                   'Admin\Users::create');
$routes->post('users/create',                  'Admin\Users::create');
$routes->get('users/(:num)',                   'Admin\Users::view/$1');
$routes->post('users/(:num)/update',           'Admin\Users::update/$1');
$routes->post('users/(:num)/reset-password',   'Admin\Users::resetPassword/$1');

// API (public — no auth guard, token-based)
$routes->post('api/auth/token',  'Api\Auth::token');
$routes->post('api/invoices',    'Api\Invoices::submit');

// Invoices (Admin)
$routes->get('invoices',         'Admin\Invoices::index');
$routes->get('invoices/(:num)',  'Admin\Invoices::view/$1');

// API Logs (Admin)
$routes->get('logs',             'Admin\ApiLogs::index');

// UAE FTA Onboarding (public — no auth, FTA redirects with ?authcode=XXX)
$routes->get('uae/onboard',                  'Uae\Onboarding::onboard');
$routes->get('uae/onboard-ar',               'Uae\Onboarding::onboardAr');
$routes->post('uae/ajax-verify',             'Uae\Onboarding::ajaxVerify');
$routes->post('uae/confirm-onboard',         'Uae\Onboarding::confirmOnboard');
$routes->post('uae/confirm-reverify-delink', 'Uae\Onboarding::confirmReverifyDelink');

// Participants (Admin)
$routes->get('participants',                                   'Admin\Participants::index');
$routes->get('participants/create',                            'Admin\Participants::create');
$routes->post('participants/create',                           'Admin\Participants::create');
$routes->get('participants/lookup-peppol',                     'Admin\Participants::lookupPeppol');
$routes->get('participants/(:num)',                            'Admin\Participants::view/$1');
$routes->post('participants/(:num)/update',                    'Admin\Participants::update/$1');
$routes->post('participants/(:num)/delete',                    'Admin\Participants::delete/$1');
$routes->post('participants/(:num)/generate-credentials',      'Admin\Participants::generateCredentials/$1');
$routes->post('participants/(:num)/status',                    'Admin\Participants::updateStatus/$1');
$routes->post('participants/(:num)/grant-production',          'Admin\Participants::grantProduction/$1');
$routes->post('participants/(:num)/link-peppol',               'Admin\Participants::linkPeppol/$1');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
