<?php
// Route Definitions
// Format: 'METHOD /path' => ['Controller', 'method']

$routes = [
    // ---- Auth (Enrollment Committee / Signatory) ----
    'GET /'                                       => ['AuthController', 'index'],
    'GET /login'                                  => ['AuthController', 'index'],
    'POST /login'                                 => ['AuthController', 'login'],
    'GET /logout'                                 => ['AuthController', 'logout'],
    
    // ---- Password Reset (Guest) ----
    'GET /forgot-password'                        => ['PasswordResetController', 'forgotPassword'],
    'POST /forgot-password'                       => ['PasswordResetController', 'sendResetLink'],
    'GET /reset-password'                         => ['PasswordResetController', 'resetPassword'],
    'POST /reset-password'                        => ['PasswordResetController', 'updatePassword'],
    
    // ---- Profile / Change Password (Auth) ----
    'GET /profile'                                => ['ProfileController', 'index'],
    'POST /profile/change-password'               => ['ProfileController', 'changePassword'],

    // ---- Auth (Admin) — redirects to shared login ----
    'GET /admin/login'                            => ['AuthController', 'adminLogin'],

    // ---- Admin: Dashboard & Security ----
    'GET /admin/dashboard'                        => ['AdminController', 'dashboard'],
    'POST /admin/verify-password'                 => ['AdminController', 'verifyPassword'],

    // ---- Admin: Students ----
    'GET /admin/students'                         => ['AdminController', 'students'],
    'POST /admin/students/add'                    => ['AdminController', 'addStudent'],
    'POST /admin/students/delete'                 => ['AdminController', 'deleteStudent'],
    'POST /admin/students/upload'                 => ['AdminController', 'uploadStudents'],

    // ---- Admin: Enrollment Committee ----
    'GET /admin/enrollment-committees'                         => ['AdminController', 'enrollmentCommittees'],
    'POST /admin/enrollment-committees/add'                    => ['AdminController', 'addEnrollmentCommittee'],
    'POST /admin/enrollment-committees/edit'                   => ['AdminController', 'editEnrollmentCommittee'],
    'POST /admin/enrollment-committees/delete'                 => ['AdminController', 'deleteEnrollmentCommittee'],

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

    // ---- Admin: Clearance – Enrollment Committee assignment ----
    'POST /admin/clearances/enrollment-committees/assign'           => ['AdminController', 'assignEnrollmentCommittee'],
    'POST /admin/clearances/enrollment-committees/bulk-assign'      => ['AdminController', 'bulkAssignEnrollmentCommittees'],
    'POST /admin/clearances/enrollment-committees/remove'           => ['AdminController', 'removeEnrollmentCommittee'],

    // ---- Admin: Clearance – Student management ----
    'POST /admin/clearances/students/upload'      => ['AdminController', 'uploadStudents'],
    'POST /admin/clearances/students/remove'      => ['AdminController', 'removeStudentFromClearance'],

    // ---- Enrollment Committee ----
    'GET /enrollment-committee/dashboard'                      => ['Enrollment_CommitteeController', 'dashboard'],
    'GET /enrollment-committee/clearances'                     => ['Enrollment_CommitteeController', 'clearances'],

    // ---- Signatory ----
    'GET /signatory/dashboard'                    => ['SignatoryController', 'dashboard'],
    'GET /signatory/clearances'                   => ['SignatoryController', 'clearances'],
    'POST /signatory/students/flag'               => ['SignatoryController', 'flagStudent'],
    'POST /signatory/students/flag-bulk'          => ['SignatoryController', 'bulkFlagStudents'],
    'POST /signatory/students/clear'              => ['SignatoryController', 'clearStudent'],
    'POST /signatory/students/clear-all'          => ['SignatoryController', 'clearAll'],
    'POST /signatory/confirm/submit'              => ['SignatoryController', 'submitConfirm'],
    'POST /signatory/confirm-all'                 => ['SignatoryController', 'confirmAll'],
    'POST /api/process-bg-emails'                 => ['SignatoryController', 'processBgEmails'],
];
