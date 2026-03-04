<div class="page-header">
    <h2>My Students</h2>
</div>

<?php
$total = count($clearances);
$cleared = array_filter($clearances, fn($c) => $c['signed'] == $c['total'] && $c['total'] > 0);
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <span class="stat-value">
                <?= $total ?>
            </span>
            <span class="stat-label">Total Students</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <span class="stat-value">
                <?= count($cleared) ?>
            </span>
            <span class="stat-label">Fully Cleared</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
            <span class="stat-value">
                <?= $total - count($cleared) ?>
            </span>
            <span class="stat-label">Still Pending</span>
        </div>
    </div>
</div>

<div class="quick-actions">
    <a href="<?= BASE_URL ?>adviser/clearances" class="btn btn-primary">View Clearance Details</a>
</div>