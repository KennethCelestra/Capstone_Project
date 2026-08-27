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

<div class="page-header mb-4" style="padding-bottom: 1.25rem; border-bottom: 1px solid var(--border);">
    <div>
        <h2 style="font-size: 1.6rem; font-weight: 700; margin-bottom: .2rem;">Dashboard</h2>
        <p class="text-muted" style="margin: 0; font-size: .95rem;">Welcome back, <strong style="color: var(--text);"><?= htmlspecialchars($userName) ?></strong>!</p>
    </div>
</div>

<div class="stats-grid mb-4" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.25rem;">
    <div class="stat-card" style="border-top: 4px solid var(--primary); border-radius: 10px; padding: 1.25rem 1.5rem; background: var(--surface); box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: transform .2s, box-shadow .2s;">
        <div class="stat-info" style="flex: 1;">
            <span class="stat-value" style="font-size: 2rem; font-weight: 800; color: var(--primary);"><?= count($clearances) ?></span>
            <span class="stat-label" style="font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .3rem;">Assigned Clearances</span>
        </div>
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(37,99,235,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="bi bi-folder" style="font-size: 1.4rem; color: var(--primary);"></i>
        </div>
    </div>
    <div class="stat-card" style="border-top: 4px solid var(--secondary); border-radius: 10px; padding: 1.25rem 1.5rem; background: var(--surface); box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: transform .2s, box-shadow .2s;">
        <div class="stat-info" style="flex: 1;">
            <span class="stat-value" style="font-size: 2rem; font-weight: 800; color: var(--secondary);"><?= $totalStudents ?></span>
            <span class="stat-label" style="font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .3rem;">Total Students</span>
        </div>
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245,158,11,0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="bi bi-people" style="font-size: 1.4rem; color: var(--secondary);"></i>
        </div>
    </div>
    <div class="stat-card" style="border-top: 4px solid var(--danger); border-radius: 10px; padding: 1.25rem 1.5rem; background: var(--surface); box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: transform .2s, box-shadow .2s;">
        <div class="stat-info" style="flex: 1;">
            <span class="stat-value" style="font-size: 2rem; font-weight: 800; color: var(--danger);"><?= $totalFlagged ?></span>
            <span class="stat-label" style="font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .3rem;">With Deficiency</span>
        </div>
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(220,38,38,0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="bi bi-exclamation-triangle" style="font-size: 1.4rem; color: var(--danger);"></i>
        </div>
    </div>
    <div class="stat-card" style="border-top: 4px solid var(--success); border-radius: 10px; padding: 1.25rem 1.5rem; background: var(--surface); box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: transform .2s, box-shadow .2s;">
        <div class="stat-info" style="flex: 1;">
            <span class="stat-value" style="font-size: 2rem; font-weight: 800; color: var(--success);"><?= $totalCleared ?></span>
            <span class="stat-label" style="font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .3rem;">Fully Cleared</span>
        </div>
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16,185,129,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="bi bi-check-circle" style="font-size: 1.4rem; color: var(--success);"></i>
        </div>
    </div>
</div>

<?php if ($totalFlagged > 0): ?>
    <div style="background: rgba(245,158,11,0.06); border: 1px solid rgba(245,158,11,0.3); border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
        <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.5rem; color: var(--warning); flex-shrink: 0;"></i>
        <div>
            <h5 style="margin: 0 0 .15rem; font-size: .95rem; font-weight: 700;">Attention Needed</h5>
            <span style="font-size: .9rem; color: var(--text-muted);">You have <strong style="color: var(--text);"><?= $totalFlagged ?></strong> student(s) with deficiencies across your assigned clearances.</span>
        </div>
    </div>
<?php endif; ?>

<div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: .6rem;">
        <i class="bi bi-folder2-open" style="color: var(--primary); font-size: 1.1rem;"></i>
        <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0; color: var(--text);">Your Assigned Clearances</h3>
    </div>
    <?php if (empty($clearances)): ?>
        <div style="padding: 3rem; text-align: center;">
            <i class="bi bi-folder-x" style="font-size: 2.5rem; color: var(--text-muted); opacity: .4;"></i>
            <p class="text-muted" style="margin-top: .75rem; font-size: .95rem;">No clearances assigned.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr style="background: var(--surface2);">
                        <th>Clearance Name</th>
                        <th>School Year</th>
                        <th class="text-center">Students</th>
                        <th style="width: 30%;">Progress</th>
                        <th class="text-end"></th>
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
                        <tr class="border-bottom" style="transition: background .15s;" onmouseenter="this.style.background='var(--surface2)'" onmouseleave="this.style.background=''">
                            <td><strong><?= htmlspecialchars($c['clearance_name']) ?></strong></td>
                            <td class="text-muted"><?= htmlspecialchars($c['school_year']) ?></td>
                            <td class="text-center fw-bold"><?= $cTotal ?></td>
                            <td>
                                <div class="progress-bar-stacked mb-2" style="height: 6px; border-radius: 4px; overflow: hidden; display: flex; background: var(--surface2);">
                                    <?php if ($cCleared > 0): ?>
                                        <div class="progress-segment" style="width: <?= $cClearedPct ?>%; background: var(--success);" title="<?= $cCleared ?> Cleared"></div>
                                    <?php endif; ?>
                                    <?php if ($cFlagged > 0): ?>
                                        <div class="progress-segment" style="width: <?= $cFlaggedPct ?>%; background: var(--danger);" title="<?= $cFlagged ?> Flagged"></div>
                                    <?php endif; ?>
                                    <?php if ($cPending > 0): ?>
                                        <div class="progress-segment" style="width: <?= $cPendingPct ?>%; background: var(--warning);" title="<?= $cPending ?> Pending"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mini-stats d-flex gap-2" style="font-size: 0.75rem;">
                                    <span title="Fully Cleared" class="text-success"><i class="bi bi-check-circle-fill"></i> <?= $cCleared ?></span>
                                    <span title="With Deficiency" class="text-danger"><i class="bi bi-exclamation-circle-fill"></i> <?= $cFlagged ?></span>
                                    <span title="In Progress" class="text-warning"><i class="bi bi-clock-fill"></i> <?= $cPending ?></span>
                                </div>
                            </td>
                            <td class="text-end">
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
