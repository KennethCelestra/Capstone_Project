<?php
// Compute totals across all clearances for the dashboard
$totalStudents = 0;
$totalFlagged  = 0;
$totalCleared  = 0;
$totalPending  = 0;

foreach ($clearances as $c) {
    $totalStudents += (int)$c['total_students'];
    $totalFlagged  += (int)$c['flagged_count'];
    $totalCleared  += (int)$c['cleared_count'];
    $totalPending  += (int)$c['pending_count'];
}
?>

<div class="page-header">
    <h2>Dashboard</h2>
    <p>Welcome back, <strong><?= htmlspecialchars($userName) ?></strong>!</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-info">
            <span class="stat-value"><?= count($clearances) ?></span>
            <span class="stat-label">Assigned Clearances</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <span class="stat-value"><?= $totalStudents ?></span>
            <span class="stat-label">Total Students</span>
        </div>
    </div>
    <div class="stat-card" style="border-top: 3px solid var(--danger, #ef4444);">
        <div class="stat-icon">🚩</div>
        <div class="stat-info">
            <span class="stat-value" style="color: var(--danger, #ef4444);"><?= $totalFlagged ?></span>
            <span class="stat-label">Flagged</span>
        </div>
    </div>
    <div class="stat-card" style="border-top: 3px solid var(--success, #22c55e);">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <span class="stat-value" style="color: var(--success, #22c55e);"><?= $totalCleared ?></span>
            <span class="stat-label">Cleared</span>
        </div>
    </div>
</div>

<div class="quick-actions">
    <h3>Quick Actions</h3>
    <div class="action-buttons">
        <a href="<?= BASE_URL ?>signatory/clearances" class="btn btn-primary">📋 Manage Student Clearances</a>
        <?php if ($totalFlagged > 0): ?>
            <a href="<?= BASE_URL ?>signatory/confirm" class="btn btn-danger">
                🚩 Review & Send Deficiency Emails (<?= $totalFlagged ?> flagged)
            </a>
        <?php endif; ?>
    </div>
</div>