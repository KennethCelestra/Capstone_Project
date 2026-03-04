<?php
// Route Definitions
// Format: 'METHOD /path' => ['Controller', 'method']

$routes = [
    // Auth
    'GET /' => ['AuthController', 'index'],
    'GET /login' => ['AuthController', 'index'],
    'POST /login' => ['AuthController', 'login'],
    'GET /logout' => ['AuthController', 'logout'],

    // Admin
    'GET /admin/dashboard' => ['AdminController', 'dashboard'],
    'GET /admin/students' => ['AdminController', 'students'],
    'POST /admin/students/add' => ['AdminController', 'addStudent'],
    'POST /admin/students/delete' => ['AdminController', 'deleteStudent'],
    'GET /admin/advisers' => ['AdminController', 'advisers'],
    'POST /admin/advisers/add' => ['AdminController', 'addAdviser'],
    'POST /admin/advisers/delete' => ['AdminController', 'deleteAdviser'],
    'GET /admin/signatories' => ['AdminController', 'signatories'],
    'POST /admin/signatories/add' => ['AdminController', 'addSignatory'],
    'POST /admin/signatories/delete' => ['AdminController', 'deleteSignatory'],
    'GET /admin/clearances' => ['AdminController', 'clearances'],

    // Adviser
    'GET /adviser/dashboard' => ['AdviserController', 'dashboard'],
    'GET /adviser/clearances' => ['AdviserController', 'clearances'],
    'POST /adviser/clearances/mark' => ['AdviserController', 'markClearance'],

    // Signatory
    'GET /signatory/dashboard' => ['SignatoryController', 'dashboard'],
    'GET /signatory/clearances' => ['SignatoryController', 'clearances'],
    'POST /signatory/clearances/sign' => ['SignatoryController', 'signClearance'],
];
