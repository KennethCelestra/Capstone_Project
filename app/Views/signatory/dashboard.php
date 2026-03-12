<div class="page-header">
    <h2>Dashboard</h2>
    <p>Welcome back, <strong><?= htmlspecialchars($userName) ?></strong>!</p>
</div>

<?php
$totalStudents = array_sum(array_column($clearances, 'total_students'));
$totalSigned   = array_sum(array_column($clearances, 'signed_count'));
$pending       = $totalStudents - $totalSigned;
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
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
            <span class="stat-value"><?= $pending ?></span>
            <span class="stat-label">Pending Signatures</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <span class="stat-value"><?= $totalSigned ?></span>
            <span class="stat-label">Signed</span>
        </div>
    </div>
</div>

<div class="quick-actions">
    <h3>Quick Actions</h3>
    <div class="action-buttons">
        <a href="<?= BASE_URL ?>signatory/clearances" class="btn btn-primary">✍️ Review & Sign Clearances</a>
    </div>
</div>