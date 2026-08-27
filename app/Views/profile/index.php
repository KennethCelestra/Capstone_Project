<div class="page-header mb-4">
    <div>
        <h2>My Profile</h2>
        <p class="text-muted">Manage your account details and password.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; max-width: 960px;">
    <!-- Account Details Form -->
    <div style="background:#fff; border: 1px solid var(--border); border-radius: 8px; padding: 1.5rem; box-shadow: var(--shadow);">
        <h3 style="font-size: 1.1rem; margin-bottom: 1rem;"><i class="bi bi-person-badge text-primary me-2"></i> Account Details</h3>
        
        <?php if (!empty($flash) && $flash['type'] === 'success_info'): ?>
            <div class="alert alert-success mb-3 p-3 rounded" style="background:#d4edda;color:#155724;border:1px solid #c3e6cb;">
                <i class="bi bi-check-circle me-2"></i> <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php elseif (!empty($flash) && $flash['type'] === 'error' && str_contains($flash['message'], 'email')): ?>
            <div class="alert alert-danger mb-3 p-3 rounded" style="background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;">
                <i class="bi bi-exclamation-octagon me-2"></i> <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>profile/update-info" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            
            <div class="form-group mb-3">
                <label class="form-label font-semibold mb-1">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($userName ?? '') ?>" required>
            </div>

            <div class="form-group mb-3">
                <label class="form-label font-semibold mb-1">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($userEmail ?? '') ?>" required>
            </div>

            <div class="form-group mb-4">
                <label class="form-label font-semibold mb-1">Role</label>
                <input type="text" class="form-control" value="<?= ucfirst(htmlspecialchars($role ?? '')) ?>" disabled style="background:#f8f9fa; color:#6c757d;">
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Save Changes
            </button>
        </form>
    </div>

    <!-- Change Password Form -->
    <div style="background:#fff; border: 1px solid var(--border); border-radius: 8px; padding: 1.5rem; box-shadow: var(--shadow);">
        <h3 style="font-size: 1.1rem; margin-bottom: 1rem;"><i class="bi bi-shield-lock text-primary me-2"></i> Change Password</h3>
        
        <form action="<?= BASE_URL ?>profile/change-password" method="POST" id="changePasswordForm" onsubmit="return validateProfilePasswords(event)">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <?php if (!empty($flash) && $flash['type'] === 'error' && !str_contains($flash['message'], 'email')): ?>
                <div class="alert alert-danger mb-3 p-3 rounded" style="background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;">
                    <i class="bi bi-exclamation-octagon me-2"></i> <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="form-group mb-3">
                <label class="form-label font-semibold mb-1">Current Password <span class="text-danger">*</span></label>
                <input type="password" name="current_password" class="form-control" required
                       style="<?= (!empty($flash) && $flash['message'] === 'Incorrect current password.') ? 'border-color: #dc3545;' : '' ?>">
                <?php if (!empty($flash) && $flash['message'] === 'Incorrect current password.'): ?>
                    <small class="text-danger mt-1 d-block">The current password you entered is incorrect.</small>
                <?php endif; ?>
            </div>
            
            <div class="form-group mb-3">
                <label class="form-label font-semibold mb-1">New Password <span class="text-danger">*</span></label>
                <input type="password" id="new_password" name="new_password" class="form-control" required oninput="clearPasswordError()">
            </div>
            
            <div class="form-group mb-4">
                <label class="form-label font-semibold mb-1">Confirm New Password <span class="text-danger">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required oninput="clearPasswordError()">
                <small id="passwordMatchError" style="display:none; color:#dc3545; margin-top:4px;">Passwords do not match.</small>
            </div>
            
            <button type="submit" class="btn btn-primary" id="updatePasswordBtn">
                <i class="bi bi-key me-1"></i> Update Password
            </button>
        </form>
    </div>
</div>

<!-- Success Modal for Password -->
<div id="password-success-modal" class="modal" style="display:none;" onclick="closeSuccessModal(event)">
    <div class="modal-box" style="text-align: center; padding: 2rem;">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
        <h3 class="mt-3">Success!</h3>
        <p class="text-muted">Your password has been successfully changed.</p>
        <button type="button" class="btn btn-primary mt-3" onclick="document.getElementById('password-success-modal').style.display='none'">OK</button>
    </div>
</div>

<script>
function clearPasswordError() {
    document.getElementById('passwordMatchError').style.display = 'none';
    document.getElementById('confirm_password').style.borderColor = '';
}

function checkPasswordMatch() {
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;

    if (newPass !== confirmPass) {
        document.getElementById('passwordMatchError').style.display = 'block';
        document.getElementById('confirm_password').style.borderColor = '#dc3545';
        return false;
    }

    clearPasswordError();
    return true;
}

function validateProfilePasswords(event) {
    if (!checkPasswordMatch()) {
        event.preventDefault();
        return false;
    }
    return true;
}

function closeSuccessModal(e) {
    if (e.target === document.getElementById('password-success-modal')) {
        document.getElementById('password-success-modal').style.display = 'none';
    }
}

<?php if (!empty($flash) && $flash['type'] === 'success'): ?>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('password-success-modal').style.display = 'flex';
});
<?php endif; ?>
</script>
