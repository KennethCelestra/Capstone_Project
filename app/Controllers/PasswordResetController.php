<?php

require_once ROOT_PATH . '/app/Models/PasswordReset.php';
require_once ROOT_PATH . '/app/Models/Admin.php';
require_once ROOT_PATH . '/app/Models/Enrollment_Committee.php';
require_once ROOT_PATH . '/app/Models/Signatory.php';
require_once ROOT_PATH . '/app/Helpers/Mailer.php';

class PasswordResetController extends Controller
{
    private PasswordReset $resetModel;
    
    public function __construct()
    {
        $this->resetModel = new PasswordReset();
    }

    public function forgotPassword(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('profile');
            return;
        }
        $this->view('auth/forgot_password', ['flash' => $this->getFlash()]);
    }

    public function sendResetLink(): void
    {
        $email = trim($this->getPost('email', ''));
        if (empty($email)) {
            $this->setFlash('error', 'Please enter your email address.');
            $this->redirect('forgot-password');
            return;
        }

        // Check if email exists in any role
        $models = [new Admin(), new Enrollment_Committee(), new Signatory()];
        $userFound = null;
        foreach ($models as $m) {
            $userFound = $m->findByEmail($email);
            if ($userFound) break;
        }

        if ($userFound) {
            $token = bin2hex(random_bytes(32));
            if ($this->resetModel->createToken($email, $token)) {
                $resetLink = BASE_URL . 'reset-password?token=' . $token;
                
                $subject = "Password Reset Request - " . APP_NAME;
                $body = "
                    <div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;\">
                        <h2>Password Reset Request</h2>
                        <p>Hello " . htmlspecialchars($userFound['full_name']) . ",</p>
                        <p>We received a request to reset your password for the ISAT U Clearance System.</p>
                        <p>Please click the button below to set a new password:</p>
                        <p style=\"text-align: center; margin: 30px 0;\">
                            <a href=\"{$resetLink}\" style=\"background: #2563eb; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;\">Reset Password</a>
                        </p>
                        <p>If you did not request this, you can safely ignore this email. The link will expire in 1 hour.</p>
                    </div>
                ";
                
                Mailer::sendEmail($email, $userFound['full_name'], $subject, $body);
            }
        }

        // Always show the same success message to prevent email enumeration
        $this->setFlash('success', 'If that email exists in our system, a password reset link has been sent to it.');
        $this->redirect('login');
    }

    public function resetPassword(): void
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            $this->setFlash('error', 'Invalid or missing reset token.');
            $this->redirect('login');
            return;
        }

        $resetData = $this->resetModel->verifyToken($token);
        if (!$resetData) {
            $this->setFlash('error', 'This password reset link is invalid or has expired.');
            $this->redirect('login');
            return;
        }

        $this->view('auth/reset_password', [
            'token' => $token,
            'flash' => $this->getFlash()
        ]);
    }

    public function updatePassword(): void
    {
        $token = $this->getPost('token', '');
        $password = $this->getPost('password', '');
        $confirm = $this->getPost('confirm_password', '');

        if (empty($token) || empty($password) || empty($confirm)) {
            $this->setFlash('error', 'All fields are required.');
            $this->redirect('reset-password?token=' . urlencode($token));
            return;
        }

        if ($password !== $confirm) {
            $this->setFlash('error', 'Passwords do not match.');
            $this->redirect('reset-password?token=' . urlencode($token));
            return;
        }

        $resetData = $this->resetModel->verifyToken($token);
        if (!$resetData) {
            $this->setFlash('error', 'This password reset link is invalid or has expired.');
            $this->redirect('login');
            return;
        }

        $email = $resetData['email'];
        
        $roles = [
            'admin' => new Admin(),
            'enrollment_committee' => new Enrollment_Committee(),
            'signatory' => new Signatory()
        ];
        
        $updated = false;
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        foreach ($roles as $role => $model) {
            $user = $model->findByEmail($email);
            if ($user) {
                $model->updatePassword((int)$user['id'], $hashed);
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $this->resetModel->deleteToken($token);
            $this->setFlash('success', 'Your password has been successfully reset! You can now log in.');
            $this->redirect('login');
        } else {
            $this->setFlash('error', 'User account not found.');
            $this->redirect('login');
        }
    }
}
