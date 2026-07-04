<div class="page-header">
    <h2>Dashboard</h2>
    <p>Welcome back, <strong><?= htmlspecialchars($userName) ?></strong>!</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-value"><?= $clearanceCount ?></span>
            <span class="stat-label">Clearances</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-value"><?= $studentCount ?></span>
            <span class="stat-label">Students</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-value"><?= $signatoryCount ?></span>
            <span class="stat-label">Signatories</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-value"><?= $adviserCount ?></span>
            <span class="stat-label">Enrollment Committee</span>
        </div>
    </div>
</div>

<div class="quick-actions">
    <h3>Quick Actions</h3>
    <div class="action-buttons">
        <a href="<?= BASE_URL ?>admin/clearances" class="btn btn-primary">Manage Clearances</a>
        <a href="<?= BASE_URL ?>admin/students"   class="btn btn-secondary">Manage Students</a>
        <a href="<?= BASE_URL ?>admin/signatories" class="btn btn-secondary">Manage Signatories</a>
        <a href="<?= BASE_URL ?>admin/advisers"    class="btn btn-secondary">Manage Enrollment Committee</a>
    </div>
</div>

<div class="info-box" style="margin-top:2rem;">
    <h3>Getting Started</h3>
    <ol style="margin-top:.75rem;line-height:2;">
        <li><strong>Create a Clearance</strong> — go to <em>Clearances</em> and click "New Clearance".</li>
        <li><strong>Upload Students</strong> — inside a clearance, upload a CSV file of students.</li>
        <li><strong>Assign Signatories</strong> — inside a clearance, assign who needs to sign.</li>
        <li><strong>Assign Enrollment Committee</strong> — assign enrollment committee members who can view student clearance progress.</li>
        <li><strong>Share Credentials</strong> — go to <em>Signatories</em> / <em>Enrollment Committee</em> to see their login info.</li>
    </ol>
</div>
