<?php
require_once ROOT_PATH . '/app/Models/Student.php';

class AuthController extends Controller
{

    public function index(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect($_SESSION['user_role'] . '/dashboard');
        }
        $flash = $this->getFlash();
        $this->view('auth/login', ['flash' => $flash]);
    }

    public function login(): void
    {
        $email = trim($this->getPost('email', ''));
        $password = $this->getPost('password', '');
        $role = $this->getPost('role', '');

        if (empty($email) || empty($password) || empty($role)) {
            $this->setFlash('error', 'Please fill in all fields.');
            $this->redirect('login');
            return;
        }

        $user = $this->findUserByRole($email, $role);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $role;
            $_SESSION['user_name'] = $user['full_name'];
            $this->redirect($role . '/dashboard');
        } else {
            $this->setFlash('error', 'Invalid credentials. Please try again.');
            $this->redirect('login');
        }
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('login');
    }

    private function findUserByRole(string $email, string $role): array|false
    {
        $db = Database::getInstance();
        $table = match ($role) {
            'admin' => 'admins',
            'adviser' => 'advisers',
            'signatory' => 'signatories',
            default => null
        };

        if (!$table)
            return false;

        $stmt = $db->prepare("SELECT * FROM {$table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}
