<?php
$totalStudents = 0;
$totalFlagged  = 0;
$totalCleared  = 0;

foreach ($clearances as $c) {
    foreach ($c['students'] as $s) {
        $totalStudents++;
        $flaggedCount = (int)($s['flagged_count'] ?? 0);
        $clearedCount = (int)($s['cleared_count'] ?? 0);
        $totalCount   = (int)($s['total_count']   ?? 0);

        if ($flaggedCount > 0) {
            $totalFlagged++;
        } elseif ($totalCount > 0 && $clearedCount === $totalCount) {
            $totalCleared++;
        }
    }
}
$totalPending = $totalStudents - $totalFlagged - $totalCleared;
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
            <span class="stat-label">With Deficiency</span>
        </div>
    </div>
    <div class="stat-card" style="border-top: 3px solid var(--success, #22c55e);">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <span class="stat-value" style="color: var(--success, #22c55e);"><?= $totalCleared ?></span>
            <span class="stat-label">Fully Cleared</span>
        </div>
    </div>
</div>

<div class="quick-actions">
    <h3>Quick Actions</h3>
    <div class="action-buttons">
        <a href="<?= BASE_URL ?>adviser/clearances" class="btn btn-primary">📋 View Clearance Status</a>
        <?php if ($totalFlagged > 0): ?>
            <a href="<?= BASE_URL ?>adviser/clearances?status=flagged" class="btn btn-danger">
                🚩 View Flagged Students (<?= $totalFlagged ?>)
            </a>
        <?php endif; ?>
    </div>
</div>
