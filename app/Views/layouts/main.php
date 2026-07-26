<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <!-- Add Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/isatu.css">
</head>

<body>
    <!-- Top Navbar -->
    <nav class="top-navbar">
        <div class="top-navbar-brand">
            <button type="button" class="sidebar-toggle-btn" id="sidebar-toggle" aria-label="Toggle menu">
                <i class="bi bi-list"></i>
            </button>
            <img src="<?= BASE_URL ?>css/logo.png" alt="ISAT-U Logo" class="nav-logo">
            <h5 class="nav-title"><?= APP_NAME ?></h5>
        </div>
    </nav>

    <div class="app-wrapper">
        <!-- Sidebar Overlay for mobile -->
        <div class="sidebar-overlay" id="sidebar-overlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar-menu">
            <nav class="sidebar-nav mt-3">
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>admin/dashboard"
                       class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'selected' : '' ?>">
                        <i class="bi bi-house me-2"></i> Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>admin/clearances"
                       class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/clearances') !== false && strpos($_SERVER['REQUEST_URI'], '/archived') === false ? 'selected' : '' ?>">
                        <i class="bi bi-file-earmark-text me-2"></i> Clearances
                    </a>
                    <a href="<?= BASE_URL ?>admin/archived-clearances"
                       class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/archived-clearances') !== false ? 'selected' : '' ?>">
                        <i class="bi bi-archive me-2"></i> Archived
                    </a>
                    <a href="<?= BASE_URL ?>admin/signatories"
                       class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/signatories') !== false ? 'selected' : '' ?>">
                        <i class="bi bi-pen me-2"></i> Signatories
                    </a>
                    <a href="<?= BASE_URL ?>admin/enrollment-committees"
                       class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/enrollment-committees') !== false ? 'selected' : '' ?>">
                        <i class="bi bi-people me-2"></i> Enrollment Committee
                    </a>
                <?php elseif ($_SESSION['user_role'] === 'enrollment_committee'): ?>
                    <a href="<?= BASE_URL ?>enrollment-committee/dashboard"
                       class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'selected' : '' ?>">
                        <i class="bi bi-house me-2"></i> Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>enrollment-committee/clearances"
                       class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/clearances') !== false ? 'selected' : '' ?>">
                        <i class="bi bi-file-earmark-check me-2"></i> My Clearances
                    </a>
                <?php elseif ($_SESSION['user_role'] === 'signatory'): ?>
                    <a href="<?= BASE_URL ?>signatory/dashboard"
                       class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'selected' : '' ?>">
                        <i class="bi bi-house me-2"></i> Dashboard
                    </a>
                    <a href="<?= BASE_URL ?>signatory/clearances"
                       class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/clearances') !== false) ? 'selected' : '' ?>">
                        <i class="bi bi-file-earmark-check me-2"></i> My Clearances
                    </a>
                <?php endif; ?>

                <div class="nav-divider" style="height: 1px; background: var(--border); margin: 1rem 0;"></div>
                <a href="<?= BASE_URL ?>profile"
                   class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/profile') !== false ? 'selected' : '' ?>">
                    <i class="bi bi-person me-2"></i> Profile / Password
                </a>
                <a href="<?= BASE_URL ?>logout" class="nav-link text-danger mt-auto">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content" id="main-content">

            
            <?php require_once ROOT_PATH . '/app/Views/' . $content . '.php'; ?>
        </main>
    </div>

    <!-- Generic Confirm Modal -->
    <div id="generic-confirm-modal" class="modal" style="display:none;" onclick="closeGenericConfirmModalOnOverlay(event)">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="generic-confirm-title">Confirm Action</h3>
                <button type="button" class="modal-close close-btn" onclick="closeGenericConfirmModal()">✕</button>
            </div>
            <div class="modal-body mb-4">
                <p id="generic-confirm-msg">Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeGenericConfirmModal()">Cancel</button>
                <button type="button" class="btn btn-danger" id="generic-confirm-btn">Confirm</button>
            </div>
        </div>
    </div>

    <script>
    let currentFormToSubmit = null;

    function confirmAction(formElement, message, btnText = 'Confirm', btnClass = 'btn-danger', title = 'Confirm Action') {
        document.getElementById('generic-confirm-title').textContent = title;
        document.getElementById('generic-confirm-msg').textContent = message;
        
        const confirmBtn = document.getElementById('generic-confirm-btn');
        confirmBtn.textContent = btnText;
        confirmBtn.className = 'btn ' + btnClass;
        
        currentFormToSubmit = formElement;
        document.getElementById('generic-confirm-modal').style.display = 'flex';
        return false; // Prevent form submit
    }

    function closeGenericConfirmModal() {
        document.getElementById('generic-confirm-modal').style.display = 'none';
        currentFormToSubmit = null;
    }

    function closeGenericConfirmModalOnOverlay(e) {
        if (e.target === document.getElementById('generic-confirm-modal')) {
            closeGenericConfirmModal();
        }
    }

    document.getElementById('generic-confirm-btn').addEventListener('click', function() {
        if (currentFormToSubmit) {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
            currentFormToSubmit.submit();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeGenericConfirmModal();
            closeSidebar();
        }
    });

    /* Mobile Sidebar Toggle */
    const sidebarToggleBtn = document.getElementById('sidebar-toggle');
    const sidebarMenu = document.getElementById('sidebar-menu');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    function toggleSidebar() {
        if (sidebarMenu) sidebarMenu.classList.toggle('open');
        if (sidebarOverlay) sidebarOverlay.classList.toggle('show');
    }

    function closeSidebar() {
        if (sidebarMenu) sidebarMenu.classList.remove('open');
        if (sidebarOverlay) sidebarOverlay.classList.remove('show');
    }

    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', toggleSidebar);
    }
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }
    </script>
    
    <?php if (!empty($_SESSION['bg_emails'])): ?>
    <script>
        // Silently process emails in the background
        fetch('<?= BASE_URL ?>api/process-bg-emails', {
            method: 'POST'
        }).catch(err => console.error('Background email trigger failed:', err));
    </script>
    <?php endif; ?>
</body>

</html>