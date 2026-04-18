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
        <h2>🎓 My Clearances</h2>
        <p class="text-muted">Select a clearance to view student clearance standing.</p>
    </div>
</div>

<?php if (empty($clearances)): ?>
    <div class="empty-state">
        <div class="empty-icon">🎓</div>
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
                        <span class="cc-stat-lbl">Deficiency</span>
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
        <h2>🎓 <?= htmlspecialchars($c['clearance_name']) ?></h2>
        <?php if (!empty($c['school_year'])): ?>
            <p class="text-muted" style="margin-top:.25rem"><?= htmlspecialchars($c['school_year']) ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Summary badges -->
<div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; margin-bottom:1.25rem;">
    <span class="badge badge-info"><?= $totalHere ?> students</span>
    <?php if ($cFlagged > 0): ?>
        <span class="badge badge-danger">🚩 <?= $cFlagged ?> with deficiency</span>
    <?php endif; ?>
    <span class="badge badge-success">✅ <?= $cCleared ?> cleared</span>
    <span class="badge badge-warning">⏳ <?= $cPending ?> pending</span>
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
    </div>
<?php endif; ?>
<?php endif; ?>

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
.clearance-card.card-has-flags { border-color: rgba(239,68,68,.4); }
.clearance-card.card-has-flags:hover { border-color: rgba(239,68,68,.7); }

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

/* ===== Detail table styles ===== */
.back-link {
    display: inline-block; color: var(--text-muted, #94a3b8);
    text-decoration: none; font-size: .875rem;
    margin-bottom: .4rem; transition: color .15s;
}
.back-link:hover { color: var(--text, #e2e8f0); }

.filter-bar {
    display: flex; flex-wrap: wrap; gap: .75rem; align-items: center;
    margin-bottom: 1.5rem; padding: 1rem 1.25rem;
    background: var(--card-bg, #1e293b);
    border: 1px solid var(--border, #334155);
    border-radius: 10px;
}
.filter-group { display: flex; align-items: center; }
.search-input { min-width: 220px; }

.enrollment-table th, .enrollment-table td { font-size: .85rem; vertical-align: top; }

.row-flagged { background: rgba(239,68,68,.06) !important; }
.row-cleared { background: rgba(34,197,94,.04) !important; }

.standing-badge {
    display: inline-block; padding: .25rem .75rem;
    border-radius: 6px; font-size: .78rem; font-weight: 700; letter-spacing: .03em;
}
.standing-flagged { background: rgba(239,68,68,.15); color: #f87171; border: 1px solid rgba(239,68,68,.3); }
.standing-cleared { background: rgba(34,197,94,.12); color: #4ade80; border: 1px solid rgba(34,197,94,.3); }
.standing-pending { background: rgba(234,179,8,.1);  color: #facc15; border: 1px solid rgba(234,179,8,.25); }

.deficiency-list {
    margin: 0; padding: 0 0 0 1rem;
    font-size: .82rem; color: var(--text-muted, #94a3b8);
    font-style: italic; line-height: 1.5;
}
.deficiency-list li { margin-bottom: .25rem; }
.deficiency-list li strong { color: var(--text, #e2e8f0); font-style: normal; }

.email-link { color: var(--accent, #6366f1); text-decoration: none; font-size: .82rem; }
.email-link:hover { text-decoration: underline; }

.detail-row td { padding: 0 !important; border-top: none !important; }

.signatory-detail-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    gap: .75rem; padding: 1rem 1.25rem; background: rgba(0,0,0,.15);
}
.signatory-detail-card {
    background: var(--card-bg, #1e293b); border: 1px solid var(--border, #334155);
    border-radius: 8px; padding: .875rem;
}
.signatory-detail-card.card-flagged { border-color: rgba(239,68,68,.5); background: rgba(239,68,68,.05); }
.signatory-detail-card.card-cleared { border-color: rgba(34,197,94,.4); background: rgba(34,197,94,.04); }
.sd-header { display: flex; flex-direction: column; margin-bottom: .5rem; }
.sd-office  { font-weight: 600; font-size: .9rem; }
.sd-name    { font-size: .8rem; }
.sd-status  { display: flex; flex-direction: column; gap: .35rem; }
.sd-note    { font-size: .82rem; color: var(--text-muted, #94a3b8); font-style: italic; margin: .35rem 0 0; line-height: 1.4; }

.badge-danger {
    background: rgba(239,68,68,.15); color: #f87171;
    border: 1px solid rgba(239,68,68,.3);
    padding: .2rem .6rem; border-radius: 999px;
    font-size: .78rem; font-weight: 600;
}
.badge-info {
    background: rgba(99,102,241,.15); color: #818cf8;
    border: 1px solid rgba(99,102,241,.3);
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
        if (btn) btn.textContent = 'Hide ▴';
    } else {
        row.style.display = 'none';
        if (btn) btn.textContent = 'View ▾';
    }
}
</script>
