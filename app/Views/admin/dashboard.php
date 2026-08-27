<div class="page-header mb-4" style="padding-bottom: 1.25rem; border-bottom: 1px solid var(--border);">
    <div>
        <h2 style="font-size: 1.6rem; font-weight: 700; margin-bottom: .2rem;">Dashboard</h2>
        <p class="text-muted" style="margin: 0; font-size: .95rem;">Welcome back, <strong style="color: var(--text);"><?= htmlspecialchars($userName) ?></strong>!</p>
    </div>
</div>

<div class="stats-grid mb-4" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.25rem;">
    <div class="stat-card" style="border-top: 4px solid var(--primary); border-radius: 10px; padding: 1.25rem 1.5rem; background: var(--surface); box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: transform .2s, box-shadow .2s;">
        <div class="stat-info" style="flex: 1;">
            <span class="stat-value" style="font-size: 2rem; font-weight: 800; color: var(--primary);"><?= $clearanceCount ?></span>
            <span class="stat-label" style="font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .3rem;">Clearances</span>
        </div>
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(37,99,235,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="bi bi-files" style="font-size: 1.4rem; color: var(--primary);"></i>
        </div>
    </div>
    <div class="stat-card" style="border-top: 4px solid var(--secondary); border-radius: 10px; padding: 1.25rem 1.5rem; background: var(--surface); box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: transform .2s, box-shadow .2s;">
        <div class="stat-info" style="flex: 1;">
            <span class="stat-value" style="font-size: 2rem; font-weight: 800; color: var(--secondary);"><?= $studentCount ?></span>
            <span class="stat-label" style="font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .3rem;">Students</span>
        </div>
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245,158,11,0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="bi bi-mortarboard" style="font-size: 1.4rem; color: var(--secondary);"></i>
        </div>
    </div>
    <div class="stat-card" style="border-top: 4px solid var(--primary); border-radius: 10px; padding: 1.25rem 1.5rem; background: var(--surface); box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: transform .2s, box-shadow .2s;">
        <div class="stat-info" style="flex: 1;">
            <span class="stat-value" style="font-size: 2rem; font-weight: 800; color: var(--primary);"><?= $signatoryCount ?></span>
            <span class="stat-label" style="font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .3rem;">Signatories</span>
        </div>
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(37,99,235,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="bi bi-pen" style="font-size: 1.4rem; color: var(--primary);"></i>
        </div>
    </div>
    <div class="stat-card" style="border-top: 4px solid var(--secondary); border-radius: 10px; padding: 1.25rem 1.5rem; background: var(--surface); box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: transform .2s, box-shadow .2s;">
        <div class="stat-info" style="flex: 1;">
            <span class="stat-value" style="font-size: 2rem; font-weight: 800; color: var(--secondary);"><?= $enrollmentCommitteeCount ?></span>
            <span class="stat-label" style="font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: .3rem;">Enrollment Committee</span>
        </div>
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245,158,11,0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="bi bi-people" style="font-size: 1.4rem; color: var(--secondary);"></i>
        </div>
    </div>
</div>

<div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: .6rem;">
        <i class="bi bi-list-check" style="color: var(--primary); font-size: 1.1rem;"></i>
        <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0; color: var(--text);">Active Clearances Summary</h3>
    </div>
    <?php if (empty($clearances)): ?>
        <div style="padding: 3rem; text-align: center;">
            <i class="bi bi-folder-x" style="font-size: 2.5rem; color: var(--text-muted); opacity: .4;"></i>
            <p class="text-muted" style="margin-top: .75rem; font-size: .95rem;">No active clearances found.</p>
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
                        <th class="text-end" style="white-space: nowrap;"></th>
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
                        <tr class="border-bottom" style="transition: background .15s;" onmouseenter="this.style.background='var(--surface2)'" onmouseleave="this.style.background=''">
                            <td data-label="Clearance Name"><strong><?= htmlspecialchars($c['clearance_name']) ?></strong></td>
                            <td class="text-muted" data-label="School Year"><?= htmlspecialchars($c['school_year']) ?></td>
                            <td class="text-center fw-bold" data-label="Students"><?= $cTotal ?></td>
                            <td data-label="Progress">
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
                            <td class="text-end" data-label="Action">
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
