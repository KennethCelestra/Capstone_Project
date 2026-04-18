<!-- ===== Signatory: Manage Student Clearances ===== -->
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2>Manage Clearances</h2>
        <p class="text-muted">Flag students for deficiencies or clear them once resolved.</p>
    </div>
    <?php
    $totalFlagged = 0;
    foreach ($clearances as $c) {
        foreach ($c['students'] as $s) {
            if ($s['status'] === 'flagged') $totalFlagged++;
        }
    }
    ?>
    <?php if ($totalFlagged > 0): ?>
        <a href="<?= BASE_URL ?>signatory/confirm" class="btn btn-danger">
            🚩 Confirm &amp; Send Emails <span class="badge-count"><?= $totalFlagged ?></span>
        </a>
    <?php endif; ?>
</div>

<!-- ===== Filter Bar ===== -->
<form method="GET" action="<?= BASE_URL ?>signatory/clearances" class="filter-bar">
    <div class="filter-group">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               placeholder="Search by name or ID…" class="form-control search-input" id="sig-search">
    </div>
    <div class="filter-group">
        <select name="status" class="form-control" id="sig-status-filter">
            <option value="all"    <?= $filterStatus === 'all'     ? 'selected' : '' ?>>All Statuses</option>
            <option value="flagged" <?= $filterStatus === 'flagged' ? 'selected' : '' ?>>🚩 Flagged</option>
            <option value="cleared" <?= $filterStatus === 'cleared' ? 'selected' : '' ?>>✅ Cleared</option>
            <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
        </select>
    </div>
    <?php if (!empty($courses)): ?>
    <div class="filter-group">
        <select name="course" class="form-control" id="sig-course-filter">
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
        <select name="year" class="form-control" id="sig-year-filter">
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
    <a href="<?= BASE_URL ?>signatory/clearances" class="btn btn-secondary btn-sm">Reset</a>
</form>

<?php if (empty($clearances)): ?>
    <div class="empty-state">
        <div class="empty-icon">✍️</div>
        <h3>No clearances assigned</h3>
        <p>You haven't been assigned to any clearance yet. Contact the administrator.</p>
    </div>
<?php else: ?>
    <?php foreach ($clearances as $c): ?>
        <?php
        $cFlagged  = array_sum(array_map(fn($s) => $s['status'] === 'flagged' ? 1 : 0, $c['students']));
        $cCleared  = array_sum(array_map(fn($s) => $s['status'] === 'cleared' ? 1 : 0, $c['students']));
        $cPending  = count($c['students']) - $cFlagged - $cCleared;
        ?>
        <div class="table-container" style="margin-bottom:2rem;">

            <!-- Clearance header -->
            <div class="clearance-header-bar">
                <div>
                    <strong><?= htmlspecialchars($c['clearance_name']) ?></strong>
                    <?php if (!empty($c['school_year'])): ?>
                        <span class="text-muted" style="font-size:.85rem; margin-left:.75rem;">
                            <?= htmlspecialchars($c['school_year']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
                    <?php if ($cFlagged > 0): ?>
                        <span class="badge badge-danger">🚩 <?= $cFlagged ?> flagged</span>
                    <?php endif; ?>
                    <span class="badge badge-success">✅ <?= $cCleared ?> cleared</span>
                    <span class="badge badge-warning">⏳ <?= $cPending ?> pending</span>
                </div>
            </div>

            <?php if (empty($c['students'])): ?>
                <p class="text-muted text-center" style="padding:1.5rem;">
                    No students match the current filters.
                </p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Course</th>
                            <th>Year / Section</th>
                            <th>Status</th>
                            <th>Flag Note</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($c['students'] as $s): ?>
                            <tr class="<?= $s['status'] === 'flagged' ? 'row-flagged' : '' ?>">
                                <td><?= htmlspecialchars($s['student_number']) ?></td>
                                <td><strong><?= htmlspecialchars($s['full_name']) ?></strong></td>
                                <td><?= htmlspecialchars($s['course']) ?></td>
                                <td>Year <?= $s['year_level'] ?> – <?= htmlspecialchars($s['section']) ?></td>
                                <td>
                                    <?php if ($s['status'] === 'flagged'): ?>
                                        <span class="badge badge-danger">🚩 Flagged</span>
                                    <?php elseif ($s['status'] === 'cleared'): ?>
                                        <span class="badge badge-success">✅ Cleared</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">⏳ Pending</span>
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
                                <td>
                                    <?php if ($s['status'] === 'flagged'): ?>
                                        <!-- Unflag button -->
                                        <form action="<?= BASE_URL ?>signatory/students/clear" method="POST"
                                              onsubmit="return confirm('Clear deficiency for <?= htmlspecialchars(addslashes($s['full_name'])) ?>?')">
                                            <input type="hidden" name="clearance_id" value="<?= $c['clearance_id'] ?>">
                                            <input type="hidden" name="student_id"   value="<?= $s['id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm">✅ Unflag</button>
                                        </form>
                                    <?php else: ?>
                                        <!-- Flag button — opens modal -->
                                        <button type="button" class="btn btn-danger btn-sm"
                                                onclick="openFlagModal(<?= $c['clearance_id'] ?>, <?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['full_name'])) ?>')">
                                            🚩 Flag
                                        </button>
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

<!-- ===== Flag Modal ===== -->
<div id="flag-modal" class="modal-overlay" style="display:none;" onclick="closeFlagModalOnOverlay(event)">
    <div class="modal-box">
        <div class="modal-header">
            <h3>🚩 Flag Student for Deficiency</h3>
            <button type="button" class="modal-close" onclick="closeFlagModal()">✕</button>
        </div>
        <form action="<?= BASE_URL ?>signatory/students/flag" method="POST" id="flag-form">
            <input type="hidden" name="clearance_id" id="modal-clearance-id">
            <input type="hidden" name="student_id"   id="modal-student-id">
            <div class="modal-body">
                <p>You are flagging: <strong id="modal-student-name"></strong></p>
                <label for="flag-note-input" class="form-label">Deficiency Reason <span style="color:#ef4444">*</span></label>
                <textarea id="flag-note-input" name="flag_note" class="form-control" rows="4"
                          placeholder="Describe the deficiency or requirement the student needs to fulfill…"
                          required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeFlagModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">🚩 Confirm Flag</button>
            </div>
        </form>
    </div>
</div>

<style>
/* ---- Flag modal ---- */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.55);
    display: flex; align-items: center; justify-content: center;
    z-index: 9999;
    animation: fadeIn .15s ease;
}
@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
.modal-box {
    background: var(--card-bg, #1e293b);
    border: 1px solid var(--border, #334155);
    border-radius: 12px;
    width: min(500px, 95vw);
    box-shadow: 0 25px 50px rgba(0,0,0,.4);
    animation: slideUp .2s ease;
}
@keyframes slideUp { from { transform:translateY(20px); opacity:0; } to { transform:translateY(0); opacity:1; } }
.modal-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border, #334155);
}
.modal-header h3 { margin:0; font-size:1.1rem; }
.modal-close {
    background: none; border: none; color: var(--text-muted, #94a3b8);
    font-size: 1.1rem; cursor: pointer; padding: .25rem .5rem;
    border-radius: 4px; transition: background .15s;
}
.modal-close:hover { background: var(--border, #334155); }
.modal-body { padding: 1.5rem; }
.modal-footer {
    display: flex; gap:.75rem; justify-content: flex-end;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border, #334155);
}

/* ---- Filter bar ---- */
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

/* ---- Clearance header bar ---- */
.clearance-header-bar {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border, #334155);
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: .5rem;
}

/* ---- Row flagged highlight ---- */
.row-flagged { background: rgba(239,68,68,.06) !important; }

/* ---- Flag note text ---- */
.flag-note { font-size: .85rem; color: var(--text-muted, #94a3b8); font-style: italic; cursor: help; }

/* ---- Badge count bubble ---- */
.badge-count {
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,.25);
    border-radius: 999px;
    min-width: 20px; height: 20px;
    font-size: .75rem; font-weight: 700;
    padding: 0 6px; margin-left: 6px;
}

/* ---- btn-danger ---- */
.btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    border: none;
    padding: .5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .2s, transform .15s;
    font-size: .875rem;
    text-decoration: none;
    display: inline-flex; align-items: center; gap: .35rem;
}
.btn-danger:hover { opacity: .9; transform: translateY(-1px); }
</style>

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