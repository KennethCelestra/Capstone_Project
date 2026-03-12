<div class="page-header">
    <h2>Dashboard</h2>
    <p>Welcome back, <strong><?= htmlspecialchars($userName) ?></strong>!</p>
</div>

<?php
$totalStudents = 0;
$totalCleared  = 0;
foreach ($clearances as $c) {
    foreach ($c['students'] as $s) {
        $totalStudents++;
        if ($s['total_count'] > 0 && $s['signed_count'] == $s['total_count']) {
            $totalCleared++;
        }
    }
}
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-info">
            <span class="stat-value"><?= count($clearances) ?></span>
            <span class="stat-label">Clearances</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <span class="stat-value"><?= $totalStudents ?></span>
            <span class="stat-label">Total Students</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <span class="stat-value"><?= $totalCleared ?></span>
            <span class="stat-label">Fully Cleared</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
            <span class="stat-value"><?= $totalStudents - $totalCleared ?></span>
            <span class="stat-label">Still Pending</span>
        </div>
    </div>
</div>

<div class="quick-actions">
    <h3>Quick Actions</h3>
    <div class="action-buttons">
        <a href="<?= BASE_URL ?>adviser/clearances" class="btn btn-primary">View Clearance Details</a>
    </div>
</div>