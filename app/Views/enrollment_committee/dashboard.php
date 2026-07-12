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

<div class="page-header mb-4">
    <h2>Dashboard</h2>
    <p class="text-muted">Welcome back, <strong><?= htmlspecialchars($userName) ?></strong>!</p>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card blue-card">
        <div class="stat-info">
            <span class="stat-value text-darkblue"><?= count($clearances) ?></span>
            <span class="stat-label">Assigned Clearances</span>
        </div>
        <i class="bi bi-folder text-muted opacity-50 display-6 ms-auto" style="margin-left: auto;"></i>
    </div>
    <div class="stat-card gold-card">
        <div class="stat-info">
            <span class="stat-value text-darkblue"><?= $totalStudents ?></span>
            <span class="stat-label">Total Students</span>
        </div>
        <i class="bi bi-people text-muted opacity-50 display-6 ms-auto" style="margin-left: auto;"></i>
    </div>
    <div class="stat-card" style="border-top: 6px solid var(--danger); background:#fff; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1);">
        <div class="stat-info">
            <span class="stat-value" style="color: var(--danger);"><?= $totalFlagged ?></span>
            <span class="stat-label">With Deficiency</span>
        </div>
        <i class="bi bi-exclamation-triangle text-muted opacity-50 display-6 ms-auto" style="margin-left: auto;"></i>
    </div>
    <div class="stat-card" style="border-top: 6px solid var(--success); background:#fff; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1);">
        <div class="stat-info">
            <span class="stat-value" style="color: var(--success);"><?= $totalCleared ?></span>
            <span class="stat-label">Fully Cleared</span>
        </div>
        <i class="bi bi-check-circle text-muted opacity-50 display-6 ms-auto" style="margin-left: auto;"></i>
    </div>
</div>

<?php if ($totalFlagged > 0): ?>
    <div class="alert alert-warning d-flex align-items-center gap-3">
        <i class="bi bi-exclamation-triangle-fill display-6 text-warning"></i>
        <div>
            <h5 class="mb-0">Attention Needed</h5>
            <span>You have <strong><?= $totalFlagged ?></strong> student(s) with deficiencies across your assigned clearances.</span>
        </div>
    </div>
<?php endif; ?>

<div class="dashboard-section blue-card" style="background:#fff; border-radius:8px; overflow:hidden;">
    <div class="p-4 border-bottom">
        <h3 class="mb-0">Your Assigned Clearances</h3>
    </div>
    <?php if (empty($clearances)): ?>
        <div class="p-4 text-center">
            <p class="text-muted">No clearances assigned.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr style="background: var(--surface2);">
                        <th class="py-3 px-4">Clearance Name</th>
                        <th class="py-3 px-4">School Year</th>
                        <th class="py-3 px-4 text-center">Students</th>
                        <th class="py-3 px-4" style="width: 30%;">Progress</th>
                        <th class="py-3 px-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clearances as $c): ?>
                        <?php
                        $cTotal = count($c['students']);
                        $cCleared = (int)$c['cleared_total'];
                        $cFlagged = (int)$c['flagged_total'];
                        $cPending = (int)$c['pending_total'];
                        $cClearedPct = $cTotal > 0 ? ($cCleared / $cTotal) * 100 : 0;
                        $cFlaggedPct = $cTotal > 0 ? ($cFlagged / $cTotal) * 100 : 0;
                        $cPendingPct = $cTotal > 0 ? ($cPending / $cTotal) * 100 : 0;
                        ?>
                        <tr class="border-bottom">
                            <td class="py-3 px-4"><strong><?= htmlspecialchars($c['clearance_name']) ?></strong></td>
                            <td class="py-3 px-4 text-muted"><?= htmlspecialchars($c['school_year']) ?></td>
                            <td class="py-3 px-4 text-center fw-bold"><?= $cTotal ?></td>
                            <td class="py-3 px-4">
                                <div class="progress-bar-stacked mb-2" style="height:6px; border-radius:3px; overflow:hidden; display:flex; background:var(--surface2);">
                                    <?php if ($cCleared > 0): ?>
                                        <div class="progress-segment" style="width: <?= $cClearedPct ?>%; background:var(--success);" title="<?= $cCleared ?> Cleared"></div>
                                    <?php endif; ?>
                                    <?php if ($cFlagged > 0): ?>
                                        <div class="progress-segment" style="width: <?= $cFlaggedPct ?>%; background:var(--danger);" title="<?= $cFlagged ?> Flagged"></div>
                                    <?php endif; ?>
                                    <?php if ($cPending > 0): ?>
                                        <div class="progress-segment" style="width: <?= $cPendingPct ?>%; background:var(--warning);" title="<?= $cPending ?> Pending"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mini-stats d-flex gap-2" style="font-size: 0.75rem;">
                                    <span title="Fully Cleared" class="text-success"><i class="bi bi-check-circle-fill"></i> <?= $cCleared ?></span>
                                    <span title="With Deficiency" class="text-danger"><i class="bi bi-exclamation-circle-fill"></i> <?= $cFlagged ?></span>
                                    <span title="In Progress" class="text-warning"><i class="bi bi-clock-fill"></i> <?= $cPending ?></span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <a href="<?= BASE_URL ?>enrollment-committee/clearances?cid=<?= $c['clearance_id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
