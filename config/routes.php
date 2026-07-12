<?php
// Route Definitions
// Format: 'METHOD /path' => ['Controller', 'method']

$routes = [
    // ---- Auth (Adviser / Signatory) ----
    'GET /'                                       => ['AuthController', 'index'],
    'GET /login'                                  => ['AuthController', 'index'],
    'POST /login'                                 => ['AuthController', 'login'],
    'GET /logout'                                 => ['AuthController', 'logout'],

    // ---- Auth (Admin) — redirects to shared login ----
    'GET /admin/login'                            => ['AuthController', 'adminLogin'],

    // ---- Admin: Dashboard ----
    'GET /admin/dashboard'                        => ['AdminController', 'dashboard'],

    // ---- Admin: Students ----
    'GET /admin/students'                         => ['AdminController', 'students'],
    'POST /admin/students/add'                    => ['AdminController', 'addStudent'],
    'POST /admin/students/delete'                 => ['AdminController', 'deleteStudent'],
    'POST /admin/students/upload'                 => ['AdminController', 'uploadStudents'],

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
    'POST /admin/clearances/archive'              => ['AdminController', 'archiveClearance'],
    'POST /admin/clearances/unarchive'            => ['AdminController', 'unarchiveClearance'],
    'GET /admin/archived-clearances'              => ['AdminController', 'archivedClearances'],

    // ---- Admin: Clearance Detail (dynamic ID via GET param) ----
    'GET /admin/clearances/detail'                => ['AdminController', 'clearanceDetail'],

    // ---- Admin: Clearance – Signatory assignment ----
    'POST /admin/clearances/signatories/assign'        => ['AdminController', 'assignSignatory'],
    'POST /admin/clearances/signatories/bulk-assign'   => ['AdminController', 'bulkAssignSignatories'],
    'POST /admin/clearances/signatories/remove'        => ['AdminController', 'removeSignatory'],

    // ---- Admin: Clearance – Adviser assignment ----
    'POST /admin/clearances/advisers/assign'           => ['AdminController', 'assignAdviser'],
    'POST /admin/clearances/advisers/bulk-assign'      => ['AdminController', 'bulkAssignAdvisers'],
    'POST /admin/clearances/advisers/remove'           => ['AdminController', 'removeAdviser'],

    // ---- Admin: Clearance – Student management ----
    'POST /admin/clearances/students/upload'      => ['AdminController', 'uploadStudents'],
    'POST /admin/clearances/students/remove'      => ['AdminController', 'removeStudentFromClearance'],

    // ---- Adviser ----
    'GET /adviser/dashboard'                      => ['AdviserController', 'dashboard'],
    'GET /adviser/clearances'                     => ['AdviserController', 'clearances'],

    // ---- Signatory ----
    'GET /signatory/dashboard'                    => ['SignatoryController', 'dashboard'],
    'GET /signatory/clearances'                   => ['SignatoryController', 'clearances'],
    'POST /signatory/students/flag'               => ['SignatoryController', 'flagStudent'],
    'POST /signatory/students/clear'              => ['SignatoryController', 'clearStudent'],
    'POST /signatory/students/clear-all'          => ['SignatoryController', 'clearAll'],
    'POST /signatory/confirm/submit'              => ['SignatoryController', 'submitConfirm'],
    'POST /signatory/confirm-all'                 => ['SignatoryController', 'confirmAll'],
    'POST /api/process-bg-emails'                 => ['SignatoryController', 'processBgEmails'],
];
