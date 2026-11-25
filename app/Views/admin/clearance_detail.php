<?php
$cid = $clearance['id'];
$isStarted = !empty($clearance['started_at']);
?>

<!-- ========================================================
     MAIN CLEARANCE DETAIL PAGE
     ======================================================== -->
<div class="page-header">
    <div>
        <a href="<?= BASE_URL ?>admin/clearances" class="back-link">← All Clearances</a>
        <h2><?= htmlspecialchars($clearance['name']) ?></h2>
        <p class="text-muted">
            <?= htmlspecialchars($clearance['school_year']) ?>
            <?php if (!empty($clearance['description'])): ?>
                — <?= htmlspecialchars($clearance['description']) ?>
            <?php endif; ?>
        </p>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
        <button class="btn btn-secondary btn-sm"
                onclick="openEditClearanceDetail(<?= $cid ?>, '<?= htmlspecialchars(addslashes($clearance['name'])) ?>', '<?= htmlspecialchars(addslashes($clearance['description'])) ?>', '<?= htmlspecialchars($clearance['school_year']) ?>')">
            <i class="bi bi-pencil me-1"></i> Edit Info
        </button>
        <?php
        $approvalMap = [];
        foreach ($collegeApprovals ?? [] as $ca) {
            $approvalMap[$ca['college']] = $ca;
        }
        $hasAllApproval = isset($approvalMap['ALL']);
        $totalEnrolledCols = count($enrolledColleges ?? []);
        $approvedColCount = 0;
        $hasUnsubmittedOrRejected = false;
        if ($hasAllApproval) {
            if ($approvalMap['ALL']['status'] === 'approved') {
                $approvedColCount = $totalEnrolledCols;
            } elseif ($approvalMap['ALL']['status'] === 'rejected') {
                $hasUnsubmittedOrRejected = true;
            }
        } else {
            foreach ($enrolledColleges ?? [] as $col) {
                $st = $approvalMap[$col]['status'] ?? null;
                if ($st === 'approved') {
                    $approvedColCount++;
                } elseif ($st === null || $st === 'rejected') {
                    $hasUnsubmittedOrRejected = true;
                }
            }
        }
        $isLegacyDirectStart = $isStarted && empty($collegeApprovals);
        ?>

        <?php if ($isLegacyDirectStart): ?>
            <span class="badge badge-success" title="Signing started">
                Started <?= date('M j, Y g:i A', strtotime($clearance['started_at'])) ?>
            </span>
        <?php else: ?>
            <?php if (!empty($collegeApprovals)): ?>
                <span class="badge <?= ($approvedColCount > 0 && $approvedColCount === $totalEnrolledCols) ? 'badge-success' : 'badge-warning text-dark' ?>" style="padding:0.4rem 0.75rem;">
                    <i class="bi bi-shield-check me-1"></i>
                    <?= $approvedColCount ?> / <?= $totalEnrolledCols ?> College(s) Approved
                </span>
            <?php endif; ?>
            <?php if ($hasUnsubmittedOrRejected || empty($collegeApprovals)): ?>
                <form action="<?= BASE_URL ?>admin/clearances/submit-for-approval" method="POST" style="margin:0; display:inline;">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                    <input type="hidden" name="clearance_id" value="<?= $cid ?>">
                    <button type="submit" class="btn btn-warning btn-sm"
                            onclick="return confirm('Submit unapproved college(s) to their respective Deans for approval?')">
                        <i class="bi bi-send-check me-1"></i> Submit for Dean Approval
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php if (!$isLegacyDirectStart && !empty($collegeApprovals)): ?>
<!-- ===== PER-COLLEGE DEAN APPROVAL STATUS ===== -->
<div class="card mb-4" style="background:var(--surface); border:1px solid var(--border); border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden;">
    <div style="padding:0.75rem 1.25rem; background:var(--surface2); border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
        <h5 style="margin:0; font-weight:700; font-size:0.98rem;">
            <i class="bi bi-shield-check me-2"></i>Dean Approval Status by College
        </h5>
        <span class="text-muted" style="font-size:0.82rem;">Students become visible to signatories as soon as their college's Dean approves</span>
    </div>
    <div style="padding:0.85rem 1.25rem; display:flex; flex-wrap:wrap; gap:0.65rem;">
        <?php
        $displayItems = $hasAllApproval
            ? [['college' => 'ALL', 'label' => 'All Colleges', 'row' => $approvalMap['ALL']]]
            : array_map(fn($col) => ['college' => $col, 'label' => $col, 'row' => $approvalMap[$col] ?? null], $enrolledColleges ?? []);
        ?>
        <?php foreach ($displayItems as $item): ?>
            <?php
            $row    = $item['row'];
            $status = $row['status'] ?? 'unsubmitted';
            $dean   = $row['approver_name'] ?? '—';
            $note   = $row['note'] ?? '';
            $bg     = '#f3f4f6'; $border = '#d1d5db'; $badgeCls = 'bg-secondary'; $statusLabel = 'Not Submitted';
            if ($status === 'approved') {
                $bg = '#f0fdf4'; $border = '#86efac'; $badgeCls = 'bg-success'; $statusLabel = 'Approved';
            } elseif ($status === 'pending') {
                $bg = '#fffbeb'; $border = '#fde68a'; $badgeCls = 'bg-warning text-dark'; $statusLabel = 'Pending';
            } elseif ($status === 'rejected') {
                $bg = '#fef2f2'; $border = '#fca5a5'; $badgeCls = 'bg-danger'; $statusLabel = 'Rejected';
            }
            ?>
            <div style="background:<?= $bg ?>; border:1px solid <?= $border ?>; border-radius:8px; padding:0.6rem 0.9rem; min-width:200px; flex:1 1 200px; max-width:300px;">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                    <strong style="font-size:0.92rem;"><?= htmlspecialchars($item['label']) ?></strong>
                    <span class="badge <?= $badgeCls ?>" style="font-size:0.74rem;"><?= $statusLabel ?></span>
                </div>
                <div style="font-size:0.78rem; color:var(--text-muted);">
                    Dean: <strong><?= htmlspecialchars($dean) ?></strong>
                </div>
                <?php if ($status === 'rejected' && $note !== ''): ?>
                    <div style="font-size:0.78rem; color:#b91c1c; margin-top:0.25rem; font-style:italic;">
                        &ldquo;<?= htmlspecialchars($note) ?>&rdquo;
                    </div>
                <?php endif; ?>
                <?php if ($status === 'rejected' || $status === 'unsubmitted'): ?>
                    <form action="<?= BASE_URL ?>admin/clearances/submit-for-approval" method="POST" style="margin-top:0.45rem;">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                        <input type="hidden" name="clearance_id" value="<?= $cid ?>">
                        <input type="hidden" name="college" value="<?= htmlspecialchars($item['college']) ?>">
                        <button type="submit" class="btn btn-warning btn-sm" style="font-size:0.75rem; padding:2px 8px;">
                            <i class="bi bi-arrow-repeat me-1"></i> <?= $status === 'rejected' ? 'Resubmit' : 'Submit' ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ===== TABS ===== -->
<div class="tabs">
    <button class="tab-btn active" onclick="showTab('tab-sig', this)">
        Signatories (<?= count($assignedSignatories) ?>)
    </button>
    <button class="tab-btn" onclick="showTab('tab-adv', this)">
        Enrollment Committee (<?= count($assignedEnrollmentCommittees) ?>)
    </button>
    <button class="tab-btn" onclick="showTab('tab-stu', this)">
        Students (<?= count($students) ?>)
    </button>
</div>

<!-- ===== TAB: Signatories ===== -->
<div id="tab-sig" class="tab-content active">
    <div class="section-header">
        <h3>Assigned Signatories</h3>
        <button class="btn btn-primary btn-sm"
                onclick="document.getElementById('assignSigModal').style.display='flex'">
            + Assign Signatory
        </button>
    </div>
    <?php if (empty($assignedSignatories)): ?>
        <p class="text-muted">No signatories assigned yet.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>Name</th><th>Office</th><th>Email</th><th>Scope</th><th class="text-end" style="white-space: nowrap;"></th></tr></thead>
                <tbody>
                    <?php foreach ($assignedSignatories as $s): ?>
                        <tr>
                            <td data-label="Name"><?= htmlspecialchars($s['full_name']) ?></td>
                            <td data-label="Office"><?= htmlspecialchars($s['office']) ?></td>
                            <td data-label="Email"><?= htmlspecialchars($s['email']) ?></td>
                            <td data-label="Scope">
                                <?php if ($s['scope_type'] === 'college'): ?>
                                    College: <?= htmlspecialchars($s['scope_value']) ?>
                                <?php elseif ($s['scope_type'] === 'course'): ?>
                                    Program: <?= htmlspecialchars($s['scope_value']) ?>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:0.8rem;">All Students</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end" data-label="Action">
                                <div class="action-cell">
                                    <form action="<?= BASE_URL ?>admin/clearances/signatories/remove"
                                          method="POST" onsubmit="return confirmAction(this, 'Remove this signatory?', 'Remove', 'btn-danger')">
                                         <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                         <input type="hidden" name="clearance_id" value="<?= $cid ?>">
                                         <input type="hidden" name="signatory_id" value="<?= $s['id'] ?>">
                                         <input type="hidden" name="tab"          value="tab-sig">
                                         <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ===== TAB: Enrollment Committee ===== -->
<div id="tab-adv" class="tab-content" style="display:none">
    <div class="section-header">
        <h3>Assigned Enrollment Committee</h3>
        <button class="btn btn-primary btn-sm"
                onclick="document.getElementById('assignAdvModal').style.display='flex'">
            + Assign Member
        </button>
    </div>
    <?php if (empty($assignedEnrollmentCommittees)): ?>
        <p class="text-muted">No enrollment committee members assigned yet (optional).</p>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>Name</th><th>College</th><th>Email</th><th class="text-end" style="white-space: nowrap;"></th></tr></thead>
                <tbody>
                    <?php foreach ($assignedEnrollmentCommittees as $a): ?>
                        <tr>
                            <td data-label="Name"><?= htmlspecialchars($a['full_name']) ?></td>
                            <td data-label="College"><?= htmlspecialchars($a['department']) ?></td>
                            <td data-label="Email"><?= htmlspecialchars($a['email']) ?></td>
                            <td class="text-end" data-label="Action">
                                <div class="action-cell">
                                    <form action="<?= BASE_URL ?>admin/clearances/enrollment-committees/remove"
                                          method="POST" onsubmit="return confirmAction(this, 'Remove this member?', 'Remove', 'btn-danger')">
                                         <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                         <input type="hidden" name="clearance_id" value="<?= $cid ?>">
                                         <input type="hidden" name="enrollment_committee_id"   value="<?= $a['id'] ?>">
                                         <input type="hidden" name="tab"          value="tab-adv">
                                         <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ===== TAB: Students ===== -->
<div id="tab-stu" class="tab-content" style="display:none">
    <div class="section-header">
        <h3>Enrolled Students</h3>
        <?php if (($clearance['type'] ?? 'regular') === 'exit'): ?>
            <button class="btn btn-warning btn-sm"
                    onclick="document.getElementById('enrollFourthYearModal').style.display='flex'">
                Enroll 4th-Year Students
            </button>
        <?php else: ?>
            <button class="btn btn-primary btn-sm"
                    onclick="document.getElementById('enrollAllModal').style.display='flex'">
                Enroll All Active Students
            </button>
        <?php endif; ?>
    </div>
    <?php if (empty($students)): ?>
        <div class="empty-state" style="padding:2rem">
            <p>No students enrolled yet.</p>
            <?php if (($clearance['type'] ?? 'regular') === 'exit'): ?>
                <p class="text-muted" style="font-size:.85rem;margin-top:.5rem;">
                    Click <strong>Enroll 4th-Year Students</strong> to enroll all active graduating students into this exit clearance.
                </p>
            <?php else: ?>
                <p class="text-muted" style="font-size:.85rem;margin-top:.5rem;">
                    Click <strong>Enroll All Active Students</strong> to enroll everyone already in the database.<br>
                    To add new first-year students, go to <a href="<?= BASE_URL ?>admin/students">Students</a> and upload a CSV first.
                </p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="filter-bar mb-3" style="display:flex; gap:.5rem; flex-wrap:wrap; align-items:center;">
            <input type="text" id="admin-stu-search" class="form-control" placeholder="Search name / ID…" oninput="applyAdminDetailFilters()" style="max-width:200px;">
            
            <select id="admin-stu-college-filter" class="form-control" style="max-width:130px;" onchange="loadAdminDetailPrograms(this.value); applyAdminDetailFilters();">
                <option value="">All Colleges</option>
                <option value="CAS">CAS</option>
                <option value="CCI">CCI</option>
                <option value="CEA">CEA</option>
                <option value="COE">COE</option>
                <option value="CIT">CIT</option>
                <option value="CGBE">CGBE</option>
            </select>

            <select id="admin-stu-program-filter" class="form-control" style="max-width:150px;" onchange="applyAdminDetailFilters()">
                <option value="">All Programs</option>
            </select>

            <select id="admin-stu-status-filter" class="form-control" style="max-width:130px;" onchange="applyAdminDetailFilters()">
                <option value="all">All Statuses</option>
                <option value="pending">In Progress</option>
                <option value="cleared">Fully Cleared</option>
                <option value="flagged">Has Issue</option>
            </select>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>College</th>
                        <th>Program</th>
                        <th>Year / Section</th>
                        <th class="text-end" style="white-space: nowrap;"></th>
                    </tr>
                </thead>
                <tbody id="admin-detail-stu-tbody">
                    <?php foreach ($students as $s):
                        $totalSig = (int) $s['total_signatories'];
                        $cleared  = (int) $s['cleared_count'];
                        $flagged  = (int) $s['flagged_count'];
                        $pending  = (int) $s['pending_count'];

                        $stStatus = $flagged > 0 ? 'flagged' : ($cleared === $totalSig && $totalSig > 0 ? 'cleared' : 'pending');
                        if ($flagged > 0) {
                            $overallBadge = '<span class="badge badge-danger">Has Issue</span>';
                        } elseif ($totalSig > 0 && $cleared === $totalSig) {
                            $overallBadge = '<span class="badge badge-success">Fully Cleared</span>';
                        } else {
                            $overallBadge = '<span class="badge badge-warning">In Progress</span>';
                        }
                    ?>
                        <tr class="<?= $flagged > 0 ? 'row-flagged' : ($cleared === $totalSig && $totalSig > 0 ? 'row-cleared' : '') ?>"
                            data-student-id="<?= $s['id'] ?>"
                            data-name="<?= strtolower(htmlspecialchars($s['last_name'].' '.$s['first_name'])) ?>"
                            data-sid="<?= strtolower(htmlspecialchars($s['student_number'])) ?>"
                            data-college="<?= strtolower(htmlspecialchars($s['college'])) ?>"
                            data-course="<?= strtolower(htmlspecialchars($s['course'])) ?>"
                            data-status="<?= $stStatus ?>">
                            <td data-label="Student ID"><?= htmlspecialchars($s['student_number']) ?></td>
                            <td data-label="Last Name"><?= htmlspecialchars($s['last_name']) ?></td>
                            <td data-label="First Name"><?= htmlspecialchars($s['first_name']) ?></td>
                            <td data-label="College"><?= htmlspecialchars($s['college']) ?></td>
                            <td data-label="Program"><?= htmlspecialchars($s['course']) ?></td>
                            <td data-label="Year / Section"><?= $s['year_level'] ?> – <?= htmlspecialchars($s['section']) ?></td>
                            <td class="text-end" data-label="Action">
                                <div class="action-cell">
                                    <a href="<?= BASE_URL ?>admin/clearances/download-form?cid=<?= (int)$cid ?>&sid=<?= (int)$s['id'] ?>"
                                       class="btn btn-outline-primary btn-sm" title="Download Clearance Form PDF">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                    </a>
                                    <button type="button" class="btn btn-secondary btn-sm"
                                            onclick="openStudentLogs(<?= (int)$cid ?>, <?= (int)$s['id'] ?>, <?= htmlspecialchars(json_encode($s['last_name'] . ', ' . $s['first_name']), ENT_QUOTES) ?>)">
                                        Logs
                                    </button>
                                    <form action="<?= BASE_URL ?>admin/clearances/students/remove"
                                          method="POST" onsubmit="return confirmAction(this, 'Remove student from clearance?', 'Remove', 'btn-danger')">
                                         <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                         <input type="hidden" name="clearance_id" value="<?= $cid ?>">
                                         <input type="hidden" name="student_id"   value="<?= $s['id'] ?>">
                                         <input type="hidden" name="tab"          value="tab-stu">
                                         <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ====== Start Clearance Modal ====== -->
<?php if (empty($clearance['started_at'])): ?>
<div id="startClearanceModal" class="modal" style="display:none;">
    <div class="modal-box" style="max-width:440px;">
        <div class="modal-header">
            <h3>Start Clearance</h3>
            <button onclick="document.getElementById('startClearanceModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <div style="padding:1.25rem 1.5rem;">
            <p>This will email <strong><?= count($students) ?> enrolled student(s)</strong> that signing has started, with a link to their clearance form where they can follow their progress.</p>
            <p class="text-muted" style="font-size:.85rem;margin-top:.75rem;">This can only be done once per clearance.</p>
        </div>
        <form action="<?= BASE_URL ?>admin/clearances/start" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="clearance_id" value="<?= $cid ?>">
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('startClearanceModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Start &amp; Send Emails</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ====== Edit Clearance Modal ====== -->
<div id="editClearanceDetailModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Clearance Info</h3>
            <button onclick="document.getElementById('editClearanceDetailModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/clearances/edit" method="POST" class="modal-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="id" id="editDetailId">
            <div class="form-group">
                <label>Clearance Name <span class="required">*</span></label>
                <input type="text" name="name" id="editDetailName" required>
            </div>
            <div class="form-group">
                <label>Academic Year <span class="required">*</span></label>
                <input type="text" name="school_year" id="editDetailYear" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="editDetailDesc" rows="2"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('editClearanceDetailModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ====== Assign Signatory Modal ====== -->
<div id="assignSigModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Assign Signatory</h3>
            <button onclick="document.getElementById('assignSigModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/clearances/signatories/assign" method="POST" class="modal-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="clearance_id" value="<?= $cid ?>">
            <input type="hidden" name="tab"          value="tab-sig">
            <div class="form-group">
                <label>Select Signatory</label>
                <?php if (empty($unassignedSignatories)): ?>
                    <p class="text-muted" style="margin-top: 0;">No available signatories to assign. Please create more in the Signatories menu.</p>
                <?php else: ?>
                    <select name="signatory_id" required>
                        <option value="all" style="font-weight: bold; color: var(--primary);">-- Assign All Signatories --</option>
                        <?php foreach ($unassignedSignatories as $us): ?>
                            <option value="<?= $us['id'] ?>">
                                <?= htmlspecialchars($us['full_name']) ?> — <?= htmlspecialchars($us['office']) ?> (<?= ucfirst($us['account_status'] ?? 'active') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            


            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('assignSigModal').style.display='none'">Cancel</button>
                <?php if (!empty($unassignedSignatories)): ?>
                    <button type="submit" class="btn btn-primary">Assign</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- ====== Assign Enrollment Committee Modal ====== -->
<div id="assignAdvModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Assign Enrollment Committee Member</h3>
            <button onclick="document.getElementById('assignAdvModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/clearances/enrollment-committees/assign" method="POST" class="modal-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="clearance_id" value="<?= $cid ?>">
            <input type="hidden" name="tab"          value="tab-adv">
            <div class="form-group">
                <label>Select Member</label>
                <?php if (empty($unassignedEnrollmentCommittees)): ?>
                    <p class="text-muted" style="margin-top: 0;">No available enrollment committee members to assign. Please create more in the Enrollment Committee menu.</p>
                <?php else: ?>
                    <select name="enrollment_committee_id" required>
                        <option value="all" style="font-weight: bold; color: var(--primary);">-- Assign All Enrollment Committees --</option>
                        <?php foreach ($unassignedEnrollmentCommittees as $ua): ?>
                            <option value="<?= $ua['id'] ?>">
                                <?= htmlspecialchars($ua['full_name']) ?> — <?= htmlspecialchars($ua['department']) ?> (<?= ucfirst($ua['account_status'] ?? 'active') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('assignAdvModal').style.display='none'">Cancel</button>
                <?php if (!empty($unassignedEnrollmentCommittees)): ?>
                    <button type="submit" class="btn btn-primary">Assign</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- ====== Enroll 4th-Year Students Modal (Exit Clearances) ====== -->
<div id="enrollFourthYearModal" class="modal" style="display:none;">
    <div class="modal-box" style="max-width:440px;">
        <div class="modal-header">
            <h3>Enroll 4th-Year Students</h3>
            <button onclick="document.getElementById('enrollFourthYearModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <div style="padding:1.25rem 1.5rem;">
            <p>This will enroll <strong>all active 4th-year students</strong> from the database into this exit clearance.</p>
            <p class="text-muted" style="font-size:.85rem;margin-top:.75rem;">
                Only graduating (4th-year) students will be enrolled. Students already enrolled will be skipped automatically.
            </p>
        </div>
        <form action="<?= BASE_URL ?>admin/clearances/students/enroll-fourth-year" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="clearance_id" value="<?= $cid ?>">
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('enrollFourthYearModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-warning">Enroll 4th-Year Students</button>
            </div>
        </form>
    </div>
</div>

<!-- ====== Enroll All Active Students Modal ====== -->
<div id="enrollAllModal" class="modal" style="display:none;">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <h3>Enroll All Active Students</h3>
            <button onclick="document.getElementById('enrollAllModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <div style="padding:1.25rem 1.5rem;">
            <p>This will enroll <strong>all active students</strong> from the database into this clearance — students who are already enrolled will be skipped automatically.</p>
            <p class="text-muted" style="font-size:.85rem;margin-top:.75rem;">
                Graduated and dropped students will <strong>not</strong> be enrolled.
            </p>
        </div>
        <form action="<?= BASE_URL ?>admin/clearances/students/enroll-all" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="clearance_id" value="<?= $cid ?>">
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('enrollAllModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Enroll All Active Students</button>
            </div>
        </form>
    </div>
</div>

<?php $logsUrl = BASE_URL . 'admin/clearances/student-logs'; include ROOT_PATH . '/app/Views/layouts/student_logs_modal.php'; ?>

<script>
/* ---- Tab switching ---- */
function showTab(id, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(id).style.display = 'block';
    if (btn) {
        btn.classList.add('active');
    } else {
        const targetBtn = document.querySelector(`.tab-btn[onclick*="${id}"]`);
        if (targetBtn) targetBtn.classList.add('active');
    }
    history.replaceState(null, null, '#' + id);
}

document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById(hash)) {
        showTab(hash);
    }
});

/* ---- Edit clearance prefill ---- */
function openEditClearanceDetail(id, name, desc, year) {
    document.getElementById('editDetailId').value   = id;
    document.getElementById('editDetailName').value = name;
    document.getElementById('editDetailDesc').value = desc;
    document.getElementById('editDetailYear').value = year;
    document.getElementById('editClearanceDetailModal').style.display = 'flex';
}

function loadAdminDetailPrograms(college) {
    const sel = document.getElementById('admin-stu-program-filter');
    if (!sel) return;
    sel.innerHTML = '<option value="">All Programs</option>';
    if (!college) {
        applyAdminDetailFilters();
        return;
    }
    fetch('<?= BASE_URL ?>api/programs?college=' + encodeURIComponent(college))
        .then(r => r.json())
        .then(data => {
            data.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.code.toLowerCase();
                opt.textContent = p.code;
                sel.appendChild(opt);
            });
            applyAdminDetailFilters();
        })
        .catch(() => applyAdminDetailFilters());
}

function applyAdminDetailFilters() {
    const search  = (document.getElementById('admin-stu-search')?.value || '').toLowerCase().trim();
    const college = (document.getElementById('admin-stu-college-filter')?.value || '').toLowerCase();
    const program = (document.getElementById('admin-stu-program-filter')?.value || '').toLowerCase();
    const status  = document.getElementById('admin-stu-status-filter')?.value || 'all';

    document.querySelectorAll('#admin-detail-stu-tbody tr[data-student-id]').forEach(row => {
        const name    = (row.dataset.name    || '').toLowerCase();
        const sid     = (row.dataset.sid     || '').toLowerCase();
        const col     = (row.dataset.college || '').toLowerCase();
        const prog    = (row.dataset.course  || '').toLowerCase();
        const st      = (row.dataset.status  || '');

        const show = (!search  || name.includes(search) || sid.includes(search))
                  && (!college || col   === college)
                  && (!program || prog  === program)
                  && (status === 'all' || st === status);

        row.style.display = show ? '' : 'none';
    });
}


</script>

