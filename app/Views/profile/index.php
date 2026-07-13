<div class="page-header mb-4">
    <div>
        <h2>My Profile</h2>
        <p class="text-muted">Manage your account details and password.</p>
    </div>
</div>

<div style="max-width: 600px;">
    <!-- User Info -->
    <div style="background:#fff; border: 1px solid var(--border); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: var(--shadow);">
        <h3 style="font-size: 1.1rem; margin-bottom: 1rem;"><i class="bi bi-person text-primary me-2"></i> Account Details</h3>
        <p style="margin-bottom: 0.5rem;"><strong>Name:</strong> <?= htmlspecialchars($userName) ?></p>
        <p style="margin-bottom: 0;"><strong>Role:</strong> <?= ucfirst(htmlspecialchars($role)) ?></p>
    </div>

    <!-- Change Password -->
    <div style="background:#fff; border: 1px solid var(--border); border-radius: 8px; padding: 1.5rem; box-shadow: var(--shadow);">
        <h3 style="font-size: 1.1rem; margin-bottom: 1rem;"><i class="bi bi-shield-lock text-primary me-2"></i> Change Password</h3>
        
        <form action="<?= BASE_URL ?>profile/change-password" method="POST" id="changePasswordForm" onsubmit="return validateProfilePasswords(event)">
            <?php if (!empty($flash) && $flash['type'] === 'error'): ?>
                <div class="alert alert-danger mb-3 p-3 rounded" style="background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;">
                    <i class="bi bi-exclamation-octagon me-2"></i> <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="form-group mb-3">
                <label>Current Password <span class="text-danger">*</span></label>
                <input type="password" name="current_password" class="form-control" required
                       style="<?= (!empty($flash) && $flash['message'] === 'Incorrect current password.') ? 'border-color: #dc3545;' : '' ?>">
                <?php if (!empty($flash) && $flash['message'] === 'Incorrect current password.'): ?>
                    <small class="text-danger mt-1 d-block">The current password you entered is incorrect.</small>
                <?php endif; ?>
            </div>
            
            <div class="form-group mb-3">
                <label>New Password <span class="text-danger">*</span></label>
                <input type="password" id="new_password" name="new_password" class="form-control" required oninput="clearPasswordError()">
            </div>
            
            <div class="form-group mb-4">
                <label>Confirm New Password <span class="text-danger">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required oninput="clearPasswordError()">
                <small id="passwordMatchError" style="display:none; color:#dc3545; margin-top:4px;">Passwords do not match.</small>
            </div>
            
            <button type="submit" class="btn btn-primary" id="updatePasswordBtn">
                Update Password
            </button>
        </form>
    </div>
</div>

<!-- Success Modal -->
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
