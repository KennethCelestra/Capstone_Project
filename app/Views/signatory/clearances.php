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
<div class="page-header mb-4 d-flex justify-content-between align-items-center" style="padding-bottom: 1.25rem; border-bottom: 1px solid var(--border);">
    <div>
        <h2 style="font-size: 1.6rem; font-weight: 700; margin-bottom: .2rem;">My Clearances</h2>
        <p class="text-muted" style="margin: 0; font-size: .95rem;">Select a clearance to manage student statuses.</p>
    </div>
</div>

<?php if (empty($clearances)): ?>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 3rem; text-align: center;">
        <i class="bi bi-folder-x" style="font-size: 2.5rem; color: var(--text-muted); opacity: .4;"></i>
        <h3 style="margin-top: .75rem; font-size: 1.1rem;">No clearances assigned</h3>
        <p class="text-muted" style="font-size: .9rem;">You haven't been assigned to any clearance yet. Contact the administrator.</p>
    </div>
<?php else: ?>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
        <div class="table-responsive">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr style="background: var(--surface2);">
                        <th>Clearance Name</th>
                        <th>Academic Year</th>
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
                        $total   = (int) $c['total_students'];
                        $flagged = (int) $c['flagged_count'];
                        $cleared = (int) $c['cleared_count'];
                        $pending = $total - $flagged - $cleared;
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
?>

<div class="page-header mb-4" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border);">
    <div>
        <a href="<?= BASE_URL ?>signatory/clearances" class="back-link text-decoration-none d-inline-block mb-2" style="font-size: .9rem; color: var(--text-muted);"><i class="bi bi-arrow-left"></i> My Clearances</a>
        <h2 style="font-size: 1.6rem; font-weight: 700; margin-bottom: .2rem;"><?= htmlspecialchars($c['clearance_name']) ?></h2>
        <?php if (!empty($c['school_year'])): ?>
            <p class="text-muted" style="margin: 0; font-size: .95rem;"><?= htmlspecialchars($c['school_year']) ?></p>
        <?php endif; ?>
    </div>
    <div style="display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
        <button type="button" class="btn btn-primary" onclick="openConfirmAllModal()">
            <i class="bi bi-check2-all me-1"></i> Sign All
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

<!-- All Offices Clearance Progress — Dean only (A11) -->
<?php if (!empty($isDean) && !empty($otherSignatoryProgress)): ?>
<div class="card mb-4" style="background:var(--surface); border:1px solid var(--border); border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden;">
    <div style="padding:0.75rem 1.25rem; background:var(--surface2); border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
        <h5 style="margin:0; font-weight:700; font-size:1rem;"><i class="bi bi-bar-chart-line me-2"></i>All Offices Progress</h5>
        <span class="text-muted" style="font-size:0.82rem;">Visible to Dean only &mdash; expand a row to see flagged students</span>
    </div>
    <div class="table-responsive">
        <table class="data-table mb-0" style="width:100%; font-size:0.88rem;">
            <thead>
                <tr style="background:var(--surface2);">
                    <th style="padding:0.6rem 1rem; width:28px;"></th>
                    <th style="padding:0.6rem 1rem;">Office</th>
                    <th style="padding:0.6rem 1rem;">Signatory</th>
                    <th class="text-center" style="padding:0.6rem 1rem; width:12%;">Signed</th>
                    <th class="text-center" style="padding:0.6rem 1rem; width:12%;">Pending</th>
                    <th class="text-center" style="padding:0.6rem 1rem; width:12%;">Flagged</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($otherSignatoryProgress as $p): ?>
                <?php
                $sigId   = null;
                // Find the signatory_id key whose first row matches this office+full_name
                foreach ($flaggedBySignatory as $sid => $rows) {
                    if ($rows[0]['office'] === $p['office'] && $rows[0]['signatory_name'] === $p['full_name']) {
                        $sigId = $sid;
                        break;
                    }
                }
                $hasFlagged  = $sigId !== null && !empty($flaggedBySignatory[$sigId]);
                $expandRowId = 'dean-sig-expand-' . md5($p['office'] . $p['full_name']);
                ?>
                <tr class="border-bottom" style="cursor:<?= $hasFlagged ? 'pointer' : 'default' ?>;"
                    <?php if ($hasFlagged): ?>onclick="toggleDeanRow('<?= $expandRowId ?>', this)"<?php endif; ?>>
                    <td style="padding:0.5rem 0.75rem; text-align:center; color:var(--text-muted);">
                        <?php if ($hasFlagged): ?>
                            <i class="bi bi-chevron-right" id="icon-<?= $expandRowId ?>" style="transition:transform .2s;"></i>
                        <?php endif; ?>
                    </td>
                    <td style="padding:0.5rem 1rem;"><strong><?= htmlspecialchars($p['office']) ?></strong></td>
                    <td style="padding:0.5rem 1rem;" class="text-muted"><?= htmlspecialchars($p['full_name']) ?></td>
                    <td class="text-center" style="padding:0.5rem 1rem;"><span class="badge bg-success"><?= (int)$p['signed_count'] ?></span></td>
                    <td class="text-center" style="padding:0.5rem 1rem;"><span class="badge bg-warning text-dark"><?= (int)$p['pending_count'] ?></span></td>
                    <td class="text-center" style="padding:0.5rem 1rem;">
                        <?php if ((int)$p['flagged_count'] > 0): ?>
                            <span class="badge bg-danger"><?= (int)$p['flagged_count'] ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary">0</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($hasFlagged): ?>
                <tr id="<?= $expandRowId ?>" style="display:none; background:var(--surface2);">
                    <td></td>
                    <td colspan="5" style="padding:0.6rem 1.25rem 0.9rem;">
                        <div style="font-size:0.82rem; font-weight:600; color:var(--text-muted); margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.04em;">
                            <i class="bi bi-flag-fill text-danger me-1"></i> Flagged students by <?= htmlspecialchars($p['office']) ?>
                        </div>
                        <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                            <thead>
                                <tr style="color:var(--text-muted);">
                                    <th style="padding:0.3rem 0.5rem; font-weight:600;">Student ID</th>
                                    <th style="padding:0.3rem 0.5rem; font-weight:600;">Name</th>
                                    <th style="padding:0.3rem 0.5rem; font-weight:600;">Flag Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($flaggedBySignatory[$sigId] as $fs): ?>
                                <tr style="border-top:1px solid var(--border);">
                                    <td style="padding:0.35rem 0.5rem; font-family:monospace;"><?= htmlspecialchars($fs['student_number']) ?></td>
                                    <td style="padding:0.35rem 0.5rem;"><?= htmlspecialchars($fs['full_name']) ?></td>
                                    <td style="padding:0.35rem 0.5rem; color:var(--danger, #dc3545); font-style:italic;"><?= htmlspecialchars($fs['flag_note']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ===== Filter Bar (client-side, no page reload) ===== -->
<div>
    <div class="filter-bar">
        <div class="filter-group flex-grow-1" style="min-width: 200px;">
            <input type="text" id="sig-search"
                   placeholder="Search by name or ID…" class="form-control"
                   oninput="applyFilters()">
        </div>
        <div class="filter-group">
            <select id="sig-status-filter" class="form-select" onchange="applyFilters()">
                <option value="all">All Statuses</option>
                <option value="flagged">Flagged</option>
                <option value="cleared">Cleared</option>
                <option value="pending">Pending</option>
            </select>
        </div>
        <div class="filter-group">
            <select id="sig-college-filter" class="form-select" style="max-width: 140px;" onchange="loadFilterPrograms(this.value); applyFilters();">
                <option value="">All Colleges</option>
                <?php foreach ($colleges as $col): ?>
                    <option value="<?= htmlspecialchars($col) ?>"><?= htmlspecialchars($col) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <select id="sig-course-filter" class="form-select" style="max-width: 150px;" onchange="applyFilters()">
                <option value="">All Programs</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= htmlspecialchars($course) ?>"><?= htmlspecialchars($course) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if (!empty($yearLevels)): ?>
        <div class="filter-group">
            <select id="sig-year-filter" class="form-select" style="max-width: 110px;" onchange="applyFilters()">
                <option value="">All Years</option>
                <?php foreach ($yearLevels as $yr): ?>
                    <option value="<?= $yr ?>"><?= $yr ?><?= $yr == 1 ? 'st' : ($yr == 2 ? 'nd' : ($yr == 3 ? 'rd' : 'th')) ?> Yr</option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <button type="button" class="btn btn-secondary btn-sm" onclick="clearFilters()"
                title="Clear all filters" id="clear-filters-btn" style="display:none;">
            <i class="bi bi-x-lg"></i> Clear
        </button>
    </div>
</div>

<?php if (empty($students)): ?>
    <div class="gold-card text-center p-5" style="background:#fff; border-radius:8px;">
        <i class="bi bi-search display-1 text-muted opacity-50 mb-3 d-block"></i>
        <p class="text-muted fs-5">No students match the current filters.</p>
    </div>
<?php else: ?>
<form id="bulk-flag-form" action="<?= BASE_URL ?>signatory/students/flag-bulk" method="POST">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
    <input type="hidden" name="clearance_id" value="<?= $selectedCid ?>">
    <input type="hidden" name="flag_note" id="bulk-flag-note-hidden" value="">

    <!-- Sticky bulk-action toolbar (hidden until a checkbox is ticked) -->
    <div id="bulk-toolbar" style="display:none; position:sticky; top:0; z-index:50; background:var(--primary, #2563eb); color:#fff; border-radius:8px; padding:.75rem 1.25rem; margin-bottom:.75rem; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; box-shadow:0 4px 16px rgba(0,0,0,.18);">
        <span id="bulk-count-label" style="font-weight:600;">0 students selected</span>
        <div style="display:flex; gap:.5rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="deselectAll()">Deselect All</button>
            <button type="button" class="btn btn-danger btn-sm" onclick="openBulkFlagModal()">
                <i class="bi bi-flag-fill"></i> Flag Selected
            </button>
        </div>
    </div>

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
        <div class="table-responsive">
            <table class="data-table m-0" style="width: 100%;">
                <thead>
                    <tr style="background: var(--surface2);">
                        <th class="py-3 px-3" style="width:40px;">
                            <input type="checkbox" id="select-all-chk" title="Select all pending"
                                   onchange="toggleSelectAll(this.checked)"
                                   style="width:16px; height:16px; cursor:pointer; accent-color:var(--primary);">
                        </th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>College</th>
                        <th>Program</th>
                        <th>Year/Sec</th>
                        <th>Standing</th>
                        <th style="width:22%;">Flag Note</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- empty-state row shown when JS filters produce 0 results -->
                    <tr id="js-empty-row" style="display:none;">
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-search"></i> No students match the current filters.
                        </td>
                    </tr>
                    <?php foreach ($students as $s): ?>
                        <?php 
                        $rowClass = $s['status'] === 'flagged' ? 'table-danger' : ($s['status'] === 'cleared' ? 'table-success' : ''); 
                        $isFinal = !empty($s['is_final']);
                        $otherUnapproved = (int)($s['other_unapproved_count'] ?? 0);
                        $deanBlocked = $isFinal && $otherUnapproved > 0;
                        ?>
                        <tr class="border-bottom <?= $rowClass ?>" id="row-<?= $s['id'] ?>"
                            data-search="<?= strtolower(htmlspecialchars($s['last_name'] . ' ' . $s['first_name'] . ' ' . $s['student_number'])) ?>"
                            data-status="<?= htmlspecialchars($s['status']) ?>"
                            data-college="<?= strtolower(htmlspecialchars($s['college'])) ?>"
                            data-course="<?= strtolower(htmlspecialchars($s['course'])) ?>"
                            data-year="<?= htmlspecialchars($s['year_level']) ?>">
                            <td class="py-3 px-3">
                                <?php if ($s['status'] === 'pending'): ?>
                                    <?php if ($deanBlocked): ?>
                                        <input type="checkbox" disabled title="Waiting for other offices to clear" style="width:16px; height:16px; opacity:0.35; cursor:not-allowed;">
                                    <?php else: ?>
                                        <input type="checkbox" name="student_ids[]" value="<?= $s['id'] ?>"
                                               class="student-chk"
                                               style="width:16px; height:16px; cursor:pointer; accent-color:var(--primary);"
                                               onchange="onCheckboxChange()">
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($s['student_number']) ?></strong></td>
                            <td><?= htmlspecialchars($s['last_name']) ?>, <?= htmlspecialchars($s['first_name']) ?></td>
                            <td><?= htmlspecialchars($s['college']) ?></td>
                            <td><?= htmlspecialchars($s['course']) ?></td>
                            <td><?= $s['year_level'] ?>–<?= htmlspecialchars($s['section']) ?></td>
                            <td>
                                <?php if ($s['status'] === 'flagged'): ?>
                                    <span class="badge bg-danger">Flagged</span>
                                <?php elseif ($s['status'] === 'cleared'): ?>
                                    <span class="badge bg-success">Cleared</span>
                                <?php elseif ($deanBlocked): ?>
                                    <span class="badge bg-secondary" title="Waiting for other offices to sign first"><i class="bi bi-hourglass-split"></i> Awaiting Others</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php if ($s['status'] === 'flagged' && !empty($s['flag_note'])): ?>
                                    <span class="text-danger small" title="<?= htmlspecialchars($s['flag_note']) ?>">
                                        <?= htmlspecialchars($s['flag_note']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end" style="white-space:nowrap;">
                                <div class="action-cell" style="display:flex; justify-content:flex-end; gap:0.4rem; align-items:center;">
                                    <?php if ($s['status'] === 'flagged'): ?>
                                        <button type="button" class="btn btn-success btn-sm" onclick="openUnflagModal(<?= $selectedCid ?>, <?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['first_name'] . ' ' . $s['last_name'])) ?>')" <?= $deanBlocked ? 'disabled title="Cannot sign until other offices clear this student"' : '' ?>>
                                            <i class="bi bi-check2"></i> Sign
                                        </button>
                                    <?php elseif ($s['status'] === 'cleared'): ?>
                                        <span class="badge bg-success" style="padding:0.4rem 0.6rem;"><i class="bi bi-check2"></i> Signed</span>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="openFlagModal(<?= $selectedCid ?>, <?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['first_name'] . ' ' . $s['last_name'])) ?>')" title="Flag this student">
                                            <i class="bi bi-flag"></i> Flag
                                        </button>
                                    <?php else: ?>
                                        <?php if ($deanBlocked): ?>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Waiting for other offices to clear this student first">
                                                <i class="bi bi-hourglass-split"></i> Awaiting Others
                                            </button>
                                        <?php else: ?>
                                            <form action="<?= BASE_URL ?>signatory/students/clear" method="POST" style="margin:0; display:inline;">
                                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                                <input type="hidden" name="clearance_id" value="<?= $selectedCid ?>">
                                                <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="bi bi-check2"></i> Sign
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-danger btn-sm"
                                                onclick="openFlagModal(<?= $selectedCid ?>, <?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['first_name'] . ' ' . $s['last_name'])) ?>')">
                                            <i class="bi bi-flag"></i> Flag
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                            onclick="openRequirementsModal(<?= $selectedCid ?>, <?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['last_name'] . ', ' . $s['first_name'])) ?>')"
                                            title="Manage Requirements / Uploads">
                                        <i class="bi bi-paperclip"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>
<?php endif; ?>
<?php endif; ?>

<!-- ===== Flag Modal (single) ===== -->
<div id="flag-modal" class="modal" style="display:none;" onclick="closeFlagModalOnOverlay(event)">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Flag Student with Remark</h3>
            <button type="button" class="modal-close" onclick="closeFlagModal()">✕</button>
        </div>
        <form action="<?= BASE_URL ?>signatory/students/flag" method="POST" id="flag-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="clearance_id" id="modal-clearance-id">
            <input type="hidden" name="student_id"   id="modal-student-id">
            <div class="modal-body">
                <p>You are flagging: <strong id="modal-student-name"></strong></p>
                <label for="flag-note-input" class="form-label">Flag Reason / Remark</label>
                <textarea id="flag-note-input" name="flag_note" class="form-control" rows="4"
                          placeholder="Describe the issue or requirement the student needs to fulfill…"
                          required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeFlagModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Flag</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== Bulk Flag Modal ===== -->
<div id="bulk-flag-modal" class="modal" style="display:none;" onclick="closeBulkFlagModalOnOverlay(event)">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="bi bi-flag-fill"></i> Bulk Flag Students</h3>
            <button type="button" class="modal-close" onclick="closeBulkFlagModal()">✕</button>
        </div>
        <div class="modal-body">
            <p>You are flagging <strong id="bulk-flag-count">0</strong> selected student(s) with the same flag reason.</p>
            <label for="bulk-flag-note" class="form-label">Shared Flag Reason</label>
            <textarea id="bulk-flag-note" class="form-control" rows="4"
                      placeholder="e.g. Unpaid tuition fees, Missing library clearance…"
                      required></textarea>
            <p class="text-muted small mt-2" style="margin-bottom:0;">
                <i class="bi bi-info-circle"></i> This reason will be sent to all selected students via email.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeBulkFlagModal()">Cancel</button>
            <button type="button" class="btn btn-danger" id="bulk-flag-submit-btn" onclick="submitBulkFlag()">
                <i class="bi bi-flag-fill"></i> Flag All Selected
            </button>
        </div>
    </div>
</div>

<!-- ===== Unflag Modal ===== -->
<div id="unflag-modal" class="modal" style="display:none;" onclick="closeUnflagModalOnOverlay(event)">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Clear Flag</h3>
            <button type="button" class="modal-close" onclick="closeUnflagModal()">✕</button>
        </div>
        <form action="<?= BASE_URL ?>signatory/students/clear" method="POST" id="unflag-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="clearance_id" id="unflag-modal-clearance-id">
            <input type="hidden" name="student_id"   id="unflag-modal-student-id">
            <div class="modal-body">
                <p>Are you sure you want to clear the flag for <strong id="unflag-modal-student-name"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeUnflagModal()">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-check2"></i> Sign</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== Confirm / Sign All Modal ===== -->
<div id="confirm-all-modal" class="modal" style="display:none;" onclick="closeConfirmAllModalOnOverlay(event)">
    <div class="modal-box" style="max-width: 440px; padding: 1.25rem 1.5rem;">
        <div class="modal-header" style="margin-bottom: 0.75rem;">
            <h3>Sign All</h3>
            <button type="button" class="modal-close" onclick="closeConfirmAllModal()">✕</button>
        </div>
        <form action="<?= BASE_URL ?>signatory/confirm-all" method="POST" id="confirm-all-form-modal">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="clearance_id" value="<?= $selectedCid ?? 0 ?>">
            <div class="modal-body" style="padding: 0; margin-bottom: 1rem;">
                <p style="margin-bottom: 0.25rem;">This will sign and clear all pending students.</p>
                <p class="text-muted" style="font-size: 0.85rem; margin: 0;">Are you sure you want to continue?</p>
            </div>
            <div class="modal-footer" style="padding-top: 0; margin-top: 1rem; border-top: none; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmAllModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="confirm-all-btn-modal" onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm\'></span> Processing...'; this.form.submit();">Sign All</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== Requirements Modal ===== -->
<div id="requirements-modal" class="modal" style="display:none;" onclick="closeRequirementsModalOnOverlay(event)">
    <div class="modal-box" style="max-width: 580px;">
        <div class="modal-header">
            <h3><i class="bi bi-paperclip me-1"></i> Student Requirements</h3>
            <button type="button" class="modal-close" onclick="closeRequirementsModal()">✕</button>
        </div>
        <div class="modal-body">
            <p class="mb-3">Student: <strong id="req-modal-student-name"></strong></p>

            <!-- Upload Box -->
            <form action="<?= BASE_URL ?>signatory/students/upload-requirement" method="POST" enctype="multipart/form-data" class="mb-3" style="background:var(--surface2); border:1px solid var(--border); border-radius:8px; padding:0.9rem;">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                <input type="hidden" name="clearance_id" id="req-upload-clearance-id">
                <input type="hidden" name="student_id"   id="req-upload-student-id">
                <label class="form-label" style="font-weight:600; font-size:0.85rem;">Upload Document (PDF, JPG, PNG &mdash; max 5MB)</label>
                <div style="display:flex; gap:0.5rem; align-items:center;">
                    <input type="file" name="requirement" class="form-control form-control-sm" required accept=".pdf,.jpg,.jpeg,.png,.webp" style="flex:1;">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-upload me-1"></i> Upload
                    </button>
                </div>
            </form>

            <h5 style="font-size:0.92rem; font-weight:700; margin-bottom:0.5rem;">Uploaded Files</h5>
            <div id="req-files-container" style="max-height:220px; overflow-y:auto;">
                <div class="text-center py-3 text-muted"><span class="spinner-border spinner-border-sm"></span> Loading...</div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeRequirementsModal()">Close</button>
        </div>
    </div>
</div>



<script>
// ---- Single flag modal ----
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

// ---- Unflag modal ----
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

// ---- Confirm All modal ----
function openConfirmAllModal() {
    document.getElementById('confirm-all-modal').style.display = 'flex';
}
function closeConfirmAllModal() {
    document.getElementById('confirm-all-modal').style.display = 'none';
}
function closeConfirmAllModalOnOverlay(e) {
    if (e.target === document.getElementById('confirm-all-modal')) closeConfirmAllModal();
}

// ---- Checkbox helpers (visible-rows-only) ----
function getCheckedBoxes() {
    return Array.from(document.querySelectorAll('.student-chk:checked'));
}
function getVisiblePendingCheckboxes() {
    return Array.from(document.querySelectorAll('.student-chk')).filter(chk => {
        return chk.closest('tr').style.display !== 'none';
    });
}
function onCheckboxChange() {
    const checked     = getCheckedBoxes();
    const count       = checked.length;
    const visibleChks = getVisiblePendingCheckboxes();
    const toolbar     = document.getElementById('bulk-toolbar');
    const label       = document.getElementById('bulk-count-label');
    const selectAllChk = document.getElementById('select-all-chk');

    toolbar.style.display = count > 0 ? 'flex' : 'none';
    label.textContent = count + ' student' + (count !== 1 ? 's' : '') + ' selected';

    // Reflect select-all state against visible pending rows only
    const visibleChecked = visibleChks.filter(c => c.checked).length;
    if (visibleChecked === 0) {
        selectAllChk.checked = false;
        selectAllChk.indeterminate = false;
    } else if (visibleChecked === visibleChks.length) {
        selectAllChk.checked = true;
        selectAllChk.indeterminate = false;
    } else {
        selectAllChk.indeterminate = true;
    }
}
function toggleSelectAll(checked) {
    // Only toggle visible pending rows
    getVisiblePendingCheckboxes().forEach(chk => chk.checked = checked);
    onCheckboxChange();
}
function deselectAll() {
    document.querySelectorAll('.student-chk').forEach(chk => chk.checked = false);
    document.getElementById('select-all-chk').checked = false;
    onCheckboxChange();
}
function openBulkFlagModal() {
    const count = getCheckedBoxes().length;
    if (count === 0) return;
    document.getElementById('bulk-flag-count').textContent = count;
    document.getElementById('bulk-flag-note').value = '';
    document.getElementById('bulk-flag-modal').style.display = 'flex';
    setTimeout(() => document.getElementById('bulk-flag-note').focus(), 100);
}
function closeBulkFlagModal() {
    document.getElementById('bulk-flag-modal').style.display = 'none';
}
function closeBulkFlagModalOnOverlay(e) {
    if (e.target === document.getElementById('bulk-flag-modal')) closeBulkFlagModal();
}
function submitBulkFlag() {
    const note = document.getElementById('bulk-flag-note').value.trim();
    if (!note) {
        document.getElementById('bulk-flag-note').focus();
        return;
    }
    const btn = document.getElementById('bulk-flag-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Flagging…';
    document.getElementById('bulk-flag-note-hidden').value = note;
    document.getElementById('bulk-flag-form').submit();
}

// ---- Client-side filtering ----
function loadFilterPrograms(college) {
    const sel = document.getElementById('sig-course-filter');
    if (!sel) return;
    sel.innerHTML = '<option value="">All Programs</option>';
    if (!college) {
        applyFilters();
        return;
    }
    fetch('<?= BASE_URL ?>api/programs?college=' + encodeURIComponent(college))
        .then(r => r.json())
        .then(data => {
            data.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.code;
                opt.textContent = p.code;
                sel.appendChild(opt);
            });
            applyFilters();
        })
        .catch(() => applyFilters());
}

function applyFilters() {
    const search  = (document.getElementById('sig-search')?.value  || '').toLowerCase().trim();
    const status  =  document.getElementById('sig-status-filter')?.value || 'all';
    const college = (document.getElementById('sig-college-filter')?.value || '').toLowerCase().trim();
    const course  = (document.getElementById('sig-course-filter')?.value  || '').toLowerCase().trim();
    const year    = (document.getElementById('sig-year-filter')?.value    || '').trim();

    const rows    = document.querySelectorAll('tbody tr[data-search]');
    let   visible = 0;

    rows.forEach(row => {
        const matchSearch  = !search  || row.dataset.search.includes(search);
        const matchStatus  = status === 'all' || row.dataset.status === status;
        const matchCollege = !college || row.dataset.college === college;
        const matchCourse  = !course  || row.dataset.course  === course;
        const matchYear    = !year    || row.dataset.year    === year;

        const show = matchSearch && matchStatus && matchCollege && matchCourse && matchYear;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    // Show inline empty-state row if nothing visible
    const emptyRow = document.getElementById('js-empty-row');
    if (emptyRow) emptyRow.style.display = visible === 0 ? '' : 'none';

    // Show/hide the Clear button
    const hasFilter = search || status !== 'all' || college || course || year;
    const clearBtn = document.getElementById('clear-filters-btn');
    if (clearBtn) clearBtn.style.display = hasFilter ? '' : 'none';

    // Re-sync checkbox state after filter
    onCheckboxChange();
}
function clearFilters() {
    const ids = ['sig-search', 'sig-college-filter', 'sig-course-filter', 'sig-year-filter'];
    ids.forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    const status = document.getElementById('sig-status-filter');
    if (status) status.value = 'all';
    applyFilters();
}

// ---- Dean: expand/collapse flagged-student sub-rows ----
function toggleDeanRow(rowId, triggerTr) {
    const row  = document.getElementById(rowId);
    const icon = document.getElementById('icon-' + rowId);
    if (!row) return;
    const isOpen = row.style.display !== 'none';
    row.style.display = isOpen ? 'none' : '';
    if (icon) icon.style.transform = isOpen ? '' : 'rotate(90deg)';
}

// ---- Requirements modal ----
function openRequirementsModal(cid, sid, studentName) {
    document.getElementById('req-upload-clearance-id').value = cid;
    document.getElementById('req-upload-student-id').value   = sid;
    document.getElementById('req-modal-student-name').textContent = studentName;
    document.getElementById('requirements-modal').style.display = 'flex';

    const container = document.getElementById('req-files-container');
    container.innerHTML = '<div class="text-center py-3 text-muted"><span class="spinner-border spinner-border-sm"></span> Loading...</div>';

    fetch('<?= BASE_URL ?>signatory/students/requirements?clearance_id=' + cid + '&student_id=' + sid)
        .then(r => r.json())
        .then(files => {
            if (!files || files.length === 0) {
                container.innerHTML = '<p class="text-muted small mb-0">No requirements uploaded yet for this student.</p>';
                return;
            }
            let html = '<div class="list-group">';
            files.forEach(f => {
                const dateStr = new Date(f.uploaded_at).toLocaleDateString(undefined, {month:'short', day:'numeric', year:'numeric'});
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center p-2 mb-1" style="background:var(--surface); border:1px solid var(--border); border-radius:6px;">
                        <div>
                            <strong style="font-size:0.88rem;">${escapeHtml(f.file_name)}</strong>
                            <div class="text-muted" style="font-size:0.75rem;">
                                ${escapeHtml(f.signatory_name)} (${escapeHtml(f.office)}) &bull; Initial: <span class="badge bg-secondary" style="font-size:0.7rem;">${escapeHtml(f.initials || 'SIG')}</span> &bull; ${dateStr}
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="<?= BASE_URL ?>signatory/requirements/download?id=${f.id}" class="btn btn-sm btn-outline-primary" target="_blank" title="Download">
                                <i class="bi bi-download"></i>
                            </a>
                            <form action="<?= BASE_URL ?>signatory/students/delete-requirement" method="POST" style="margin:0;" onsubmit="return confirm('Delete this requirement file?')">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                <input type="hidden" name="clearance_id" value="${cid}">
                                <input type="hidden" name="id" value="${f.id}">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        })
        .catch(() => {
            container.innerHTML = '<p class="text-danger small mb-0">Failed to load requirements.</p>';
        });
}

function closeRequirementsModal() {
    document.getElementById('requirements-modal').style.display = 'none';
}
function closeRequirementsModalOnOverlay(e) {
    if (e.target === document.getElementById('requirements-modal')) closeRequirementsModal();
}
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// ---- Escape closes all modals ----
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFlagModal();
        closeUnflagModal();
        closeConfirmAllModal();
        closeBulkFlagModal();
        closeRequirementsModal();
    }
});
</script>
