<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= APP_NAME ?>
    </title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="app-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <span class="logo-icon">🎓</span>
                <span class="logo-text">
                    <?= APP_NAME ?>
                </span>
            </div>
            <nav class="sidebar-nav">
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>admin/dashboard"
                        class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">📊</span> Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>admin/students"
                        class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/students') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">👥</span> Students
                    </a>
                    <a href="<?= BASE_URL ?>admin/advisers"
                        class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/advisers') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">👨‍🏫</span> Advisers
                    </a>
                    <a href="<?= BASE_URL ?>admin/signatories"
                        class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/signatories') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">✍️</span> Signatories
                    </a>
                    <a href="<?= BASE_URL ?>admin/clearances"
                        class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/clearances') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">📋</span> Clearances
                    </a>
                <?php elseif ($_SESSION['user_role'] === 'adviser'): ?>
                    <a href="<?= BASE_URL ?>adviser/dashboard"
                        class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">📊</span> Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>adviser/clearances"
                        class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/clearances') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">📋</span> Clearances
                    </a>
                <?php elseif ($_SESSION['user_role'] === 'signatory'): ?>
                    <a href="<?= BASE_URL ?>signatory/dashboard"
                        class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">📊</span> Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>signatory/clearances"
                        class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/clearances') !== false ? 'active' : '' ?>">
                        <span class="nav-icon">✍️</span> Sign Clearances
                    </a>
                <?php endif; ?>
            </nav>
            <div class="sidebar-footer">
                <span class="user-name">👤
                    <?= htmlspecialchars($userName) ?>
                </span>
                <a href="<?= BASE_URL ?>logout" class="logout-btn">Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php if (!empty($flash)): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>
            <?php require_once ROOT_PATH . '/app/Views/' . $content . '.php'; ?>
        </main>
    </div>
</body>

</html>