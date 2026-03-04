<div class="page-header">
    <h2>Dashboard</h2>
    <p>Welcome back,
        <?= htmlspecialchars($userName) ?>!
    </p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <span class="stat-value">
                <?= $studentCount ?>
            </span>
            <span class="stat-label">Total Students</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👨‍🏫</div>
        <div class="stat-info">
            <span class="stat-value">
                <?= $adviserCount ?>
            </span>
            <span class="stat-label">Advisers</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✍️</div>
        <div class="stat-info">
            <span class="stat-value">
                <?= $signatoryCount ?>
            </span>
            <span class="stat-label">Signatories</span>
        </div>
    </div>
</div>

<div class="quick-actions">
    <h3>Quick Actions</h3>
    <div class="action-buttons">
        <a href="<?= BASE_URL ?>admin/students" class="btn btn-primary">Manage Students</a>
        <a href="<?= BASE_URL ?>admin/clearances" class="btn btn-secondary">View Clearances</a>
    </div>
</div>