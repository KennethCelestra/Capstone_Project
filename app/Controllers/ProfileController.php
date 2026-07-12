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

        $data = [
            'flash'    => $this->getFlash(),
            'userName' => $_SESSION['user_name'],
            'role'     => $_SESSION['user_role']
        ];
        
        $this->view('layouts/main', array_merge($data, ['content' => 'profile/index']));
    }

    public function changePassword(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $role   = $_SESSION['user_role'];
        
        $currentPass = $this->getPost('current_password', '');
        $newPass     = $this->getPost('new_password', '');
        $confirmPass = $this->getPost('confirm_password', '');

        if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
            $this->setFlash('error', 'All fields are required.');
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
