<?php
/**
 * Enrollment Committee (Adviser): My Clearances — two-phase view
 *   $phase === 'select'  → clearance selection cards
 *   $phase === 'detail'  → student status table for selected clearance
 */
?>

<?php if ($phase === 'select'): ?>
<!-- =====================================================
     PHASE 1 — Clearance Selection Cards
     ===================================================== -->
<div class="page-header">
    <div>
        <h2>My Clearances</h2>
        <p class="text-muted">Select a clearance to view student clearance standing.</p>
    </div>
</div>

<?php if (empty($clearances)): ?>
    <div class="empty-state">
        <h3>No clearances assigned</h3>
        <p>You haven't been assigned to any clearance yet. Contact your administrator.</p>
    </div>
<?php else: ?>
    <div class="clearance-cards-grid">
        <?php foreach ($clearances as $c): ?>
            <?php
            $total   = count($c['students']);
            $flagged = $c['flagged_total'];
            $cleared = $c['cleared_total'];
            $pending = $c['pending_total'];
            ?>
            <a href="<?= BASE_URL ?>adviser/clearances?cid=<?= $c['clearance_id'] ?>"
               class="clearance-card <?= $flagged > 0 ? 'card-has-flags' : '' ?>">
                <div class="cc-header">
                    <div class="cc-title">
                        <strong><?= htmlspecialchars($c['clearance_name']) ?></strong>
                        <?php if (!empty($c['school_year'])): ?>
                            <span class="cc-year"><?= htmlspecialchars($c['school_year']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="cc-stats">
                    <div class="cc-stat">
                        <span class="cc-stat-num"><?= $total ?></span>
                        <span class="cc-stat-lbl">Students</span>
                    </div>
                    <?php if ($flagged > 0): ?>
                    <div class="cc-stat cc-stat-flagged">
                        <span class="cc-stat-num"><?= $flagged ?></span>
                        <span class="cc-stat-lbl">Deficiency</span>
                    </div>
                    <?php endif; ?>
                    <div class="cc-stat cc-stat-cleared">
                        <span class="cc-stat-num"><?= $cleared ?></span>
                        <span class="cc-stat-lbl">Cleared</span>
                    </div>
                    <div class="cc-stat cc-stat-pending">
                        <span class="cc-stat-num"><?= $pending ?></span>
                        <span class="cc-stat-lbl">Pending</span>
                    </div>
                </div>
                <div class="cc-footer">
                    <span class="cc-open">View Students →</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<?php else: ?>
<!-- =====================================================
     PHASE 2 — Student Status Table
     ===================================================== -->
<?php
$c         = $selectedClearance;
$students  = $c['students'];
$totalHere = count($students);
$cFlagged  = array_sum(array_map(fn($s) => $s['display_status'] === 'flagged' ? 1 : 0, $students));
$cCleared  = array_sum(array_map(fn($s) => $s['display_status'] === 'cleared' ? 1 : 0, $students));
$cPending  = $totalHere - $cFlagged - $cCleared;
?>

<div class="page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
    <div>
        <a href="<?= BASE_URL ?>adviser/clearances" class="back-link">← My Clearances</a>
        <h2><?= htmlspecialchars($c['clearance_name']) ?></h2>
        <?php if (!empty($c['school_year'])): ?>
            <p class="text-muted" style="margin-top:.25rem"><?= htmlspecialchars($c['school_year']) ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Summary badges -->
<div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; margin-bottom:1.25rem;">
    <span class="badge badge-info"><?= $totalHere ?> students</span>
    <?php if ($cFlagged > 0): ?>
        <span class="badge badge-danger"><?= $cFlagged ?> with deficiency</span>
    <?php endif; ?>
    <span class="badge badge-success"><?= $cCleared ?> cleared</span>
    <span class="badge badge-warning"><?= $cPending ?> pending</span>
</div>

<!-- ===== Filter Bar ===== -->
<form method="GET" action="<?= BASE_URL ?>adviser/clearances" class="filter-bar">
    <input type="hidden" name="cid" value="<?= $selectedCid ?>">
    <div class="filter-group">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               placeholder="Search by name or ID…" class="form-control search-input" id="adv-search">
    </div>
    <div class="filter-group">
        <select name="status" class="form-control" id="adv-status-filter">
            <option value="all"     <?= $filterStatus === 'all'     ? 'selected' : '' ?>>All Statuses</option>
            <option value="flagged" <?= $filterStatus === 'flagged' ? 'selected' : '' ?>>Has Deficiency</option>
            <option value="cleared" <?= $filterStatus === 'cleared' ? 'selected' : '' ?>>Fully Cleared</option>
            <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>In Progress</option>
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
    <a href="<?= BASE_URL ?>adviser/clearances?cid=<?= $selectedCid ?>" class="btn btn-secondary btn-sm">Reset</a>
</form>

<?php if (empty($students)): ?>
    <p class="text-muted text-center" style="padding:2rem;">No students match the current filters.</p>
<?php else: ?>
    <div class="table-container">
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
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php $rowNum = 1; foreach ($students as $s): ?>
                    <?php $rowId = 'detail-' . $selectedCid . '-' . $s['id']; ?>
                    <tr class="<?= $s['display_status'] === 'flagged' ? 'row-flagged' : ($s['display_status'] === 'cleared' ? 'row-cleared' : '') ?>">
                        <td class="text-muted"><?= $rowNum++ ?></td>
                        <td><?= htmlspecialchars($s['student_number']) ?></td>
                        <td><strong><?= htmlspecialchars($s['full_name']) ?></strong></td>
                        <td><?= htmlspecialchars($s['course']) ?></td>
                        <td>Year <?= $s['year_level'] ?> – <?= htmlspecialchars($s['section']) ?></td>
                        <td><a href="mailto:<?= htmlspecialchars($s['email']) ?>" class="email-link"><?= htmlspecialchars($s['email']) ?></a></td>
                        <td>
                            <?php if ($s['display_status'] === 'flagged'): ?>
                                <span class="standing-badge standing-flagged">NOT CLEARED</span>
                            <?php elseif ($s['display_status'] === 'cleared'): ?>
                                <span class="standing-badge standing-cleared">CLEARED</span>
                            <?php else: ?>
                                <span class="standing-badge standing-pending">PENDING</span>
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
                        <td>
                            <button type="button" class="btn btn-secondary btn-sm"
                                    onclick="toggleDetail('<?= $rowId ?>')">
                                View ▾
                            </button>
                        </td>
                    </tr>
                    <!-- Expandable per-signatory detail row -->
                    <tr id="<?= $rowId ?>" class="detail-row" style="display:none;">
                        <td colspan="9">
                            <div class="signatory-detail-grid">
                                <?php foreach ($s['signatory_detail'] as $sg): ?>
                                    <div class="signatory-detail-card <?= $sg['status'] === 'flagged' ? 'card-flagged' : ($sg['status'] === 'cleared' ? 'card-cleared' : '') ?>">
                                        <div class="sd-header">
                                            <span class="sd-office"><?= htmlspecialchars($sg['office']) ?></span>
                                            <span class="sd-name text-muted"><?= htmlspecialchars($sg['signatory_name']) ?></span>
                                        </div>
                                        <div class="sd-status">
                                            <?php if ($sg['status'] === 'flagged'): ?>
                                                <span class="badge badge-danger">Flagged</span>
                                                <?php if (!empty($sg['flag_note'])): ?>
                                                    <p class="sd-note"><?= nl2br(htmlspecialchars($sg['flag_note'])) ?></p>
                                                <?php endif; ?>
                                            <?php elseif ($sg['status'] === 'cleared'): ?>
                                                <span class="badge badge-success">Cleared</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Pending</span>
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
    </div>
<?php endif; ?>
<?php endif; ?>



<script>
function toggleDetail(rowId) {
    const row = document.getElementById(rowId);
    if (!row) return;
    const btn = row.previousElementSibling?.querySelector('button');
    if (row.style.display === 'none') {
        row.style.display = '';
        if (btn) btn.textContent = 'Hide ▴';
    } else {
        row.style.display = 'none';
        if (btn) btn.textContent = 'View ▾';
    }
}
</script>
