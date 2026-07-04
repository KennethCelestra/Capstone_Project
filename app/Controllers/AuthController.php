<?php
require_once ROOT_PATH . '/app/Models/Admin.php';
require_once ROOT_PATH . '/app/Models/Adviser.php';
require_once ROOT_PATH . '/app/Models/Signatory.php';

class AuthController extends Controller
{
    private Admin     $adminModel;
    private Adviser   $adviserModel;
    private Signatory $signatoryModel;

    public function __construct()
    {
        $this->adminModel     = new Admin();
        $this->adviserModel   = new Adviser();
        $this->signatoryModel = new Signatory();
    }

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

        // Try admin first, then adviser, then signatory
        $user = null;
        $role = null;

        $candidates = [
            'admin'     => $this->adminModel,
            'adviser'   => $this->adviserModel,
            'signatory' => $this->signatoryModel,
        ];

        foreach ($candidates as $r => $model) {
            $found = $model->findByEmail($email);
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

    // /admin/login now redirects to the shared login page
    public function adminLogin(): void
    {
        $this->redirect('login');
    }

    // --------------------------------------------------------
    //  Logout
    // --------------------------------------------------------

    public function logout(): void
    {
        session_destroy();
        $this->redirect('login');
    }
}
