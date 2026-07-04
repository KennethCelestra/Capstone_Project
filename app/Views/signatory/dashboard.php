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
        <div class="stat-info">
            <span class="stat-value"><?= count($clearances) ?></span>
            <span class="stat-label">Assigned Clearances</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-value"><?= $totalStudents ?></span>
            <span class="stat-label">Total Students</span>
        </div>
    </div>
    <div class="stat-card" style="border-top: 3px solid var(--danger, #b91c1c);">
        <div class="stat-info">
            <span class="stat-value" style="color: var(--danger, #b91c1c);"><?= $totalFlagged ?></span>
            <span class="stat-label">Flagged</span>
        </div>
    </div>
    <div class="stat-card" style="border-top: 3px solid var(--success, #15803d);">
        <div class="stat-info">
            <span class="stat-value" style="color: var(--success, #15803d);"><?= $totalCleared ?></span>
            <span class="stat-label">Cleared</span>
        </div>
    </div>
</div>

<?php if ($totalPending > 0): ?>
    <div class="alert-banner alert-info">
        <strong>Pending Actions:</strong> You have <?= $totalPending ?> student(s) awaiting your review.
    </div>
<?php endif; ?>

<div class="dashboard-section">
    <h3>Your Assigned Clearances</h3>
    <?php if (empty($clearances)): ?>
        <p class="text-muted">No clearances assigned.</p>
    <?php else: ?>
        <table class="mini-table">
            <thead>
                <tr>
                    <th>Clearance Name</th>
                    <th>School Year</th>
                    <th>Students</th>
                    <th>Progress</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clearances as $c): ?>
                    <?php
                    $cTotal = (int)$c['total_students'];
                    $cCleared = (int)$c['cleared_count'];
                    $cFlagged = (int)$c['flagged_count'];
                    $cPending = (int)$c['pending_count'];
                    $cClearedPct = $cTotal > 0 ? ($cCleared / $cTotal) * 100 : 0;
                    $cFlaggedPct = $cTotal > 0 ? ($cFlagged / $cTotal) * 100 : 0;
                    $cPendingPct = $cTotal > 0 ? ($cPending / $cTotal) * 100 : 0;
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['clearance_name']) ?></strong></td>
                        <td><?= htmlspecialchars($c['school_year']) ?></td>
                        <td><?= $cTotal ?></td>
                        <td style="min-width: 200px;">
                            <div class="progress-bar-stacked">
                                <?php if ($cCleared > 0): ?>
                                    <div class="progress-segment segment-cleared" style="width: <?= $cClearedPct ?>%" title="<?= $cCleared ?> Cleared"></div>
                                <?php endif; ?>
                                <?php if ($cFlagged > 0): ?>
                                    <div class="progress-segment segment-flagged" style="width: <?= $cFlaggedPct ?>%" title="<?= $cFlagged ?> Flagged"></div>
                                <?php endif; ?>
                                <?php if ($cPending > 0): ?>
                                    <div class="progress-segment segment-pending" style="width: <?= $cPendingPct ?>%" title="<?= $cPending ?> Pending"></div>
                                <?php endif; ?>
                            </div>
                            <div class="mini-stats">
                                <span title="Cleared">C: <?= $cCleared ?></span>
                                <span title="Flagged">F: <?= $cFlagged ?></span>
                                <span title="Pending">P: <?= $cPending ?></span>
                            </div>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>signatory/clearances?cid=<?= $c['clearance_id'] ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Open &rarr;</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>