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
        <div class="empty-icon">✍️</div>
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
                    <div class="cc-icon">📋</div>
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
                        <span class="cc-stat-num">🚩 <?= $flagged ?></span>
                        <span class="cc-stat-lbl">Flagged</span>
                    </div>
                    <?php endif; ?>
                    <div class="cc-stat cc-stat-cleared">
                        <span class="cc-stat-num">✅ <?= $cleared ?></span>
                        <span class="cc-stat-lbl">Cleared</span>
                    </div>
                    <div class="cc-stat cc-stat-pending">
                        <span class="cc-stat-num">⏳ <?= $pending ?></span>
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
        <?php if ($totalFlagged > 0): ?>
            <form action="<?= BASE_URL ?>signatory/confirm/submit" method="POST" id="direct-email-form"
                  onsubmit="return confirm('Send deficiency emails to all <?= $totalFlagged ?> flagged student(s)?')">
                <input type="hidden" name="clearance_id" value="<?= $selectedCid ?>">
                <button type="submit" class="btn btn-danger" id="send-emails-btn">
                    🚩 Send Deficiency Emails <span class="badge-count"><?= $totalFlagged ?></span>
                </button>
            </form>
            <script>
            document.getElementById('direct-email-form').addEventListener('submit', function() {
                const btn = document.getElementById('send-emails-btn');
                btn.disabled = true;
                btn.innerHTML = '📧 Sending…';
            });
            </script>
        <?php endif; ?>
        <!-- Clear All pending students (skips flagged) -->
        <form action="<?= BASE_URL ?>signatory/students/clear-all" method="POST"
              onsubmit="return confirm('Clear all pending students for this clearance? Flagged students will not be affected.')">
            <input type="hidden" name="clearance_id" value="<?= $selectedCid ?>">
            <button type="submit" class="btn btn-success" id="clear-all-btn">
                ✅ Clear All Pending
            </button>
        </form>
    </div>
</div>

<!-- Summary badges -->
<div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; margin-bottom:1.25rem;">
    <?php if ($cFlagged > 0): ?>
        <span class="badge badge-danger">🚩 <?= $cFlagged ?> flagged</span>
    <?php endif; ?>
    <span class="badge badge-success">✅ <?= $cCleared ?> cleared</span>
    <span class="badge badge-warning">⏳ <?= $cPending ?> pending</span>
</div>

<!-- ===== Filter Bar ===== -->
<form method="GET" action="<?= BASE_URL ?>signatory/clearances" class="filter-bar">
    <input type="hidden" name="cid" value="<?= $selectedCid ?>">
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
    <a href="<?= BASE_URL ?>signatory/clearances?cid=<?= $selectedCid ?>" class="btn btn-secondary btn-sm">Reset</a>
</form>

<?php if (empty($students)): ?>
    <p class="text-muted text-center" style="padding:2rem;">No students match the current filters.</p>
<?php else: ?>
<div class="table-container">
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
            <?php foreach ($students as $s): ?>
                <tr class="<?= $s['status'] === 'flagged' ? 'row-flagged' : ($s['status'] === 'cleared' ? 'row-cleared' : '') ?>">
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
                    <td class="actions-cell">
                        <?php if ($s['status'] === 'flagged'): ?>
                            <!-- Unflag — resolves deficiency, moves to cleared -->
                            <form action="<?= BASE_URL ?>signatory/students/clear" method="POST"
                                  onsubmit="return confirm('Clear deficiency for <?= htmlspecialchars(addslashes($s['full_name'])) ?>?')">
                                <input type="hidden" name="clearance_id" value="<?= $selectedCid ?>">
                                <input type="hidden" name="student_id"   value="<?= $s['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">✅ Unflag</button>
                            </form>
                        <?php elseif ($s['status'] === 'cleared'): ?>
                            <span class="text-muted" style="font-size:.8rem">Done</span>
                        <?php else: ?>
                            <!-- Pending: can Flag or Clear -->
                            <div style="display:flex; gap:.4rem; flex-wrap:wrap;">
                                <form action="<?= BASE_URL ?>signatory/students/clear" method="POST"
                                      onsubmit="return confirm('Clear <?= htmlspecialchars(addslashes($s['full_name'])) ?>?')">
                                    <input type="hidden" name="clearance_id" value="<?= $selectedCid ?>">
                                    <input type="hidden" name="student_id"   value="<?= $s['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">✅ Clear</button>
                                </form>
                                <button type="button" class="btn btn-danger btn-sm"
                                        onclick="openFlagModal(<?= $selectedCid ?>, <?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['full_name'])) ?>')">
                                    🚩 Flag
                                </button>
                            </div>
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
/* ===== Clearance Selection Cards ===== */
.clearance-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.25rem;
    margin-top: .5rem;
}
.clearance-card {
    background: var(--card-bg, #1e293b);
    border: 1px solid var(--border, #334155);
    border-radius: 14px;
    padding: 1.5rem;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    transition: transform .2s, box-shadow .2s, border-color .2s;
    cursor: pointer;
}
.clearance-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 32px rgba(0,0,0,.35);
    border-color: var(--primary, #6366f1);
}
.clearance-card.card-has-flags {
    border-color: rgba(239,68,68,.4);
}
.clearance-card.card-has-flags:hover {
    border-color: rgba(239,68,68,.7);
}
.cc-header { display: flex; align-items: flex-start; gap: 1rem; }
.cc-icon { font-size: 1.75rem; line-height: 1; }
.cc-title { display: flex; flex-direction: column; gap: .2rem; }
.cc-title strong { font-size: 1rem; color: var(--text, #e2e8f0); }
.cc-year { font-size: .8rem; color: var(--text-muted, #94a3b8); }
.cc-stats {
    display: flex; gap: .75rem; flex-wrap: wrap;
    padding: .875rem;
    background: rgba(0,0,0,.15);
    border-radius: 8px;
}
.cc-stat { display: flex; flex-direction: column; align-items: center; gap: .15rem; min-width: 50px; }
.cc-stat-num { font-weight: 700; font-size: .95rem; color: var(--text, #e2e8f0); }
.cc-stat-lbl { font-size: .7rem; color: var(--text-muted, #94a3b8); text-transform: uppercase; letter-spacing: .04em; }
.cc-stat-flagged .cc-stat-num { color: #f87171; }
.cc-stat-cleared .cc-stat-num { color: #4ade80; }
.cc-stat-pending .cc-stat-num { color: #facc15; }
.cc-footer { display: flex; justify-content: flex-end; }
.cc-open { font-size: .85rem; color: var(--primary-light, #818cf8); font-weight: 500; }

/* ===== Table page styles ===== */
.back-link {
    display: inline-block;
    color: var(--text-muted, #94a3b8);
    text-decoration: none;
    font-size: .875rem;
    margin-bottom: .4rem;
    transition: color .15s;
}
.back-link:hover { color: var(--text, #e2e8f0); }

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

.row-flagged { background: rgba(239,68,68,.06) !important; }
.row-cleared { background: rgba(34,197,94,.04) !important; }

.flag-note { font-size: .85rem; color: var(--text-muted, #94a3b8); font-style: italic; cursor: help; }

.actions-cell { white-space: nowrap; }

.badge-count {
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,.25);
    border-radius: 999px;
    min-width: 20px; height: 20px;
    font-size: .75rem; font-weight: 700;
    padding: 0 6px; margin-left: 6px;
}

/* ===== Flag modal ===== */
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

/* Danger + Success buttons */
.btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff; border: none;
    padding: .5rem 1rem; border-radius: 8px;
    font-weight: 600; cursor: pointer;
    transition: opacity .2s, transform .15s;
    font-size: .875rem; text-decoration: none;
    display: inline-flex; align-items: center; gap: .35rem;
}
.btn-danger:hover { opacity: .9; transform: translateY(-1px); }
.btn-success {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff; border: none;
    padding: .5rem 1rem; border-radius: 8px;
    font-weight: 600; cursor: pointer;
    transition: opacity .2s, transform .15s;
    font-size: .875rem; text-decoration: none;
    display: inline-flex; align-items: center; gap: .35rem;
}
.btn-success:hover { opacity: .9; transform: translateY(-1px); }
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