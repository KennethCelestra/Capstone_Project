<?php
/**
 * Signatory: My Clearances — two-phase view
 *   $phase === 'select'  → clearance selection cards
 *   $phase === 'detail'  → student list for selected clearance
 */
?>

<?php if ($phase === 'select'): ?>
<!-- =====================================================
     PHASE 1 — Clearance Selection Cards
     ===================================================== -->
<div class="page-header">
    <div>
        <h2>My Clearances</h2>
        <p class="text-muted">Select a clearance to manage student statuses.</p>
    </div>
</div>

<?php if (empty($clearances)): ?>
    <div class="empty-state">
        <h3>No clearances assigned</h3>
        <p>You haven't been assigned to any clearance yet. Contact the administrator.</p>
    </div>
<?php else: ?>
    <div class="clearance-cards-grid">
        <?php foreach ($clearances as $c): ?>
            <?php
            $total   = (int) $c['total_students'];
            $flagged = (int) $c['flagged_count'];
            $cleared = (int) $c['cleared_count'];
            $pending = $total - $flagged - $cleared;
            ?>
            <a href="<?= BASE_URL ?>signatory/clearances?cid=<?= $c['clearance_id'] ?>"
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
                        <span class="cc-stat-lbl">Flagged</span>
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
                    <span class="cc-open">Open →</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<?php else: ?>
<!-- =====================================================
     PHASE 2 — Student List for Selected Clearance
     ===================================================== -->
<?php
$c        = $selectedClearance;
$cFlagged = array_sum(array_map(fn($s) => $s['status'] === 'flagged' ? 1 : 0, $students));
$cCleared = array_sum(array_map(fn($s) => $s['status'] === 'cleared' ? 1 : 0, $students));
$cPending = count($students) - $cFlagged - $cCleared;
// Count unfiltered flagged (for confirm button) — re-use $c summary
$totalFlagged = (int) ($c['flagged_count'] ?? $cFlagged);
?>

<div class="page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
    <div>
        <a href="<?= BASE_URL ?>signatory/clearances" class="back-link">← My Clearances</a>
        <h2><?= htmlspecialchars($c['clearance_name']) ?></h2>
        <?php if (!empty($c['school_year'])): ?>
            <p class="text-muted" style="margin-top:.25rem"><?= htmlspecialchars($c['school_year']) ?></p>
        <?php endif; ?>
    </div>
    <div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
        <!-- Confirm All: clears all pending + sends deficiency emails to flagged -->
        <form action="<?= BASE_URL ?>signatory/confirm-all" method="POST" id="confirm-all-form"
              onsubmit="return confirm('This will clear all pending students and send deficiency emails to <?= $totalFlagged ?> flagged student(s). Continue?')">
            <input type="hidden" name="clearance_id" value="<?= $selectedCid ?>">
            <button type="submit" class="btn btn-primary" id="confirm-all-btn">
                Confirm All
            </button>
        </form>
        <script>
        document.getElementById('confirm-all-form').addEventListener('submit', function() {
            const btn = document.getElementById('confirm-all-btn');
            btn.disabled = true;
            btn.innerHTML = 'Processing…';
        });
        </script>
    </div>
</div>

<!-- Summary badges -->
<div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; margin-bottom:1.25rem;">
    <?php if ($cFlagged > 0): ?>
        <span class="badge badge-danger"><?= $cFlagged ?> flagged</span>
    <?php endif; ?>
    <span class="badge badge-success"><?= $cCleared ?> cleared</span>
    <span class="badge badge-warning"><?= $cPending ?> pending</span>
</div>

<!-- ===== Filter Bar ===== -->
<form method="GET" action="<?= BASE_URL ?>signatory/clearances" class="filter-bar">
    <input type="hidden" name="cid" value="<?= $selectedCid ?>">
    <div class="filter-group">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               placeholder="Search by name or ID…" class="form-control search-input" id="sig-search">
    </div>
    <div class="filter-group">
        <select name="status" class="form-control" id="sig-status-filter" onchange="this.form.submit()">
            <option value="all"    <?= $filterStatus === 'all'     ? 'selected' : '' ?>>All Statuses</option>
            <option value="flagged" <?= $filterStatus === 'flagged' ? 'selected' : '' ?>>Flagged</option>
            <option value="cleared" <?= $filterStatus === 'cleared' ? 'selected' : '' ?>>Cleared</option>
            <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
        </select>
    </div>
    <?php if (!empty($courses)): ?>
    <div class="filter-group">
        <select name="course" class="form-control" id="sig-course-filter" onchange="this.form.submit()">
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
        <select name="year" class="form-control" id="sig-year-filter" onchange="this.form.submit()">
            <option value="">All Year Levels</option>
            <?php foreach ($yearLevels as $yr): ?>
                <option value="<?= $yr ?>" <?= $filterYear === (string)$yr ? 'selected' : '' ?>>
                    Year <?= $yr ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <button type="submit" style="display:none"></button>
</form>

<?php if (empty($students)): ?>
    <p class="text-muted text-center" style="padding:2rem;">No students match the current filters.</p>
<?php else: ?>
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 100px;">Student ID</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>College</th>
                <th>Course</th>
                <th>Year / Section</th>
                <th>Standing</th>
                <th>Deficiency Note</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $s): ?>
                <?php $rowClass = $s['status'] === 'flagged' ? 'row-flagged' : ($s['status'] === 'cleared' ? 'row-cleared' : ''); ?>
                <tr class="<?= $rowClass ?>">
                    <td><?= htmlspecialchars($s['student_number']) ?></td>
                    <td><?= htmlspecialchars($s['last_name']) ?></td>
                    <td><?= htmlspecialchars($s['first_name']) ?></td>
                    <td><?= htmlspecialchars($s['college']) ?></td>
                    <td><?= htmlspecialchars($s['course']) ?></td>
                    <td><?= $s['year_level'] ?> – <?= htmlspecialchars($s['section']) ?></td>
                    <td>
                        <?php if ($s['status'] === 'flagged'): ?>
                            <span class="badge badge-danger">Flagged</span>
                        <?php elseif ($s['status'] === 'cleared'): ?>
                            <span class="badge badge-success">Cleared</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($s['status'] === 'flagged' && !empty($s['flag_note'])): ?>
                            <span class="flag-note" title="<?= htmlspecialchars($s['flag_note']) ?>">
                                <?= htmlspecialchars(mb_substr($s['flag_note'], 0, 60)) ?>
                                <?= mb_strlen($s['flag_note']) > 60 ? '…' : '' ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-cell">
                        <?php if ($s['status'] === 'flagged'): ?>
                            <!-- Unflag — resolves deficiency, moves to cleared -->
                            <form action="<?= BASE_URL ?>signatory/students/clear" method="POST"
                                  onsubmit="return confirm('Clear deficiency for <?= htmlspecialchars(addslashes($s['first_name'] . ' ' . $s['last_name'])) ?>?')">
                                <input type="hidden" name="clearance_id" value="<?= $selectedCid ?>">
                                <input type="hidden" name="student_id"   value="<?= $s['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">Unflag</button>
                            </form>
                        <?php elseif ($s['status'] === 'cleared'): ?>
                            <span class="text-muted" style="font-size:.8rem">Done</span>
                        <?php else: ?>
                            <!-- Pending: can only Flag (bulk clear via Confirm All) -->
                            <button type="button" class="btn btn-danger btn-sm"
                                    onclick="openFlagModal(<?= $selectedCid ?>, <?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['first_name'] . ' ' . $s['last_name'])) ?>')">
                                Flag
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- ===== Flag Modal ===== -->
<div id="flag-modal" class="modal-overlay" style="display:none;" onclick="closeFlagModalOnOverlay(event)">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Flag Student for Deficiency</h3>
            <button type="button" class="modal-close" onclick="closeFlagModal()">✕</button>
        </div>
        <form action="<?= BASE_URL ?>signatory/students/flag" method="POST" id="flag-form">
            <input type="hidden" name="clearance_id" id="modal-clearance-id">
            <input type="hidden" name="student_id"   id="modal-student-id">
            <div class="modal-body">
                <p>You are flagging: <strong id="modal-student-name"></strong></p>
                <label for="flag-note-input" class="form-label">Deficiency Reason <span style="color:var(--danger, #b91c1c)">*</span></label>
                <textarea id="flag-note-input" name="flag_note" class="form-control" rows="4"
                          placeholder="Describe the deficiency or requirement the student needs to fulfill…"
                          required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeFlagModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Flag</button>
            </div>
        </form>
    </div>
</div>



<script>
function openFlagModal(clearanceId, studentId, studentName) {
    document.getElementById('modal-clearance-id').value = clearanceId;
    document.getElementById('modal-student-id').value   = studentId;
    document.getElementById('modal-student-name').textContent = studentName;
    document.getElementById('flag-note-input').value    = '';
    document.getElementById('flag-modal').style.display = 'flex';
    setTimeout(() => document.getElementById('flag-note-input').focus(), 100);
}
function closeFlagModal() {
    document.getElementById('flag-modal').style.display = 'none';
}
function closeFlagModalOnOverlay(e) {
    if (e.target === document.getElementById('flag-modal')) closeFlagModal();
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeFlagModal();
});
</script>