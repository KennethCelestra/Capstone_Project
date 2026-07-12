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
        
        <form action="<?= BASE_URL ?>profile/change-password" method="POST">
            <div class="form-group mb-3">
                <label>Current Password <span class="text-danger">*</span></label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            
            <div class="form-group mb-3">
                <label>New Password <span class="text-danger">*</span></label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            
            <div class="form-group mb-4">
                <label>Confirm New Password <span class="text-danger">*</span></label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary">
                Update Password
            </button>
        </form>
    </div>
</div>
