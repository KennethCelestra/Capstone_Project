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
<div class="page-header mb-4">
    <div>
        <h2>My Clearances</h2>
        <p class="text-muted">Select a clearance to manage student statuses.</p>
    </div>
</div>

<?php if (empty($clearances)): ?>
    <div class="empty-state text-center p-5 gold-card" style="background:#fff; border-radius:8px;">
        <i class="bi bi-folder-x display-1 text-muted mb-3 opacity-50"></i>
        <h3>No clearances assigned</h3>
        <p class="text-muted">You haven't been assigned to any clearance yet. Contact the administrator.</p>
    </div>
<?php else: ?>
    <div class="gold-card" style="background:#fff; border-radius:8px; overflow:hidden;">
        <div class="table-responsive">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr style="background: var(--surface2);">
                        <th class="py-3 px-4">Clearance Name</th>
                        <th class="py-3 px-4">School Year</th>
                        <th class="py-3 px-4 text-center">Students</th>
                        <th class="py-3 px-4 text-center">Pending</th>
                        <th class="py-3 px-4 text-center">Flagged</th>
                        <th class="py-3 px-4 text-center">Cleared</th>
                        <th class="py-3 px-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clearances as $c): ?>
                        <?php
                        $total   = (int) $c['total_students'];
                        $flagged = (int) $c['flagged_count'];
                        $cleared = (int) $c['cleared_count'];
                        $pending = $total - $flagged - $cleared;
                        ?>
                        <tr class="border-bottom">
                            <td class="py-3 px-4">
                                <strong><?= htmlspecialchars($c['clearance_name']) ?></strong>
                            </td>
                            <td class="py-3 px-4"><?= htmlspecialchars($c['school_year'] ?? '') ?></td>
                            <td class="py-3 px-4 text-center"><span class="badge badge-info"><?= $total ?></span></td>
                            <td class="py-3 px-4 text-center">
                                <?php if ($pending > 0): ?>
                                    <span class="badge badge-warning text-dark"><?= $pending ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if ($flagged > 0): ?>
                                    <span class="badge badge-danger"><?= $flagged ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if ($cleared > 0): ?>
                                    <span class="badge badge-success"><?= $cleared ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <a href="<?= BASE_URL ?>signatory/clearances?cid=<?= $c['clearance_id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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

<div class="page-header mb-4" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
    <div>
        <a href="<?= BASE_URL ?>signatory/clearances" class="back-link text-decoration-none d-inline-block mb-2"><i class="bi bi-arrow-left"></i> My Clearances</a>
        <h2><?= htmlspecialchars($c['clearance_name']) ?></h2>
        <?php if (!empty($c['school_year'])): ?>
            <p class="text-muted" style="margin-top:.25rem"><?= htmlspecialchars($c['school_year']) ?></p>
        <?php endif; ?>
    </div>
    <div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
        <button type="button" class="btn btn-primary" onclick="openConfirmAllModal()">
            Confirm All
        </button>
    </div>
</div>

<!-- Summary badges -->
<div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; margin-bottom:1.25rem;">
    <?php if ($cFlagged > 0): ?>
        <span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> <?= $cFlagged ?> flagged</span>
    <?php endif; ?>
    <span class="badge bg-success"><i class="bi bi-check-circle"></i> <?= $cCleared ?> cleared</span>
    <span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> <?= $cPending ?> pending</span>
</div>

<!-- ===== Filter Bar ===== -->
<div class="gold-card p-3 mb-4" style="background:#fff; border-radius:8px;">
    <form method="GET" action="<?= BASE_URL ?>signatory/clearances" class="filter-bar m-0 d-flex gap-3 align-items-center flex-wrap">
        <input type="hidden" name="cid" value="<?= $selectedCid ?>">
        <div class="filter-group flex-grow-1" style="min-width: 200px;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search by name or ID…" class="form-control" id="sig-search">
        </div>
        <div class="filter-group">
            <select name="status" class="form-select" id="sig-status-filter" onchange="this.form.submit()">
                <option value="all"    <?= $filterStatus === 'all'     ? 'selected' : '' ?>>All Statuses</option>
                <option value="flagged" <?= $filterStatus === 'flagged' ? 'selected' : '' ?>>Flagged</option>
                <option value="cleared" <?= $filterStatus === 'cleared' ? 'selected' : '' ?>>Cleared</option>
                <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
            </select>
        </div>
        <?php if (!empty($colleges)): ?>
        <div class="filter-group">
            <input type="text" list="college-list" name="college" class="form-control" placeholder="All Colleges" value="<?= htmlspecialchars($filterCollege) ?>" onchange="this.form.submit()" style="max-width: 140px;">
            <datalist id="college-list">
                <?php foreach ($colleges as $col): ?>
                    <option value="<?= htmlspecialchars($col) ?>">
                <?php endforeach; ?>
            </datalist>
        </div>
        <?php endif; ?>
        <?php if (!empty($courses)): ?>
        <div class="filter-group">
            <input type="text" list="course-list" name="course" class="form-control" placeholder="All Courses" value="<?= htmlspecialchars($filterCourse) ?>" onchange="this.form.submit()" style="max-width: 140px;">
            <datalist id="course-list">
                <?php foreach ($courses as $course): ?>
                    <option value="<?= htmlspecialchars($course) ?>">
                <?php endforeach; ?>
            </datalist>
        </div>
        <?php endif; ?>
        <?php if (!empty($yearLevels)): ?>
        <div class="filter-group">
            <input type="text" list="year-list" name="year" class="form-control" placeholder="All Years" value="<?= htmlspecialchars($filterYear) ?>" onchange="this.form.submit()" style="max-width: 110px;">
            <datalist id="year-list">
                <?php foreach ($yearLevels as $yr): ?>
                    <option value="<?= $yr ?>">
                <?php endforeach; ?>
            </datalist>
        </div>
        <?php endif; ?>
        <button type="submit" style="display:none"></button>
    </form>
</div>

<?php if (empty($students)): ?>
    <div class="gold-card text-center p-5" style="background:#fff; border-radius:8px;">
        <i class="bi bi-search display-1 text-muted opacity-50 mb-3 d-block"></i>
        <p class="text-muted fs-5">No students match the current filters.</p>
    </div>
<?php else: ?>
<div class="gold-card" style="background:#fff; border-radius:8px; overflow:hidden;">
    <div class="table-responsive">
        <table class="data-table m-0" style="width: 100%;">
            <thead>
                <tr style="background: var(--surface2);">
                    <th class="py-3 px-4">Student ID</th>
                    <th class="py-3 px-4">Name</th>
                    <th class="py-3 px-4">College</th>
                    <th class="py-3 px-4">Course</th>
                    <th class="py-3 px-4">Year/Sec</th>
                    <th class="py-3 px-4">Standing</th>
                    <th class="py-3 px-4" style="width:25%;">Deficiency Note</th>
                    <th class="py-3 px-4 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $s): ?>
                    <?php $rowClass = $s['status'] === 'flagged' ? 'table-danger' : ($s['status'] === 'cleared' ? 'table-success' : ''); ?>
                    <tr class="border-bottom <?= $rowClass ?>">
                        <td class="py-3 px-4"><strong><?= htmlspecialchars($s['student_number']) ?></strong></td>
                        <td class="py-3 px-4"><?= htmlspecialchars($s['last_name']) ?>, <?= htmlspecialchars($s['first_name']) ?></td>
                        <td class="py-3 px-4"><?= htmlspecialchars($s['college']) ?></td>
                        <td class="py-3 px-4"><?= htmlspecialchars($s['course']) ?></td>
                        <td class="py-3 px-4"><?= $s['year_level'] ?>–<?= htmlspecialchars($s['section']) ?></td>
                        <td class="py-3 px-4">
                            <?php if ($s['status'] === 'flagged'): ?>
                                <span class="badge bg-danger">Flagged</span>
                            <?php elseif ($s['status'] === 'cleared'): ?>
                                <span class="badge bg-success">Cleared</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php if ($s['status'] === 'flagged' && !empty($s['flag_note'])): ?>
                                <span class="text-danger small" title="<?= htmlspecialchars($s['flag_note']) ?>">
                                    <?= htmlspecialchars($s['flag_note']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 text-end">
                            <?php if ($s['status'] === 'flagged'): ?>
                                <button type="button" class="btn btn-success btn-sm" onclick="openUnflagModal(<?= $selectedCid ?>, <?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['first_name'] . ' ' . $s['last_name'])) ?>')">
                                    <i class="bi bi-check2-circle"></i> Unflag
                                </button>
                            <?php elseif ($s['status'] === 'cleared'): ?>
                                <span class="text-success small fw-bold">Cleared</span>
                            <?php else: ?>
                                <button type="button" class="btn btn-danger btn-sm"
                                        onclick="openFlagModal(<?= $selectedCid ?>, <?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['first_name'] . ' ' . $s['last_name'])) ?>')">
                                    <i class="bi bi-flag"></i> Flag
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- ===== Flag Modal ===== -->
<div id="flag-modal" class="modal" style="display:none;" onclick="closeFlagModalOnOverlay(event)">
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

<!-- ===== Unflag Modal ===== -->
<div id="unflag-modal" class="modal" style="display:none;" onclick="closeUnflagModalOnOverlay(event)">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Clear Deficiency</h3>
            <button type="button" class="modal-close" onclick="closeUnflagModal()">✕</button>
        </div>
        <form action="<?= BASE_URL ?>signatory/students/clear" method="POST" id="unflag-form">
            <input type="hidden" name="clearance_id" id="unflag-modal-clearance-id">
            <input type="hidden" name="student_id"   id="unflag-modal-student-id">
            <div class="modal-body">
                <p>Are you sure you want to clear the deficiency for <strong id="unflag-modal-student-name"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeUnflagModal()">Cancel</button>
                <button type="submit" class="btn btn-success">Confirm</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== Confirm All Modal ===== -->
<div id="confirm-all-modal" class="modal" style="display:none;" onclick="closeConfirmAllModalOnOverlay(event)">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Confirm All</h3>
            <button type="button" class="modal-close" onclick="closeConfirmAllModal()">✕</button>
        </div>
        <form action="<?= BASE_URL ?>signatory/confirm-all" method="POST" id="confirm-all-form-modal">
            <input type="hidden" name="clearance_id" value="<?= $selectedCid ?>">
            <div class="modal-body">
                <p>This will clear all pending students.</p>
                <p>Are you sure you want to continue?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmAllModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="confirm-all-btn-modal" onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm\'></span> Processing...'; this.form.submit();">Confirm All</button>
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

function openUnflagModal(clearanceId, studentId, studentName) {
    document.getElementById('unflag-modal-clearance-id').value = clearanceId;
    document.getElementById('unflag-modal-student-id').value   = studentId;
    document.getElementById('unflag-modal-student-name').textContent = studentName;
    document.getElementById('unflag-modal').style.display = 'flex';
}
function closeUnflagModal() {
    document.getElementById('unflag-modal').style.display = 'none';
}
function closeUnflagModalOnOverlay(e) {
    if (e.target === document.getElementById('unflag-modal')) closeUnflagModal();
}

function openConfirmAllModal() {
    document.getElementById('confirm-all-modal').style.display = 'flex';
}
function closeConfirmAllModal() {
    document.getElementById('confirm-all-modal').style.display = 'none';
}
function closeConfirmAllModalOnOverlay(e) {
    if (e.target === document.getElementById('confirm-all-modal')) closeConfirmAllModal();
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFlagModal();
        closeUnflagModal();
        closeConfirmAllModal();
    }
});
</script>