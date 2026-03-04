<?php

abstract class Controller
{
    protected function view(string $viewPath, array $data = []): void
    {
        extract($data);
        $fullPath = ROOT_PATH . '/app/Views/' . $viewPath . '.php';
        if (!file_exists($fullPath)) {
            die("View not found: {$fullPath}");
        }
        require_once $fullPath;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . ltrim($path, '/'));
        exit;
    }

    protected function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    protected function requireLogin(string $role = null): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }
        if ($role && $_SESSION['user_role'] !== $role) {
            $this->redirect('login');
        }
    }

    protected function getPost(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    protected function getGet(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function getFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
