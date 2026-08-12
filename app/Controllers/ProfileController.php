<?php

require_once ROOT_PATH . '/app/Models/Admin.php';
require_once ROOT_PATH . '/app/Models/Enrollment_Committee.php';
require_once ROOT_PATH . '/app/Models/Signatory.php';

class ProfileController extends Controller
{
    public function index(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $role   = $_SESSION['user_role'];

        $model = match($role) {
            'admin' => new Admin(),
            'enrollment_committee' => new Enrollment_Committee(),
            'signatory' => new Signatory(),
            default => null
        };

        $user = $model ? $model->findById($userId) : null;

        $data = [
            'flash'     => $this->getFlash(),
            'userName'  => $user ? $user['full_name'] : $_SESSION['user_name'],
            'userEmail' => $user ? $user['email'] : '',
            'role'      => $role
        ];
        
        $this->view('layouts/main', array_merge($data, ['content' => 'profile/index']));
    }

    public function updateInfo(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->redirect('profile');
            return;
        }

        $userId   = (int)$_SESSION['user_id'];
        $role     = $_SESSION['user_role'];
        $fullName = trim($this->getPost('full_name', ''));
        $email    = trim($this->getPost('email', ''));

        if (empty($fullName) || empty($email)) {
            $this->setFlash('error', 'Full Name and Email address are required.');
            $this->redirect('profile');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Please enter a valid email address.');
            $this->redirect('profile');
            return;
        }

        // Check if email already belongs to another user
        $models = [
            'admin' => new Admin(),
            'enrollment_committee' => new Enrollment_Committee(),
            'signatory' => new Signatory()
        ];

        foreach ($models as $mRole => $m) {
            $existing = $m->findByEmail($email);
            if ($existing) {
                $isSameUser = ($existing['id'] == $userId && $mRole === $role);
                if (!$isSameUser) {
                    $this->setFlash('error', 'That email address is already in use by another account.');
                    $this->redirect('profile');
                    return;
                }
            }
        }

        if ($role === 'admin') {
            $adminModel = new Admin();
            $adminModel->updateProfile($userId, $fullName, $email);
        } elseif ($role === 'enrollment_committee') {
            $ecModel = new Enrollment_Committee();
            $user = $ecModel->findById($userId);
            if ($user) {
                $ecModel->update($userId, [
                    'full_name'  => $fullName,
                    'email'      => $email,
                    'department' => $user['department']
                ]);
            }
        } elseif ($role === 'signatory') {
            $sigModel = new Signatory();
            $user = $sigModel->findById($userId);
            if ($user) {
                $sigModel->update($userId, [
                    'full_name'   => $fullName,
                    'email'       => $email,
                    'office'      => $user['office'],
                    'scope_type'  => $user['scope_type'] ?? null,
                    'scope_value' => $user['scope_value'] ?? null
                ]);
            }
        }

        $_SESSION['user_name'] = $fullName;
        $this->setFlash('success_info', 'Account details updated successfully!');
        $this->redirect('profile');
    }

    public function changePassword(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->redirect('profile');
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $role   = $_SESSION['user_role'];
        
        $currentPass = $this->getPost('current_password', '');
        $newPass     = $this->getPost('new_password', '');
        $confirmPass = $this->getPost('confirm_password', '');

        if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
            $this->setFlash('error', 'All password fields are required.');
            $this->redirect('profile');
            return;
        }

        if ($newPass !== $confirmPass) {
            $this->setFlash('error', 'New passwords do not match.');
            $this->redirect('profile');
            return;
        }

        // Get the appropriate model
        $model = match($role) {
            'admin' => new Admin(),
            'enrollment_committee' => new Enrollment_Committee(),
            'signatory' => new Signatory(),
            default => null
        };

        if (!$model) {
            $this->setFlash('error', 'Invalid user role.');
            $this->redirect('profile');
            return;
        }

        $user = $model->findById($userId);
        if (!$user || !password_verify($currentPass, $user['password'])) {
            $this->setFlash('error', 'Incorrect current password.');
            $this->redirect('profile');
            return;
        }

        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $model->updatePassword($userId, $hashed);

        $this->setFlash('success', 'Password successfully changed!');
        $this->redirect('profile');
    }
}
