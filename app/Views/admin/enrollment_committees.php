<div class="page-header mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2>Manage Enrollment Committee</h2>
        <p class="text-muted">Add or edit enrollment committee members.</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addEnrollmentCommitteeModal').style.display='flex'">
        <i class="bi bi-person-plus"></i> Add Member
    </button>
</div>

<div class="gold-card" style="background:#fff; border-radius:8px; overflow:hidden;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%;">
            <thead>
                <tr style="background: var(--surface2);">
                    <th class="py-3 px-4">Full Name</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Department</th>
                    <th class="py-3 px-4">Password</th>
                    <th class="py-3 px-4 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($enrollment_committees)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No enrollment committee members found.</td></tr>
                <?php else: ?>
                    <?php foreach ($enrollment_committees as $a): ?>
                        <tr class="border-bottom">
                            <td class="py-3 px-4"><strong><i class="bi bi-person-badge text-muted me-2"></i> <?= htmlspecialchars($a['full_name']) ?></strong></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($a['email']) ?></td>
                            <td class="py-3 px-4"><span class="badge badge-info"><?= htmlspecialchars($a['department']) ?></span></td>
                            <td class="py-3 px-4">
                                <?php if (!empty($a['temp_password'])): ?>
                                    <span class="badge bg-warning text-dark" style="font-family: monospace;" title="Temporary Password">Temp: <?= htmlspecialchars($a['temp_password']) ?></span>
                                <?php else: ?>
                                    <span class="password-pill">********</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div style="display:inline-flex; gap:.5rem; align-items:center;">
                                    <button class="btn btn-secondary btn-sm"
                                            onclick="openEditEnrollmentCommittee(<?= $a['id'] ?>, '<?= htmlspecialchars(addslashes($a['full_name'])) ?>', '<?= htmlspecialchars(addslashes($a['email'])) ?>', '<?= htmlspecialchars(addslashes($a['department'])) ?>')">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <form action="<?= BASE_URL ?>admin/enrollment-committees/delete" method="POST" style="margin:0;"
                                          onsubmit="return confirmAction(this, 'Delete this enrollment committee member?', 'Delete', 'btn-danger')">
                                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ====== Add Enrollment Committee Modal ====== -->
<div id="addEnrollmentCommitteeModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add Enrollment Committee Member</h3>
            <button onclick="document.getElementById('addEnrollmentCommitteeModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/enrollment-committees/add" method="POST" class="modal-form">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" required placeholder="Prof. Juan Dela Cruz">
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required placeholder="committee@school.edu">
            </div>
            <div class="form-group">
                <label>Department *</label>
                <input type="text" name="department" required placeholder="College of Computing">
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="text" name="password" required placeholder="Set login password">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('addEnrollmentCommitteeModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Member</button>
            </div>
        </form>
    </div>
</div>

<!-- ====== Edit Enrollment Committee Modal ====== -->
<div id="editEnrollmentCommitteeModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Enrollment Committee Member</h3>
            <button onclick="document.getElementById('editEnrollmentCommitteeModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/enrollment-committees/edit" method="POST" class="modal-form" id="editAdvForm" onsubmit="return validateEditForm(event, this, 'editAdvAdminPassword')">
            <input type="hidden" name="id" id="editAdvId">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" id="editAdvName" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" id="editAdvEmail" required>
            </div>
            <div class="form-group">
                <label>Department *</label>
                <input type="text" name="department" id="editAdvDept" required>
            </div>
            <div class="form-group">
                <label>New Password <small class="text-muted">(leave blank to keep current)</small></label>
                <input type="text" name="password" id="editAdvPassword" placeholder="Leave blank to keep" oninput="toggleAdminPasswordReq(this, 'editAdvAdminPassword')">
            </div>
            <div class="form-group" id="editAdvAdminPasswordGroup" style="display:none;">
                <label>Admin Password <small class="text-danger">*Required for password change</small></label>
                <input type="password" name="admin_password" id="editAdvAdminPassword" placeholder="Enter your admin password">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('editEnrollmentCommitteeModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditEnrollmentCommittee(id, name, email, dept) {
    document.getElementById('editAdvId').value     = id;
    document.getElementById('editAdvName').value   = name;
    document.getElementById('editAdvEmail').value  = email;
    document.getElementById('editAdvDept').value   = dept;
    document.getElementById('editAdvPassword').value = '';
    document.getElementById('editAdvAdminPassword').value = '';
    document.getElementById('editAdvAdminPassword').required = false;
    document.getElementById('editAdvAdminPasswordGroup').style.display = 'none';
    document.getElementById('editEnrollmentCommitteeModal').style.display = 'flex';
}

function toggleAdminPasswordReq(input, adminPassId) {
    const adminPassInput = document.getElementById(adminPassId);
    const group = document.getElementById(adminPassId + 'Group');
    if (input.value.trim() !== '') {
        group.style.display = 'block';
        adminPassInput.required = true;
    } else {
        group.style.display = 'none';
        adminPassInput.required = false;
        adminPassInput.value = '';
        // clear errors if any
        adminPassInput.classList.remove('border-danger');
        const err = document.getElementById(adminPassId + 'Error');
        if (err) err.remove();
    }
}

async function validateEditForm(event, form, adminPassId) {
    const adminPassInput = document.getElementById(adminPassId);
    if (adminPassInput && adminPassInput.required && adminPassInput.value.trim() !== '') {
        event.preventDefault();
        try {
            const formData = new FormData();
            formData.append('admin_password', adminPassInput.value);
            const response = await fetch('<?= BASE_URL ?>admin/verify-password', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (!result.valid) {
                const group = document.getElementById(adminPassId + 'Group');
                let err = document.getElementById(adminPassId + 'Error');
                if (!err) {
                    err = document.createElement('small');
                    err.id = adminPassId + 'Error';
                    err.className = 'text-danger d-block mt-1';
                    group.appendChild(err);
                }
                err.textContent = 'Incorrect admin password. Please try again.';
                adminPassInput.classList.add('border-danger');
                return false;
            } else {
                form.submit();
            }
        } catch (e) {
            console.error(e);
        }
        return false;
    }
    return true;
}
</script>
