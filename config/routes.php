<?php
// Route Definitions
// Format: 'METHOD /path' => ['Controller', 'method']

$routes = [
    // ---- Auth (Adviser / Signatory) ----
    'GET /'                                       => ['AuthController', 'index'],
    'GET /login'                                  => ['AuthController', 'index'],
    'POST /login'                                 => ['AuthController', 'login'],
    'GET /logout'                                 => ['AuthController', 'logout'],

    // ---- Auth (Admin - separate page) ----
    'GET /admin/login'                            => ['AuthController', 'adminLogin'],
    'POST /admin/login'                           => ['AuthController', 'adminLoginPost'],

    // ---- Admin: Dashboard ----
    'GET /admin/dashboard'                        => ['AdminController', 'dashboard'],

    // ---- Admin: Students ----
    'GET /admin/students'                         => ['AdminController', 'students'],
    'POST /admin/students/add'                    => ['AdminController', 'addStudent'],
    'POST /admin/students/delete'                 => ['AdminController', 'deleteStudent'],
    'POST /admin/students/upload'                 => ['AdminController', 'uploadStudents'],
    'POST /admin/students/dummies'                => ['AdminController', 'insertDummies'],

    // ---- Admin: Advisers ----
    'GET /admin/advisers'                         => ['AdminController', 'advisers'],
    'POST /admin/advisers/add'                    => ['AdminController', 'addAdviser'],
    'POST /admin/advisers/edit'                   => ['AdminController', 'editAdviser'],
    'POST /admin/advisers/delete'                 => ['AdminController', 'deleteAdviser'],

    // ---- Admin: Signatories ----
    'GET /admin/signatories'                      => ['AdminController', 'signatories'],
    'POST /admin/signatories/add'                 => ['AdminController', 'addSignatory'],
    'POST /admin/signatories/edit'                => ['AdminController', 'editSignatory'],
    'POST /admin/signatories/delete'              => ['AdminController', 'deleteSignatory'],

    // ---- Admin: Clearances ----
    'GET /admin/clearances'                       => ['AdminController', 'clearances'],
    'POST /admin/clearances/create'               => ['AdminController', 'createClearance'],
    'POST /admin/clearances/edit'                 => ['AdminController', 'editClearance'],
    'POST /admin/clearances/delete'               => ['AdminController', 'deleteClearance'],

    // ---- Admin: Clearance Detail (dynamic ID via GET param) ----
    'GET /admin/clearances/detail'                => ['AdminController', 'clearanceDetail'],

    // ---- Admin: Clearance – Signatory assignment ----
    'POST /admin/clearances/signatories/assign'   => ['AdminController', 'assignSignatory'],
    'POST /admin/clearances/signatories/remove'   => ['AdminController', 'removeSignatory'],

    // ---- Admin: Clearance – Adviser assignment ----
    'POST /admin/clearances/advisers/assign'      => ['AdminController', 'assignAdviser'],
    'POST /admin/clearances/advisers/remove'      => ['AdminController', 'removeAdviser'],

    // ---- Admin: Clearance – Student management ----
    'POST /admin/clearances/students/upload'      => ['AdminController', 'uploadStudents'],
    'POST /admin/clearances/students/dummies'     => ['AdminController', 'insertDummies'],
    'POST /admin/clearances/students/remove'      => ['AdminController', 'removeStudentFromClearance'],

    // ---- Adviser ----
    'GET /adviser/dashboard'                      => ['AdviserController', 'dashboard'],
    'GET /adviser/clearances'                     => ['AdviserController', 'clearances'],
    'GET /adviser/enrollment'                     => ['AdviserController', 'enrollment'],

    // ---- Signatory ----
    'GET /signatory/dashboard'                    => ['SignatoryController', 'dashboard'],
    'GET /signatory/clearances'                   => ['SignatoryController', 'clearances'],
    'POST /signatory/students/flag'               => ['SignatoryController', 'flagStudent'],
    'POST /signatory/students/clear'              => ['SignatoryController', 'clearStudent'],
    'GET /signatory/confirm'                      => ['SignatoryController', 'confirmFlags'],
    'POST /signatory/confirm/submit'              => ['SignatoryController', 'submitConfirm'],

    // ---- Legacy (kept for backward compat) ----
    'POST /signatory/clearances/sign'             => ['SignatoryController', 'clearStudent'],
];
