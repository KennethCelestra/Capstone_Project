<div class="page-header">
    <h2>Students</h2>
    <button class="btn btn-primary" onclick="document.getElementById('addStudentModal').style.display='flex'">
        + Add Student
    </button>
</div>

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
            <?php if (empty($students)): ?>
                <tr><td colspan="7" class="text-center">No students found. Add manually or upload via a clearance.</td></tr>
            <?php else: ?>
                <?php foreach ($students as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['student_id']) ?></td>
                        <td><?= htmlspecialchars($s['last_name']) ?></td>
                        <td><?= htmlspecialchars($s['first_name']) ?></td>
                        <td><?= htmlspecialchars($s['college']) ?></td>
                        <td><?= htmlspecialchars($s['course']) ?></td>
                        <td><?= htmlspecialchars($s['year_level']) ?> – <?= htmlspecialchars($s['section']) ?></td>
                        <td>
                            <form action="<?= BASE_URL ?>admin/students/delete" method="POST" style="display:inline"
                                  onsubmit="return confirm('Delete this student?')">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="info-box" style="margin-top:1.5rem">
    <strong>Tip:</strong> To enroll students into a specific clearance (and generate their clearance status),
    go to <a href="<?= BASE_URL ?>admin/clearances">Clearances</a> → Manage → Students tab and upload a CSV file.
</div>

<!-- ====== Add Student Modal ====== -->
<div id="addStudentModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add New Student</h3>
            <button onclick="document.getElementById('addStudentModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/students/add" method="POST" class="modal-form">
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
                        <option value="CAS">CAS</option>
                        <option value="CEA">CEA</option>
                        <option value="COE">COE</option>
                        <option value="CCI">CCI</option>
                        <option value="CIT">CIT</option>
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
            <div class="form-group">
                <label>Default Password *</label>
                <input type="text" name="password" required placeholder="Password">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('addStudentModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Student</button>
            </div>
        </form>
    </div>
</div>
