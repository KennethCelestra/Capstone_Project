<div class="page-header">
    <h2>My Clearances</h2>
</div>

<?php if (empty($clearances)): ?>
    <div class="empty-state">
        <div class="empty-icon">📋</div>
        <h3>No clearances assigned</h3>
        <p>You haven't been assigned to any clearance yet. Contact your administrator.</p>
    </div>
<?php else: ?>
    <?php foreach ($clearances as $c): ?>
        <div class="table-container" style="margin-bottom:1.5rem;">
            <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong><?= htmlspecialchars($c['clearance_name']) ?></strong>
                    <span class="text-muted" style="font-size:.85rem; margin-left:.75rem"><?= htmlspecialchars($c['school_year']) ?></span>
                </div>
                <span class="badge badge-info"><?= count($c['students']) ?> students</span>
            </div>
            <?php if (empty($c['students'])): ?>
                <p class="text-center text-muted" style="padding:1rem">No students enrolled in this clearance.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Course</th>
                            <th>Year / Section</th>
                            <th>Progress</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($c['students'] as $s):
                            $total  = (int) $s['total_count'];
                            $signed = (int) $s['signed_count'];
                            $pct    = $total > 0 ? round(($signed / $total) * 100) : 0;
                            $isCleared = $total > 0 && $signed === $total;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($s['student_number']) ?></td>
                                <td><?= htmlspecialchars($s['full_name']) ?></td>
                                <td><?= htmlspecialchars($s['course']) ?></td>
                                <td><?= $s['year_level'] ?> – <?= htmlspecialchars($s['section']) ?></td>
                                <td>
                                    <div class="progress-bar-wrap">
                                        <div class="progress-bar" style="width:<?= $pct ?>%"></div>
                                    </div>
                                    <small><?= $signed ?>/<?= $total ?> signed</small>
                                </td>
                                <td>
                                    <span class="badge <?= $isCleared ? 'badge-success' : 'badge-warning' ?>">
                                        <?= $isCleared ? 'Cleared' : 'Pending' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
