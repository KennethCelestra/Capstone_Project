<div class="page-header mb-4">
    <h2>Dashboard</h2>
    <p class="text-muted">Welcome back, <strong><?= htmlspecialchars($userName) ?></strong>!</p>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card blue-card">
        <div class="stat-info">
            <span class="stat-value text-darkblue"><?= $clearanceCount ?></span>
            <span class="stat-label">Clearances</span>
        </div>
        <i class="bi bi-files text-muted opacity-50 display-6 ms-auto" style="margin-left: auto;"></i>
    </div>
    <div class="stat-card gold-card">
        <div class="stat-info">
            <span class="stat-value text-darkblue"><?= $studentCount ?></span>
            <span class="stat-label">Students</span>
        </div>
        <i class="bi bi-mortarboard text-muted opacity-50 display-6 ms-auto" style="margin-left: auto;"></i>
    </div>
    <div class="stat-card blue-card">
        <div class="stat-info">
            <span class="stat-value text-darkblue"><?= $signatoryCount ?></span>
            <span class="stat-label">Signatories</span>
        </div>
        <i class="bi bi-pen text-muted opacity-50 display-6 ms-auto" style="margin-left: auto;"></i>
    </div>
    <div class="stat-card gold-card">
        <div class="stat-info">
            <span class="stat-value text-darkblue"><?= $enrollmentCommitteeCount ?></span>
            <span class="stat-label">Enrollment Committee</span>
        </div>
        <i class="bi bi-people text-muted opacity-50 display-6 ms-auto" style="margin-left: auto;"></i>
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

<div class="dashboard-section blue-card p-4 mb-4" style="background:#fff; border-radius:8px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Overall Clearance Progress</h3>
        <span class="badge badge-info py-2 px-3"><?= $total ?> Total Students Enrolled</span>
    </div>
    <div class="progress-container">
        <div class="progress-stats mb-3 d-flex gap-4">
            <div class="stat-item d-flex align-items-center gap-2">
                <div class="stat-dot dot-cleared" style="width:12px;height:12px;border-radius:50%;background:var(--success);"></div>
                <span class="fw-bold"><?= $cleared ?> <small class="text-muted fw-normal">Fully Cleared</small></span>
            </div>
            <div class="stat-item d-flex align-items-center gap-2">
                <div class="stat-dot dot-flagged" style="width:12px;height:12px;border-radius:50%;background:var(--danger);"></div>
                <span class="fw-bold"><?= $flagged ?> <small class="text-muted fw-normal">With Deficiency</small></span>
            </div>
            <div class="stat-item d-flex align-items-center gap-2">
                <div class="stat-dot dot-pending" style="width:12px;height:12px;border-radius:50%;background:var(--warning);"></div>
                <span class="fw-bold"><?= $pending ?> <small class="text-muted fw-normal">In Progress</small></span>
            </div>
        </div>
        <div class="progress-bar-stacked" style="height:12px; border-radius:6px; overflow:hidden; display:flex; background:var(--surface2);">
            <?php if ($cleared > 0): ?>
                <div class="progress-segment segment-cleared" style="width: <?= $clearedPct ?>%; background:var(--success);" title="<?= $cleared ?> Cleared"></div>
            <?php endif; ?>
            <?php if ($flagged > 0): ?>
                <div class="progress-segment segment-flagged" style="width: <?= $flaggedPct ?>%; background:var(--danger);" title="<?= $flagged ?> Flagged"></div>
            <?php endif; ?>
            <?php if ($pending > 0): ?>
                <div class="progress-segment segment-pending" style="width: <?= $pendingPct ?>%; background:var(--warning);" title="<?= $pending ?> Pending"></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="dashboard-section gold-card" style="background:#fff; border-radius:8px; overflow:hidden;">
    <div class="p-4 border-bottom">
        <h3 class="mb-0">Active Clearances Summary</h3>
    </div>
    <?php if (empty($clearances)): ?>
        <div class="p-4 text-center">
            <p class="text-muted">No active clearances found.</p>
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
                        <th class="py-3 px-4 text-end" style="white-space: nowrap;">Action</th>
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
                        <tr class="border-bottom">
                            <td class="py-3 px-4" data-label="Clearance Name"><strong><?= htmlspecialchars($c['clearance_name']) ?></strong></td>
                            <td class="py-3 px-4 text-muted" data-label="School Year"><?= htmlspecialchars($c['school_year']) ?></td>
                            <td class="py-3 px-4 text-center fw-bold" data-label="Students"><?= $cTotal ?></td>
                            <td class="py-3 px-4" data-label="Progress">
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
                            <td class="py-3 px-4 text-end" data-label="Action">
                                <div class="action-cell">
                                    <a href="<?= BASE_URL ?>admin/clearances/detail?id=<?= $c['clearance_id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-gear"></i> Manage
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
