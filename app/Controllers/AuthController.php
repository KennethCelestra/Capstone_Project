<?php

class AuthController extends Controller
{

    // --------------------------------------------------------
    //  Regular login (Adviser / Signatory)
    // --------------------------------------------------------

    public function index(): void
    {
        if ($this->isLoggedIn()) {
            $role = $_SESSION['user_role'];
            if ($role === 'admin') {
                $this->redirect('admin/dashboard');
            } else {
                $this->redirect($role . '/dashboard');
            }
        }
        $flash = $this->getFlash();
        $this->view('auth/login', ['flash' => $flash]);
    }

    public function login(): void
    {
        $email    = trim($this->getPost('email', ''));
        $password = $this->getPost('password', '');

        if (empty($email) || empty($password)) {
            $this->setFlash('error', 'Please fill in all fields.');
            $this->redirect('login');
            return;
        }

        // Try adviser first, then signatory
        $user = null;
        $role = null;

        $db = Database::getInstance();

        foreach (['adviser' => 'advisers', 'signatory' => 'signatories'] as $r => $table) {
            $stmt = $db->prepare("SELECT * FROM {$table} WHERE email = ?");
            $stmt->execute([$email]);
            $found = $stmt->fetch();
            if ($found && password_verify($password, $found['password'])) {
                $user = $found;
                $role = $r;
                break;
            }
        }

        if ($user) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_role'] = $role;
            $_SESSION['user_name'] = $user['full_name'];
            $this->redirect($role . '/dashboard');
        } else {
            $this->setFlash('error', 'Invalid credentials. Please try again.');
            $this->redirect('login');
        }
    }

    // --------------------------------------------------------
    //  Admin login (separate page)
    // --------------------------------------------------------

    public function adminLogin(): void
    {
        if ($this->isLoggedIn() && $_SESSION['user_role'] === 'admin') {
            $this->redirect('admin/dashboard');
        }
        $flash = $this->getFlash();
        $this->view('auth/admin_login', ['flash' => $flash]);
    }

    public function adminLoginPost(): void
    {
        $email    = trim($this->getPost('email', ''));
        $password = $this->getPost('password', '');

        if (empty($email) || empty($password)) {
            $this->setFlash('error', 'Please fill in all fields.');
            $this->redirect('admin/login');
            return;
        }

        $db   = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_role'] = 'admin';
            $_SESSION['user_name'] = $user['full_name'];
            $this->redirect('admin/dashboard');
        } else {
            $this->setFlash('error', 'Invalid admin credentials.');
            $this->redirect('admin/login');
        }
    }

    // --------------------------------------------------------
    //  Logout
    // --------------------------------------------------------

    public function logout(): void
    {
        $role = $_SESSION['user_role'] ?? 'adviser';
        session_destroy();
        if ($role === 'admin') {
            $this->redirect('admin/login');
        } else {
            $this->redirect('login');
        }
    }
}
