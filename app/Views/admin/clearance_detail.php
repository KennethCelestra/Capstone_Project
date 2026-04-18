<?php
$cid = $clearance['id'];
?>
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
    <button class="btn btn-secondary"
            onclick="openEditClearanceDetail(<?= $cid ?>, '<?= htmlspecialchars(addslashes($clearance['name'])) ?>', '<?= htmlspecialchars(addslashes($clearance['description'])) ?>', '<?= htmlspecialchars($clearance['school_year']) ?>')">
        ✏️ Edit Info
    </button>
</div>

<!-- ===== TABS ===== -->
<div class="tabs">
    <button class="tab-btn active" onclick="showTab('tab-sig', this)">✍️ Signatories (<?= count($assignedSignatories) ?>)</button>
    <button class="tab-btn"        onclick="showTab('tab-adv', this)">🎓 Enrollment Committee (<?= count($assignedAdvisers) ?>)</button>
    <button class="tab-btn"        onclick="showTab('tab-stu', this)">👥 Students (<?= count($students) ?>)</button>
</div>

<!-- ===== TAB: Signatories ===== -->
<div id="tab-sig" class="tab-content active">
    <div class="section-header">
        <h3>Assigned Signatories</h3>
        <?php if (!empty($unassignedSignatories)): ?>
            <button class="btn btn-primary btn-sm" onclick="document.getElementById('assignSigModal').style.display='flex'">
                + Assign Signatory
            </button>
        <?php else: ?>
            <span class="text-muted" style="font-size:.85rem">All signatories are assigned</span>
        <?php endif; ?>
    </div>

    <?php if (empty($assignedSignatories)): ?>
        <p class="text-muted">No signatories assigned yet.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr><th>Name</th><th>Office</th><th>Email</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($assignedSignatories as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['full_name']) ?></td>
                            <td><?= htmlspecialchars($s['office']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td>
                                <form action="<?= BASE_URL ?>admin/clearances/signatories/remove" method="POST"
                                      onsubmit="return confirm('Remove this signatory from the clearance?')">
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
        <h3>Assigned Enrollment Committee Members</h3>
        <?php if (!empty($unassignedAdvisers)): ?>
            <button class="btn btn-primary btn-sm" onclick="document.getElementById('assignAdvModal').style.display='flex'">
                + Assign Member
            </button>
        <?php else: ?>
            <span class="text-muted" style="font-size:.85rem">All enrollment committee members are assigned</span>
        <?php endif; ?>
    </div>

    <?php if (empty($assignedAdvisers)): ?>
        <p class="text-muted">No enrollment committee members assigned yet.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr><th>Name</th><th>Department</th><th>Email</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($assignedAdvisers as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['full_name']) ?></td>
                            <td><?= htmlspecialchars($a['department']) ?></td>
                            <td><?= htmlspecialchars($a['email']) ?></td>
                            <td>
                                <form action="<?= BASE_URL ?>admin/clearances/advisers/remove" method="POST"
                                      onsubmit="return confirm('Remove this enrollment committee member from the clearance?')">
                                    <input type="hidden" name="clearance_id" value="<?= $cid ?>">
                                    <input type="hidden" name="adviser_id"   value="<?= $a['id'] ?>">
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
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <button class="btn btn-primary btn-sm" onclick="document.getElementById('uploadCSVModal').style.display='flex'">
                📤 Upload CSV
            </button>
            <form action="<?= BASE_URL ?>admin/clearances/students/dummies" method="POST" style="display:inline">
                <input type="hidden" name="clearance_id" value="<?= $cid ?>">
                <button type="submit" class="btn btn-secondary btn-sm"
                        onclick="return confirm('Insert 10 dummy students and enroll them in this clearance?')">
                    🧪 Insert Dummies
                </button>
            </form>
        </div>
    </div>

    <?php if (empty($students)): ?>
        <div class="empty-state" style="padding:2rem">
            <div class="empty-icon">👥</div>
            <p>No students enrolled yet. Upload a CSV or insert dummy data.</p>
            <p class="text-muted" style="font-size:.8rem">
                CSV format: <code>student_id, full_name, email, course, year_level, section</code>
            </p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Course</th>
                        <th>Year / Section</th>
                        <th>Clearance Progress</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s):
                        $total  = (int) $s['total_count'];
                        $signed = (int) $s['signed_count'];
                        $pct    = $total > 0 ? round(($signed / $total) * 100) : 0;
                        $isCleared = ($total > 0 && $signed === $total);
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($s['student_number']) ?></td>
                            <td><?= htmlspecialchars($s['full_name']) ?></td>
                            <td><?= htmlspecialchars($s['course']) ?></td>
                            <td><?= $s['year_level'] ?> – <?= htmlspecialchars($s['section']) ?></td>
                            <td>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar" style="width:<?= $pct ?>%"></div>
                                </div>
                                <small><?= $signed ?>/<?= $total ?> signed</small>
                            </td>
                            <td>
                                <span class="badge <?= $isCleared ? 'badge-success' : 'badge-warning' ?>">
                                    <?= $isCleared ? 'Cleared' : 'Pending' ?>
                                </span>
                            </td>
                            <td>
                                <form action="<?= BASE_URL ?>admin/clearances/students/remove" method="POST"
                                      onsubmit="return confirm('Remove this student from the clearance?')">
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
                <select name="signatory_id" required>
                    <option value="">-- Choose Signatory --</option>
                    <?php foreach ($unassignedSignatories as $us): ?>
                        <option value="<?= $us['id'] ?>">
                            <?= htmlspecialchars($us['full_name']) ?> — <?= htmlspecialchars($us['office']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('assignSigModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign</button>
            </div>
        </form>
    </div>
</div>

<!-- ====== Assign Adviser Modal ====== -->
<div id="assignAdvModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Assign Enrollment Committee Member</h3>
            <button onclick="document.getElementById('assignAdvModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/clearances/advisers/assign" method="POST" class="modal-form">
            <input type="hidden" name="clearance_id" value="<?= $cid ?>">
            <div class="form-group">
                <label>Select Enrollment Committee Member</label>
                <select name="adviser_id" required>
                    <option value="">-- Choose Member --</option>
                    <?php foreach ($unassignedAdvisers as $ua): ?>
                        <option value="<?= $ua['id'] ?>">
                            <?= htmlspecialchars($ua['full_name']) ?> — <?= htmlspecialchars($ua['department']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('assignAdvModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign</button>
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
                    Required columns: <code>student_id, full_name, email, course, year_level, section</code><br>
                    First row must be the header. Default password = student ID.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('uploadCSVModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Upload & Enroll</button>
            </div>
        </form>
    </div>
</div>

<script>
function showTab(id, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(id).style.display = 'block';
    btn.classList.add('active');
}
function openEditClearanceDetail(id, name, desc, year) {
    document.getElementById('editDetailId').value   = id;
    document.getElementById('editDetailName').value = name;
    document.getElementById('editDetailDesc').value = desc;
    document.getElementById('editDetailYear').value = year;
    document.getElementById('editClearanceDetailModal').style.display = 'flex';
}
</script>
