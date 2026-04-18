<!-- ===== Enrollment Committee: Enrollment View ===== -->
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2>🎓 Enrollment Committee</h2>
        <p class="text-muted">Consolidated clearance summary for enrollment recommendation. View each student's full clearance standing.</p>
    </div>
    <a href="<?= BASE_URL ?>adviser/clearances" class="btn btn-secondary">← Back to Clearance Status</a>
</div>

<!-- ===== Filter Bar ===== -->
<form method="GET" action="<?= BASE_URL ?>adviser/enrollment" class="filter-bar">
    <div class="filter-group">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               placeholder="Search by name or ID…" class="form-control search-input" id="enroll-search">
    </div>
    <div class="filter-group">
        <select name="status" class="form-control" id="enroll-status-filter">
            <option value="all"     <?= $filterStatus === 'all'     ? 'selected' : '' ?>>All Statuses</option>
            <option value="flagged" <?= $filterStatus === 'flagged' ? 'selected' : '' ?>>🚩 Has Deficiency</option>
            <option value="cleared" <?= $filterStatus === 'cleared' ? 'selected' : '' ?>>✅ Fully Cleared</option>
            <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>⏳ In Progress</option>
        </select>
    </div>
    <?php if (!empty($courses)): ?>
    <div class="filter-group">
        <select name="course" class="form-control" id="enroll-course-filter">
            <option value="">All Courses</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= htmlspecialchars($course) ?>"
                    <?= $filterCourse === $course ? 'selected' : '' ?>>
                    <?= htmlspecialchars($course) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <?php if (!empty($yearLevels)): ?>
    <div class="filter-group">
        <select name="year" class="form-control" id="enroll-year-filter">
            <option value="">All Year Levels</option>
            <?php foreach ($yearLevels as $yr): ?>
                <option value="<?= $yr ?>" <?= $filterYear === (string)$yr ? 'selected' : '' ?>>
                    Year <?= $yr ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?= BASE_URL ?>adviser/enrollment" class="btn btn-secondary btn-sm">Reset</a>
</form>

<?php if (empty($clearances)): ?>
    <div class="empty-state">
        <div class="empty-icon">🎓</div>
        <h3>No clearances assigned</h3>
        <p>You haven't been assigned to any clearance yet. Contact your administrator.</p>
    </div>
<?php else: ?>
    <?php foreach ($clearances as $c): ?>
        <?php
        $totalHere   = count($c['students']);
        $cFlagged    = array_sum(array_map(fn($s) => $s['display_status'] === 'flagged' ? 1 : 0, $c['students']));
        $cCleared    = array_sum(array_map(fn($s) => $s['display_status'] === 'cleared' ? 1 : 0, $c['students']));
        $cPending    = $totalHere - $cFlagged - $cCleared;
        ?>
        <div class="table-container" style="margin-bottom:2rem;">

            <!-- Clearance header -->
            <div class="clearance-header-bar">
                <div>
                    <strong><?= htmlspecialchars($c['clearance_name']) ?></strong>
                    <span class="text-muted" style="font-size:.85rem; margin-left:.75rem;">
                        <?= htmlspecialchars($c['school_year']) ?>
                    </span>
                </div>
                <div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
                    <span class="badge badge-info"><?= $totalHere ?> students</span>
                    <?php if ($cFlagged > 0): ?>
                        <span class="badge badge-danger">🚩 <?= $cFlagged ?> with deficiency</span>
                    <?php endif; ?>
                    <span class="badge badge-success">✅ <?= $cCleared ?> cleared</span>
                    <span class="badge badge-warning">⏳ <?= $cPending ?> pending</span>
                </div>
            </div>

            <?php if (empty($c['students'])): ?>
                <p class="text-muted text-center" style="padding:1.5rem;">No students match the current filters.</p>
            <?php else: ?>
                <table class="data-table enrollment-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Course</th>
                            <th>Year / Section</th>
                            <th>Email</th>
                            <th>Clearance Standing</th>
                            <th>Deficiency Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rowNum = 1; foreach ($c['students'] as $s): ?>
                            <tr class="<?= $s['display_status'] === 'flagged' ? 'row-flagged' : ($s['display_status'] === 'cleared' ? 'row-cleared' : '') ?>">
                                <td class="text-muted"><?= $rowNum++ ?></td>
                                <td><?= htmlspecialchars($s['student_number']) ?></td>
                                <td><strong><?= htmlspecialchars($s['full_name']) ?></strong></td>
                                <td><?= htmlspecialchars($s['course']) ?></td>
                                <td>Year <?= $s['year_level'] ?> – <?= htmlspecialchars($s['section']) ?></td>
                                <td><a href="mailto:<?= htmlspecialchars($s['email']) ?>" class="email-link"><?= htmlspecialchars($s['email']) ?></a></td>
                                <td>
                                    <?php if ($s['display_status'] === 'flagged'): ?>
                                        <span class="standing-badge standing-flagged">🚩 NOT CLEARED</span>
                                    <?php elseif ($s['display_status'] === 'cleared'): ?>
                                        <span class="standing-badge standing-cleared">✅ CLEARED</span>
                                    <?php else: ?>
                                        <span class="standing-badge standing-pending">⏳ PENDING</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $deficiencies = array_filter($s['signatory_detail'], fn($sg) => $sg['status'] === 'flagged');
                                    ?>
                                    <?php if (!empty($deficiencies)): ?>
                                        <ul class="deficiency-list">
                                            <?php foreach ($deficiencies as $d): ?>
                                                <li>
                                                    <strong><?= htmlspecialchars($d['office']) ?>:</strong>
                                                    <?= htmlspecialchars($d['flag_note']) ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<style>
.filter-bar {
    display: flex; flex-wrap: wrap; gap: .75rem; align-items: center;
    margin-bottom: 1.5rem;
    padding: 1rem 1.25rem;
    background: var(--card-bg, #1e293b);
    border: 1px solid var(--border, #334155);
    border-radius: 10px;
}
.filter-group { display: flex; align-items: center; }
.search-input { min-width: 220px; }

.clearance-header-bar {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border, #334155);
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap:.5rem;
}

.row-flagged { background: rgba(239,68,68,.06) !important; }
.row-cleared { background: rgba(34,197,94,.04) !important; }

.enrollment-table th, .enrollment-table td { font-size: .85rem; vertical-align: top; }

.standing-badge {
    display: inline-block;
    padding: .25rem .75rem;
    border-radius: 6px;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .03em;
}
.standing-flagged { background: rgba(239,68,68,.15); color: #f87171; border: 1px solid rgba(239,68,68,.3); }
.standing-cleared { background: rgba(34,197,94,.12); color: #4ade80; border: 1px solid rgba(34,197,94,.3); }
.standing-pending { background: rgba(234,179,8,.1);  color: #facc15; border: 1px solid rgba(234,179,8,.25); }

.deficiency-list {
    margin: 0; padding: 0 0 0 1rem;
    font-size: .82rem;
    color: var(--text-muted, #94a3b8);
    font-style: italic;
    line-height: 1.5;
}
.deficiency-list li { margin-bottom: .25rem; }
.deficiency-list li strong { color: var(--text, #e2e8f0); font-style: normal; }

.email-link { color: var(--accent, #6366f1); text-decoration: none; font-size: .82rem; }
.email-link:hover { text-decoration: underline; }

.badge-danger {
    background: rgba(239,68,68,.15);
    color: #f87171;
    border: 1px solid rgba(239,68,68,.3);
    padding: .2rem .6rem; border-radius: 999px;
    font-size: .78rem; font-weight: 600;
}
</style>
