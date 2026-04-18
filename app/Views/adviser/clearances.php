<!-- ===== Enrollment Committee: Student Clearance Status ===== -->
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2>Student Clearance Status</h2>
        <p class="text-muted">View clearance progress and deficiency flags for your assigned students.</p>
    </div>
    <a href="<?= BASE_URL ?>adviser/enrollment" class="btn btn-primary">🎓 Enrollment Committee View</a>
</div>

<!-- ===== Filter Bar ===== -->
<form method="GET" action="<?= BASE_URL ?>adviser/clearances" class="filter-bar">
    <div class="filter-group">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               placeholder="Search by name or ID…" class="form-control search-input" id="adv-search">
    </div>
    <div class="filter-group">
        <select name="status" class="form-control" id="adv-status-filter">
            <option value="all"     <?= $filterStatus === 'all'     ? 'selected' : '' ?>>All Statuses</option>
            <option value="flagged" <?= $filterStatus === 'flagged' ? 'selected' : '' ?>>🚩 Has Deficiency</option>
            <option value="cleared" <?= $filterStatus === 'cleared' ? 'selected' : '' ?>>✅ Fully Cleared</option>
            <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>⏳ In Progress</option>
        </select>
    </div>
    <?php if (!empty($courses)): ?>
    <div class="filter-group">
        <select name="course" class="form-control" id="adv-course-filter">
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
        <select name="year" class="form-control" id="adv-year-filter">
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
    <a href="<?= BASE_URL ?>adviser/clearances" class="btn btn-secondary btn-sm">Reset</a>
</form>

<?php if (empty($clearances)): ?>
    <div class="empty-state">
        <div class="empty-icon">📋</div>
        <h3>No clearances assigned</h3>
        <p>You haven't been assigned to any clearance yet. Contact your administrator.</p>
    </div>
<?php else: ?>
    <?php foreach ($clearances as $c): ?>
        <?php
        $cFlagged = array_sum(array_map(fn($s) => $s['display_status'] === 'flagged' ? 1 : 0, $c['students']));
        $cCleared = array_sum(array_map(fn($s) => $s['display_status'] === 'cleared' ? 1 : 0, $c['students']));
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
                    <span class="badge badge-info"><?= count($c['students']) ?> students</span>
                    <?php if ($cFlagged > 0): ?>
                        <span class="badge badge-danger">🚩 <?= $cFlagged ?> flagged</span>
                    <?php endif; ?>
                    <span class="badge badge-success">✅ <?= $cCleared ?> cleared</span>
                </div>
            </div>

            <?php if (empty($c['students'])): ?>
                <p class="text-muted text-center" style="padding:1.5rem;">No students match the current filters.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Course / Year</th>
                            <th>Overall Status</th>
                            <th>Signatory Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($c['students'] as $s): ?>
                            <?php $rowId = 'detail-' . $c['clearance_id'] . '-' . $s['id']; ?>
                            <tr class="<?= $s['display_status'] === 'flagged' ? 'row-flagged' : '' ?>">
                                <td><?= htmlspecialchars($s['student_number']) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($s['full_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($s['email']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($s['course']) ?> / Year <?= $s['year_level'] ?> – <?= htmlspecialchars($s['section']) ?></td>
                                <td>
                                    <?php if ($s['display_status'] === 'flagged'): ?>
                                        <span class="badge badge-danger">🚩 Has Deficiency</span>
                                    <?php elseif ($s['display_status'] === 'cleared'): ?>
                                        <span class="badge badge-success">✅ Fully Cleared</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">⏳ In Progress</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-secondary btn-sm"
                                            onclick="toggleDetail('<?= $rowId ?>')">
                                        View Detail ▾
                                    </button>
                                </td>
                            </tr>
                            <!-- Expandable detail row -->
                            <tr id="<?= $rowId ?>" class="detail-row" style="display:none;">
                                <td colspan="5">
                                    <div class="signatory-detail-grid">
                                        <?php foreach ($s['signatory_detail'] as $sg): ?>
                                            <div class="signatory-detail-card <?= $sg['status'] === 'flagged' ? 'card-flagged' : ($sg['status'] === 'cleared' ? 'card-cleared' : '') ?>">
                                                <div class="sd-header">
                                                    <span class="sd-office"><?= htmlspecialchars($sg['office']) ?></span>
                                                    <span class="sd-name text-muted"><?= htmlspecialchars($sg['signatory_name']) ?></span>
                                                </div>
                                                <div class="sd-status">
                                                    <?php if ($sg['status'] === 'flagged'): ?>
                                                        <span class="badge badge-danger">🚩 Flagged</span>
                                                        <?php if (!empty($sg['flag_note'])): ?>
                                                            <p class="sd-note"><?= nl2br(htmlspecialchars($sg['flag_note'])) ?></p>
                                                        <?php endif; ?>
                                                    <?php elseif ($sg['status'] === 'cleared'): ?>
                                                        <span class="badge badge-success">✅ Cleared</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">⏳ Pending</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
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
    flex-wrap: wrap; gap: .5rem;
}

.row-flagged { background: rgba(239,68,68,.06) !important; }

.detail-row td { padding: 0 !important; border-top: none !important; }

.signatory-detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    gap: .75rem;
    padding: 1rem 1.25rem;
    background: rgba(0,0,0,.15);
}
.signatory-detail-card {
    background: var(--card-bg, #1e293b);
    border: 1px solid var(--border, #334155);
    border-radius: 8px;
    padding: .875rem;
}
.signatory-detail-card.card-flagged {
    border-color: rgba(239,68,68,.5);
    background: rgba(239,68,68,.05);
}
.signatory-detail-card.card-cleared {
    border-color: rgba(34,197,94,.4);
    background: rgba(34,197,94,.04);
}
.sd-header { display: flex; flex-direction: column; margin-bottom: .5rem; }
.sd-office { font-weight: 600; font-size: .9rem; }
.sd-name   { font-size: .8rem; }
.sd-status { display: flex; flex-direction: column; gap: .35rem; }
.sd-note   { font-size: .82rem; color: var(--text-muted, #94a3b8); font-style: italic; margin: .35rem 0 0; line-height: 1.4; }

.badge-danger {
    background: rgba(239,68,68,.15);
    color: #f87171;
    border: 1px solid rgba(239,68,68,.3);
    padding: .2rem .6rem; border-radius: 999px;
    font-size: .78rem; font-weight: 600;
}
</style>

<script>
function toggleDetail(rowId) {
    const row = document.getElementById(rowId);
    if (!row) return;
    const btn = row.previousElementSibling?.querySelector('button');
    if (row.style.display === 'none') {
        row.style.display = '';
        if (btn) btn.textContent = 'Hide Detail ▴';
    } else {
        row.style.display = 'none';
        if (btn) btn.textContent = 'View Detail ▾';
    }
}
</script>
