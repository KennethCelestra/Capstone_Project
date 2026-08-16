<?php
$totalCount     = count($students);
$activeCount    = count(array_filter($students, fn($s) => $s['status'] === 'active'));
$graduatedCount = count(array_filter($students, fn($s) => $s['status'] === 'graduated'));
$droppedCount   = count(array_filter($students, fn($s) => $s['status'] === 'dropped'));
?>

<div class="page-header mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2>Students</h2>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.35rem;">
            <span class="badge badge-info" style="font-size:.78rem;">Total: <?= $totalCount ?></span>
            <span class="badge badge-success" style="font-size:.78rem;">Active: <?= $activeCount ?></span>
            <?php if ($graduatedCount > 0): ?>
                <span class="badge badge-info" style="font-size:.78rem;">Graduated: <?= $graduatedCount ?></span>
            <?php endif; ?>
            <?php if ($droppedCount > 0): ?>
                <span class="badge badge-danger" style="font-size:.78rem;">Dropped: <?= $droppedCount ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
        <button class="btn btn-secondary"
                onclick="document.getElementById('promoteModal').style.display='flex'">
            Promote
        </button>
        <button class="btn btn-secondary"
                onclick="document.getElementById('uploadCSVModal').style.display='flex'">
            Upload CSV
        </button>
        <button class="btn btn-primary" onclick="document.getElementById('addStudentModal').style.display='flex'">
            + Add Student
        </button>
    </div>
</div>

<!-- Filter Bar -->
<div style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;margin-bottom:1rem;">
    <input type="text" id="stu-search"
           placeholder="Search name or ID..."
           style="flex:1;min-width:180px;max-width:280px;"
           oninput="applyStudentFilters()">
    <select id="stu-college" onchange="applyStudentFilters()" style="width:auto;max-width:130px;">
        <option value="">College</option>
        <?php foreach ($colleges as $col): ?>
            <option value="<?= htmlspecialchars($col) ?>"><?= htmlspecialchars($col) ?></option>
        <?php endforeach; ?>
    </select>
    <select id="stu-year" onchange="applyStudentFilters()" style="width:auto;max-width:110px;">
        <option value="">Year</option>
        <option value="1">1st Year</option>
        <option value="2">2nd Year</option>
        <option value="3">3rd Year</option>
        <option value="4">4th Year</option>
    </select>
    <select id="stu-status" onchange="applyStudentFilters()" style="width:auto;max-width:110px;">
        <option value="">Status</option>
        <option value="active">Active</option>
        <option value="graduated">Graduated</option>
        <option value="dropped">Dropped</option>
    </select>
    <button class="btn btn-secondary btn-sm" onclick="clearStudentFilters()" id="stu-clear-btn" style="display:none;">✕ Clear</button>
</div>

<div class="table-container">
    <table class="data-table" id="stu-table">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>College</th>
                <th>Course</th>
                <th>Year / Section</th>
                <th>Status</th>
                <th class="text-end" style="white-space: nowrap;">Action</th>
            </tr>
        </thead>
        <tbody id="stu-tbody">
            <?php if (empty($students)): ?>
                <tr><td colspan="8" class="text-center">No students found. Add manually or upload a CSV.</td></tr>
            <?php else: ?>
                <?php foreach ($students as $s): ?>
                    <?php
                        $statusBadge = match($s['status']) {
                            'active'    => '<span class="badge badge-success">Active</span>',
                            'graduated' => '<span class="badge badge-info">Graduated</span>',
                            'dropped'   => '<span class="badge badge-danger">Dropped</span>',
                            default     => '<span class="badge badge-warning">' . htmlspecialchars($s['status']) . '</span>',
                        };
                    ?>
                    <tr data-name="<?= strtolower(htmlspecialchars($s['last_name'] . ' ' . $s['first_name'])) ?>"
                        data-id="<?= strtolower(htmlspecialchars($s['student_id'])) ?>"
                        data-college="<?= htmlspecialchars($s['college']) ?>"
                        data-year="<?= (int)$s['year_level'] ?>"
                        data-status="<?= htmlspecialchars($s['status']) ?>">
                        <td data-label="Student ID"><?= htmlspecialchars($s['student_id']) ?></td>
                        <td data-label="Last Name"><?= htmlspecialchars($s['last_name']) ?></td>
                        <td data-label="First Name"><?= htmlspecialchars($s['first_name']) ?></td>
                        <td data-label="College"><?= htmlspecialchars($s['college']) ?></td>
                        <td data-label="Course"><?= htmlspecialchars($s['course']) ?></td>
                        <td data-label="Year / Section"><?= htmlspecialchars($s['year_level']) ?> – <?= htmlspecialchars($s['section']) ?></td>
                        <td data-label="Status"><?= $statusBadge ?></td>
                        <td class="text-end" data-label="Action">
                            <div class="action-cell">
                                <button class="btn btn-secondary btn-sm"
                                        onclick="openEditStudentModal(
                                            <?= (int)$s['id'] ?>,
                                            '<?= htmlspecialchars(addslashes($s['student_id'])) ?>',
                                            '<?= htmlspecialchars(addslashes($s['last_name'])) ?>',
                                            '<?= htmlspecialchars(addslashes($s['first_name'])) ?>',
                                            '<?= htmlspecialchars(addslashes($s['email'])) ?>',
                                            '<?= htmlspecialchars(addslashes($s['college'])) ?>',
                                            '<?= htmlspecialchars(addslashes($s['course'])) ?>',
                                            <?= (int)$s['year_level'] ?>,
                                            '<?= htmlspecialchars(addslashes($s['section'])) ?>',
                                            '<?= htmlspecialchars($s['status']) ?>'
                                        )">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <form action="<?= BASE_URL ?>admin/students/delete" method="POST" style="margin:0;"
                                      onsubmit="return confirmAction(this, 'Delete this student from the database? This cannot be undone.', 'Delete', 'btn-danger')">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr id="stu-no-match" style="display:none;">
                    <td colspan="8" class="text-center text-muted">No students match the current filters.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ====== Upload CSV Modal ====== -->
<div id="uploadCSVModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Upload New Students (CSV)</h3>
            <button onclick="document.getElementById('uploadCSVModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/students/upload" method="POST"
              enctype="multipart/form-data" class="modal-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <div class="info-box" style="margin-bottom:1rem;">
                <strong>For new first-year students only.</strong> Existing students (matched by Student ID or email) will be skipped automatically.
            </div>
            <div class="form-group">
                <label>CSV File</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required>
                <small class="text-muted">
                    Required columns: <code>student_id, last_name, first_name, email, college, course, year_level, section</code><br>
                    First row must be the header row.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('uploadCSVModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Upload Students</button>
            </div>
        </form>
    </div>
</div>

<!-- ====== Promote Students Confirmation Modal ====== -->
<div id="promoteModal" class="modal" style="display:none;">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-header">
            <h3>Promote Students — Year-End</h3>
            <button onclick="document.getElementById('promoteModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <div style="padding:1.25rem 1.5rem;">
            <div class="info-box" style="background:rgba(var(--warning-rgb,255,193,7),.12);border-color:#f59e0b;margin-bottom:1rem;">
                <strong>⚠ This action is irreversible.</strong> Run this only once at the end of each school year.
            </div>
            <p>The following will happen to <strong>all active students</strong>:</p>
            <ul style="margin:.75rem 0 .75rem 1.25rem;line-height:2;">
                <li>1st-year → 2nd-year</li>
                <li>2nd-year → 3rd-year</li>
                <li>3rd-year → 4th-year</li>
                <li>4th-year → <strong>Graduated</strong></li>
            </ul>
            <p class="text-muted" style="font-size:.85rem;">After promotion, upload a CSV to add new first-year students.</p>
        </div>
        <form action="<?= BASE_URL ?>admin/students/promote" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('promoteModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-danger">Yes, Promote All Students</button>
            </div>
        </form>
    </div>
</div>

<!-- ====== Add Student Modal ====== -->
<div id="addStudentModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add New Student</h3>
            <button onclick="document.getElementById('addStudentModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/students/add" method="POST" class="modal-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Student ID *</label>
                    <input type="text" name="student_id" required placeholder="e.g. 2024-00001">
                </div>
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" required placeholder="Dela Cruz">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" required placeholder="Juan">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required placeholder="student@school.edu">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>College *</label>
                    <select name="college" required>
                        <?php foreach ($colleges as $col): ?>
                            <option value="<?= htmlspecialchars($col) ?>"><?= htmlspecialchars($col) ?></option>
                        <?php endforeach; ?>
                        <?php if (empty($colleges)): ?>
                            <option value="CAS">CAS</option>
                            <option value="CEA">CEA</option>
                            <option value="COE">COE</option>
                            <option value="CCI">CCI</option>
                            <option value="CIT">CIT</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Course *</label>
                    <input type="text" name="course" required placeholder="BSIT">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Year Level *</label>
                    <select name="year_level" required>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Section *</label>
                    <input type="text" name="section" required placeholder="A">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('addStudentModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Student</button>
            </div>
        </form>
    </div>
</div>

<!-- ====== Edit Student Modal ====== -->
<div id="editStudentModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Student</h3>
            <button onclick="document.getElementById('editStudentModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/students/update" method="POST" class="modal-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="id" id="editStudentId">
            <div class="form-row">
                <div class="form-group">
                    <label>Student ID *</label>
                    <input type="text" name="student_id" id="editStudentStudentId" required>
                </div>
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" id="editStudentLastName" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" id="editStudentFirstName" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" id="editStudentEmail" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>College *</label>
                    <select name="college" id="editStudentCollege" required>
                        <?php foreach ($colleges as $col): ?>
                            <option value="<?= htmlspecialchars($col) ?>"><?= htmlspecialchars($col) ?></option>
                        <?php endforeach; ?>
                        <?php if (empty($colleges)): ?>
                            <option value="CAS">CAS</option>
                            <option value="CEA">CEA</option>
                            <option value="COE">COE</option>
                            <option value="CCI">CCI</option>
                            <option value="CIT">CIT</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Course *</label>
                    <input type="text" name="course" id="editStudentCourse" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Year Level *</label>
                    <select name="year_level" id="editStudentYearLevel" required>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Section *</label>
                    <input type="text" name="section" id="editStudentSection" required>
                </div>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" id="editStudentStatus">
                    <option value="active">Active</option>
                    <option value="graduated">Graduated</option>
                    <option value="dropped">Dropped</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('editStudentModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditStudentModal(id, studentId, lastName, firstName, email, college, course, yearLevel, section, status) {
    document.getElementById('editStudentId').value         = id;
    document.getElementById('editStudentStudentId').value  = studentId;
    document.getElementById('editStudentLastName').value   = lastName;
    document.getElementById('editStudentFirstName').value  = firstName;
    document.getElementById('editStudentEmail').value      = email;
    document.getElementById('editStudentCourse').value     = course;
    document.getElementById('editStudentSection').value    = section;
    document.getElementById('editStudentStatus').value     = status;

    const collegeSelect    = document.getElementById('editStudentCollege');
    const yearLevelSelect  = document.getElementById('editStudentYearLevel');
    collegeSelect.value   = college;
    yearLevelSelect.value = yearLevel;

    document.getElementById('editStudentModal').style.display = 'flex';
}

function applyStudentFilters() {
    const search  = document.getElementById('stu-search').value.trim().toLowerCase();
    const college = document.getElementById('stu-college').value;
    const year    = document.getElementById('stu-year').value;
    const status  = document.getElementById('stu-status').value;

    const rows     = document.querySelectorAll('#stu-tbody tr[data-name]');
    const noMatch  = document.getElementById('stu-no-match');
    const clearBtn = document.getElementById('stu-clear-btn');

    const hasFilter = search || college || year || status;
    clearBtn.style.display = hasFilter ? 'inline-flex' : 'none';

    let visible = 0;
    rows.forEach(row => {
        const nameMatch    = !search  || row.dataset.name.includes(search) || row.dataset.id.includes(search);
        const collegeMatch = !college || row.dataset.college === college;
        const yearMatch    = !year    || row.dataset.year    === year;
        const statusMatch  = !status  || row.dataset.status  === status;

        const show = nameMatch && collegeMatch && yearMatch && statusMatch;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    if (noMatch) noMatch.style.display = (visible === 0 && rows.length > 0) ? '' : 'none';
}

function clearStudentFilters() {
    document.getElementById('stu-search').value  = '';
    document.getElementById('stu-college').value = '';
    document.getElementById('stu-year').value    = '';
    document.getElementById('stu-status').value  = '';
    applyStudentFilters();
}
</script>
