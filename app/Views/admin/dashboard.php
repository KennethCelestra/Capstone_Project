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

<?php
$total = $overallProgress['total_students'];
$cleared = $overallProgress['cleared_total'];
$flagged = $overallProgress['flagged_total'];
$pending = $overallProgress['pending_total'];

$clearedPct = $total > 0 ? ($cleared / $total) * 100 : 0;
$flaggedPct = $total > 0 ? ($flagged / $total) * 100 : 0;
$pendingPct = $total > 0 ? ($pending / $total) * 100 : 0;
?>

<div class="dashboard-section">
    <h3>Overall Clearance Progress</h3>
    <div class="progress-container">
        <h4><?= $total ?> Total Students Enrolled</h4>
        <div class="progress-stats">
            <div class="stat-item">
                <div class="stat-dot dot-cleared"></div>
                <span><?= $cleared ?> Fully Cleared</span>
            </div>
            <div class="stat-item">
                <div class="stat-dot dot-flagged"></div>
                <span><?= $flagged ?> With Deficiency</span>
            </div>
            <div class="stat-item">
                <div class="stat-dot dot-pending"></div>
                <span><?= $pending ?> In Progress</span>
            </div>
        </div>
        <div class="progress-bar-stacked">
            <?php if ($cleared > 0): ?>
                <div class="progress-segment segment-cleared" style="width: <?= $clearedPct ?>%" title="<?= $cleared ?> Cleared"></div>
            <?php endif; ?>
            <?php if ($flagged > 0): ?>
                <div class="progress-segment segment-flagged" style="width: <?= $flaggedPct ?>%" title="<?= $flagged ?> Flagged"></div>
            <?php endif; ?>
            <?php if ($pending > 0): ?>
                <div class="progress-segment segment-pending" style="width: <?= $pendingPct ?>%" title="<?= $pending ?> Pending"></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="dashboard-section">
    <h3>Active Clearances Summary</h3>
    <?php if (empty($clearances)): ?>
        <p class="text-muted">No active clearances found.</p>
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
                    $cTotal = $c['student_count'];
                    $cCleared = $c['cleared_total'];
                    $cFlagged = $c['flagged_total'];
                    $cPending = $c['pending_total'];
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
                                <span title="Fully Cleared">C: <?= $cCleared ?></span>
                                <span title="With Deficiency">F: <?= $cFlagged ?></span>
                                <span title="In Progress">P: <?= $cPending ?></span>
                            </div>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>admin/clearances/detail?id=<?= $c['clearance_id'] ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Manage</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
