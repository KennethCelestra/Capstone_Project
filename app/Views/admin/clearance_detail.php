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
                <thead><tr><th>Name</th><th>Office</th><th>Email</th><th>Scope</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($assignedSignatories as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['full_name']) ?></td>
                            <td><?= htmlspecialchars($s['office']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td>
                                <?php if ($s['scope_type'] === 'college'): ?>
                                    <span class="badge" style="background:var(--primary);color:#fff;">College: <?= htmlspecialchars($s['scope_value']) ?></span>
                                <?php elseif ($s['scope_type'] === 'course'): ?>
                                    <span class="badge" style="background:var(--secondary);color:#fff;">Course: <?= htmlspecialchars($s['scope_value']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:0.8rem;">All Students</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="<?= BASE_URL ?>admin/clearances/signatories/remove"
                                      method="POST" onsubmit="return confirmAction(this, 'Remove this signatory?', 'Remove', 'btn-danger')">
                                    <input type="hidden" name="clearance_id" value="<?= $cid ?>">
                                    <input type="hidden" name="signatory_id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
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
                <thead><tr><th>Name</th><th>Department</th><th>Email</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($assignedEnrollmentCommittees as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['full_name']) ?></td>
                            <td><?= htmlspecialchars($a['department']) ?></td>
                            <td><?= htmlspecialchars($a['email']) ?></td>
                            <td>
                                <form action="<?= BASE_URL ?>admin/clearances/enrollment-committees/remove"
                                      method="POST" onsubmit="return confirmAction(this, 'Remove this member?', 'Remove', 'btn-danger')">
                                    <input type="hidden" name="clearance_id" value="<?= $cid ?>">
                                    <input type="hidden" name="enrollment_committee_id"   value="<?= $a['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
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
                        <th>Action</th>
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
                            <td><?= htmlspecialchars($s['student_number']) ?></td>
                            <td><?= htmlspecialchars($s['last_name']) ?></td>
                            <td><?= htmlspecialchars($s['first_name']) ?></td>
                            <td><?= htmlspecialchars($s['college']) ?></td>
                            <td><?= htmlspecialchars($s['course']) ?></td>
                            <td><?= $s['year_level'] ?> – <?= htmlspecialchars($s['section']) ?></td>
                            <td>
                                <form action="<?= BASE_URL ?>admin/clearances/students/remove"
                                      method="POST" onsubmit="return confirmAction(this, 'Remove student from clearance?', 'Remove', 'btn-danger')">
                                    <input type="hidden" name="clearance_id" value="<?= $cid ?>">
                                    <input type="hidden" name="student_id"   value="<?= $s['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
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
            <input type="hidden" name="clearance_id" value="<?= $cid ?>">
            <div class="form-group">
                <label>Select Signatory</label>
                <?php if (empty($unassignedSignatories)): ?>
                    <p class="text-muted" style="margin-top: 0;">No available signatories to assign. Please create more in the Signatories menu.</p>
                <?php else: ?>
                    <select name="signatory_id" required>
                        <option value="">-- Choose Signatory --</option>
                        <?php foreach ($unassignedSignatories as $us): ?>
                            <option value="<?= $us['id'] ?>">
                                <?= htmlspecialchars($us['full_name']) ?> — <?= htmlspecialchars($us['office']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            
            <div class="form-group" style="margin-top: 1rem;">
                <label>Student Scope <small class="text-muted">(Which students must this signatory clear?)</small></label>

                <!-- Scope option cards -->
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:8px; margin-top:8px;">

                    <label style="display:flex; flex-direction:column; align-items:center; gap:6px; padding:12px 8px; background:var(--surface2); border:2px solid var(--border-color); border-radius:8px; cursor:pointer; text-align:center; transition:border-color .15s;" id="scope-card-global">
                        <input type="radio" name="scope_type" value="" checked onchange="toggleScopeInputs(this.value)" style="margin:0; accent-color:var(--primary);">
                        <i class="bi bi-globe2" style="font-size:1.2rem; color:var(--primary);"></i>
                        <span style="font-weight:600; font-size:0.85rem; line-height:1.2;">Global<br><small style="font-weight:400; color:var(--text-muted);">All Students</small></span>
                    </label>

                    <label style="display:flex; flex-direction:column; align-items:center; gap:6px; padding:12px 8px; background:var(--surface2); border:2px solid var(--border-color); border-radius:8px; cursor:pointer; text-align:center; transition:border-color .15s;" id="scope-card-college">
                        <input type="radio" name="scope_type" value="college" onchange="toggleScopeInputs(this.value)" style="margin:0; accent-color:#0ea5e9;">
                        <i class="bi bi-building" style="font-size:1.2rem; color:#0ea5e9;"></i>
                        <span style="font-weight:600; font-size:0.85rem; line-height:1.2;">By College<br><small style="font-weight:400; color:var(--text-muted);">e.g. Dean</small></span>
                    </label>

                    <label style="display:flex; flex-direction:column; align-items:center; gap:6px; padding:12px 8px; background:var(--surface2); border:2px solid var(--border-color); border-radius:8px; cursor:pointer; text-align:center; transition:border-color .15s;" id="scope-card-course">
                        <input type="radio" name="scope_type" value="course" onchange="toggleScopeInputs(this.value)" style="margin:0; accent-color:#22c55e;">
                        <i class="bi bi-journal-bookmark" style="font-size:1.2rem; color:#22c55e;"></i>
                        <span style="font-weight:600; font-size:0.85rem; line-height:1.2;">By Course<br><small style="font-weight:400; color:var(--text-muted);">e.g. Dept. Head</small></span>
                    </label>

                </div>

                <!-- College input -->
                <div id="scope_college_container" style="display:none; margin-top:10px;">
                    <input type="text" name="scope_college" id="scope_college_input" list="college_list"
                           placeholder="Type or select a college (e.g. CCI)"
                           style="width:100%;">
                    <datalist id="college_list">
                        <?php foreach (['CAS','CCI','CEA','CIT','COE'] as $col): ?>
                            <option value="<?= $col ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <!-- Course input -->
                <div id="scope_course_container" style="display:none; margin-top:10px;">
                    <input type="text" name="scope_course" id="scope_course_input" list="course_list"
                           placeholder="Type or select a course (e.g. BSIT)"
                           style="width:100%;">
                    <datalist id="course_list">
                        <?php foreach (['BSAMT','BSArchi','BSCE','BSCS','BSECE','BSEE','BSFT','BSIS','BSIT','BSME'] as $cur): ?>
                            <option value="<?= $cur ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>

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
            <input type="hidden" name="clearance_id" value="<?= $cid ?>">
            <div class="form-group">
                <label>Select Member</label>
                <?php if (empty($unassignedEnrollmentCommittees)): ?>
                    <p class="text-muted" style="margin-top: 0;">No available enrollment committee members to assign. Please create more in the Enrollment Committee menu.</p>
                <?php else: ?>
                    <select name="enrollment_committee_id" required>
                        <option value="">-- Choose Member --</option>
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
            <input type="hidden" name="clearance_id" value="<?= $cid ?>">
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
    btn.classList.add('active');
}

/* ---- Edit clearance prefill ---- */
function openEditClearanceDetail(id, name, desc, year) {
    document.getElementById('editDetailId').value   = id;
    document.getElementById('editDetailName').value = name;
    document.getElementById('editDetailDesc').value = desc;
    document.getElementById('editDetailYear').value = year;
    document.getElementById('editClearanceDetailModal').style.display = 'flex';
}

/* ---- Scope Input Toggle ---- */
function toggleScopeInputs(val) {
    var colContainer = document.getElementById('scope_college_container');
    var colInput = document.getElementById('scope_college_input');
    var curContainer = document.getElementById('scope_course_container');
    var curInput = document.getElementById('scope_course_input');
    
    colContainer.style.display = 'none';
    colInput.required = false;
    colInput.value = ''; // Reset when hidden
    
    curContainer.style.display = 'none';
    curInput.required = false;
    curInput.value = ''; // Reset when hidden
    
    if (val === 'college') {
        colContainer.style.display = 'block';
        colInput.required = true;
        colInput.focus();
    } else if (val === 'course') {
        curContainer.style.display = 'block';
        curInput.required = true;
        curInput.focus();
    }
}
</script>
