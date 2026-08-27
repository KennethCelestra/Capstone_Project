<?php
/**
 * Enrollment Committee: My Clearances — two-phase view
 *   $phase === 'select'  → clearance selection cards
 *   $phase === 'detail'  → student status table for selected clearance
 */
?>

<?php if ($phase === 'select'): ?>
<!-- =====================================================
     PHASE 1 — Clearance Selection Cards
     ===================================================== -->
<div class="page-header mb-4 d-flex justify-content-between align-items-center" style="padding-bottom: 1.25rem; border-bottom: 1px solid var(--border);">
    <div>
        <h2 style="font-size: 1.6rem; font-weight: 700; margin-bottom: .2rem;">My Clearances</h2>
        <p class="text-muted" style="margin: 0; font-size: .95rem;">Select a clearance to view student clearance standing.</p>
    </div>
</div>

<?php if (empty($clearances)): ?>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 3rem; text-align: center;">
        <i class="bi bi-folder-x" style="font-size: 2.5rem; color: var(--text-muted); opacity: .4;"></i>
        <h3 style="margin-top: .75rem; font-size: 1.1rem;">No clearances assigned</h3>
        <p class="text-muted" style="font-size: .9rem;">You haven't been assigned to any clearance yet. Contact your administrator.</p>
    </div>
<?php else: ?>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
        <div class="table-responsive">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr style="background: var(--surface2);">
                        <th>Clearance Name</th>
                        <th>School Year</th>
                        <th class="text-center">Students</th>
                        <th class="text-center">Pending</th>
                        <th class="text-center">Flagged</th>
                        <th class="text-center">Cleared</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clearances as $c): ?>
                        <?php
                        $total   = count($c['students']);
                        $flagged = $c['flagged_total'];
                        $cleared = $c['cleared_total'];
                        $pending = $c['pending_total'];
                        ?>
                        <tr class="border-bottom" style="transition: background .15s;" onmouseenter="this.style.background='var(--surface2)'" onmouseleave="this.style.background=''">
                            <td>
                                <strong><?= htmlspecialchars($c['clearance_name']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($c['school_year'] ?? '') ?></td>
                            <td class="text-center"><span class="badge badge-info"><?= $total ?></span></td>
                            <td class="text-center">
                                <?php if ($pending > 0): ?>
                                    <span class="badge badge-warning text-dark"><?= $pending ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($flagged > 0): ?>
                                    <span class="badge badge-danger"><?= $flagged ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($cleared > 0): ?>
                                    <span class="badge badge-success"><?= $cleared ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>enrollment-committee/clearances?cid=<?= $c['clearance_id'] ?>" class="btn btn-primary btn-sm">
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

<div class="page-header mb-4" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border);">
    <div>
        <a href="<?= BASE_URL ?>enrollment-committee/clearances" class="back-link text-decoration-none d-inline-block mb-2" style="font-size: .9rem; color: var(--text-muted);"><i class="bi bi-arrow-left"></i> My Clearances</a>
        <h2 style="font-size: 1.6rem; font-weight: 700; margin-bottom: .2rem;"><?= htmlspecialchars($c['clearance_name']) ?></h2>
        <?php if (!empty($c['school_year'])): ?>
            <p class="text-muted" style="margin: 0; font-size: .95rem;"><?= htmlspecialchars($c['school_year']) ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Summary badges -->
<div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; margin-bottom:1.25rem;">
    <span class="badge bg-info text-dark"><i class="bi bi-people"></i> <?= $totalHere ?> students</span>
    <?php if ($cFlagged > 0): ?>
        <span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> <?= $cFlagged ?> with deficiency</span>
    <?php endif; ?>
    <span class="badge bg-success"><i class="bi bi-check-circle"></i> <?= $cCleared ?> cleared</span>
    <span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> <?= $cPending ?> pending</span>
</div>

<!-- ===== Filter Bar ===== -->
<div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 1rem; margin-bottom: 1.25rem;">
    <form method="GET" action="<?= BASE_URL ?>enrollment-committee/clearances" class="filter-bar m-0 d-flex gap-3 align-items-center flex-wrap">
        <input type="hidden" name="cid" value="<?= $selectedCid ?>">
        <div class="filter-group flex-grow-1" style="min-width: 200px;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search by name or ID…" class="form-control" id="adv-search">
        </div>
        <div class="filter-group">
            <select name="status" class="form-select" id="adv-status-filter" onchange="this.form.submit()">
                <option value="all"     <?= $filterStatus === 'all'     ? 'selected' : '' ?>>All Statuses</option>
                <option value="flagged" <?= $filterStatus === 'flagged' ? 'selected' : '' ?>>Has Deficiency</option>
                <option value="cleared" <?= $filterStatus === 'cleared' ? 'selected' : '' ?>>Fully Cleared</option>
                <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>In Progress</option>
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
    <div class="blue-card text-center p-5" style="background:#fff; border-radius:8px;">
        <i class="bi bi-search display-1 text-muted opacity-50 mb-3 d-block"></i>
        <p class="text-muted fs-5">No students match the current filters.</p>
    </div>
<?php else: ?>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
        <div class="table-responsive">
            <table class="data-table enrollment-table m-0" style="width: 100%;">
                <thead>
                    <tr style="background: var(--surface2);">
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>College</th>
                        <th>Course</th>
                        <th>Year/Sec</th>
                        <th>Status</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                        <?php 
                        $rowId = 'detail-' . $selectedCid . '-' . $s['id'];
                        $flagged = array_sum(array_map(fn($sg) => $sg['status'] === 'flagged' ? 1 : 0, $s['signatory_detail']));
                        $totalSig = count($s['signatory_detail']);
                        $cleared = array_sum(array_map(fn($sg) => $sg['status'] === 'cleared' ? 1 : 0, $s['signatory_detail']));
                        ?>
                        <tr class="border-bottom <?= $flagged > 0 ? 'table-danger' : ($cleared === $totalSig && $totalSig > 0 ? 'table-success' : '') ?>">
                            <td><strong><?= htmlspecialchars($s['student_number']) ?></strong></td>
                            <td><?= htmlspecialchars($s['last_name']) ?>, <?= htmlspecialchars($s['first_name']) ?></td>
                            <td><?= htmlspecialchars($s['college']) ?></td>
                            <td><?= htmlspecialchars($s['course']) ?></td>
                            <td><?= $s['year_level'] ?>–<?= htmlspecialchars($s['section']) ?></td>
                            <td>
                                <?php if ($flagged > 0): ?>
                                    <span class="badge bg-danger">FLAG</span>
                                <?php elseif ($cleared === $totalSig && $totalSig > 0): ?>
                                    <span class="badge bg-success">CLEARED</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">PENDING</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="toggleDetail('<?= $rowId ?>')">
                                    <i class="bi bi-eye"></i> View
                                </button>
                            </td>
                        </tr>
                        <!-- Expandable per-signatory detail row -->
                        <tr id="<?= $rowId ?>" class="detail-row bg-light" style="display:none;">
                            <td colspan="7" class="p-4">
                                <h6 class="text-muted mb-3"><i class="bi bi-list-check"></i> Signatory Status Breakdown</h6>
                                <div class="signatory-detail-grid d-flex flex-wrap gap-3">
                                    <?php foreach ($s['signatory_detail'] as $sg): ?>
                                        <div class="card shadow-sm flex-fill" style="min-width: 250px; border-left: 4px solid <?= $sg['status'] === 'flagged' ? 'var(--danger)' : ($sg['status'] === 'cleared' ? 'var(--success)' : 'var(--warning)') ?>">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="fw-bold"><?= htmlspecialchars($sg['office']) ?></span>
                                                    <?php if ($sg['status'] === 'flagged'): ?>
                                                        <span class="badge bg-danger">Flagged</span>
                                                    <?php elseif ($sg['status'] === 'cleared'): ?>
                                                        <span class="badge bg-success">Cleared</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-muted small mb-2"><i class="bi bi-person"></i> <?= htmlspecialchars($sg['signatory_name']) ?></div>
                                                <?php if ($sg['status'] === 'flagged' && !empty($sg['flag_note'])): ?>
                                                    <div class="alert alert-danger p-2 mb-0 small">
                                                        <i class="bi bi-exclamation-triangle"></i> <?= nl2br(htmlspecialchars($sg['flag_note'])) ?>
                                                    </div>
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
        if (btn) btn.innerHTML = '<i class="bi bi-eye-slash"></i> Hide';
    } else {
        row.style.display = 'none';
        if (btn) btn.innerHTML = '<i class="bi bi-eye"></i> View';
    }
}
</script>
