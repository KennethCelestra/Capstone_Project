<?php
$cid = $clearance['id'];
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
            Edit Info
        </button>
    </div>
</div>

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
                <thead><tr><th>Name</th><th>Office</th><th>Email</th><th>Scope</th><th class="text-end" style="white-space: nowrap;">Action</th></tr></thead>
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
                                    Course: <?= htmlspecialchars($s['scope_value']) ?>
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
                <thead><tr><th>Name</th><th>Department</th><th>Email</th><th class="text-end" style="white-space: nowrap;">Action</th></tr></thead>
                <tbody>
                    <?php foreach ($assignedEnrollmentCommittees as $a): ?>
                        <tr>
                            <td data-label="Name"><?= htmlspecialchars($a['full_name']) ?></td>
                            <td data-label="Department"><?= htmlspecialchars($a['department']) ?></td>
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
        <button class="btn btn-primary btn-sm"
                onclick="document.getElementById('uploadCSVModal').style.display='flex'">
            Upload CSV
        </button>
    </div>
    <?php if (empty($students)): ?>
        <div class="empty-state" style="padding:2rem">
            <p>No students enrolled yet. Please upload a CSV file to enroll students.</p>
            <p class="text-muted" style="font-size:.8rem">
                CSV format: <code>student_id, last_name, first_name, email, college, course, year_level, section</code>
            </p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>College</th>
                        <th>Course</th>
                        <th>Year / Section</th>
                        <th class="text-end" style="white-space: nowrap;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s):
                        $totalSig = (int) $s['total_signatories'];
                        $cleared  = (int) $s['cleared_count'];
                        $flagged  = (int) $s['flagged_count'];
                        $pending  = (int) $s['pending_count'];

                        if ($flagged > 0) {
                            $overallBadge = '<span class="badge badge-danger">Has Deficiency</span>';
                        } elseif ($totalSig > 0 && $cleared === $totalSig) {
                            $overallBadge = '<span class="badge badge-success">Fully Cleared</span>';
                        } else {
                            $overallBadge = '<span class="badge badge-warning">In Progress</span>';
                        }
                    ?>
                        <tr class="<?= $flagged > 0 ? 'row-flagged' : ($cleared === $totalSig && $totalSig > 0 ? 'row-cleared' : '') ?>">
                            <td data-label="Student ID"><?= htmlspecialchars($s['student_number']) ?></td>
                            <td data-label="Last Name"><?= htmlspecialchars($s['last_name']) ?></td>
                            <td data-label="First Name"><?= htmlspecialchars($s['first_name']) ?></td>
                            <td data-label="College"><?= htmlspecialchars($s['college']) ?></td>
                            <td data-label="Course"><?= htmlspecialchars($s['course']) ?></td>
                            <td data-label="Year / Section"><?= $s['year_level'] ?> – <?= htmlspecialchars($s['section']) ?></td>
                            <td class="text-end" data-label="Action">
                                <div class="action-cell">
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
                <label>Clearance Name *</label>
                <input type="text" name="name" id="editDetailName" required>
            </div>
            <div class="form-group">
                <label>School Year</label>
                <input type="text" name="school_year" id="editDetailYear">
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
                        <option value="">-- Choose Signatory --</option>
                        <option value="all" style="font-weight: bold; color: var(--primary);">-- Assign All Signatories --</option>
                        <?php foreach ($unassignedSignatories as $us): ?>
                            <option value="<?= $us['id'] ?>">
                                <?= htmlspecialchars($us['full_name']) ?> — <?= htmlspecialchars($us['office']) ?>
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
                        <option value="">-- Choose Member --</option>
                        <option value="all" style="font-weight: bold; color: var(--primary);">-- Assign All Enrollment Committees --</option>
                        <?php foreach ($unassignedEnrollmentCommittees as $ua): ?>
                            <option value="<?= $ua['id'] ?>">
                                <?= htmlspecialchars($ua['full_name']) ?> — <?= htmlspecialchars($ua['department']) ?>
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

<!-- ====== Upload CSV Modal ====== -->
<div id="uploadCSVModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Upload Student CSV</h3>
            <button onclick="document.getElementById('uploadCSVModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/clearances/students/upload" method="POST"
              enctype="multipart/form-data" class="modal-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="clearance_id" value="<?= $cid ?>">
            <input type="hidden" name="tab"          value="tab-stu">
            <div class="form-group">
                <label>CSV File</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required>
                <small class="text-muted">
                    Required columns: <code>student_id, last_name, first_name, email, college, course, year_level, section</code><br>
                    First row = header. Default password = student ID.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('uploadCSVModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Upload &amp; Enroll</button>
            </div>
        </form>
    </div>
</div>

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


</script>
