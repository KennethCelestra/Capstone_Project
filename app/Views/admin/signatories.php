<div class="page-header mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2>Manage Signatories</h2>
        <p class="text-muted">Add or edit signatory accounts.</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addSignatoryModal').style.display='flex'">
        <i class="bi bi-person-plus"></i> Add Signatory
    </button>
</div>

<div class="blue-card" style="background:#fff; border-radius:8px; overflow:hidden;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%;">
            <thead>
                <tr style="background: var(--surface2);">
                    <th class="py-3 px-4">Full Name</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Office</th>
                    <th class="py-3 px-4">Password</th>
                    <th class="py-3 px-4 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($signatories)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No signatories found.</td></tr>
                <?php else: ?>
                    <?php foreach ($signatories as $s): ?>
                        <tr class="border-bottom">
                            <td class="py-3 px-4"><strong><i class="bi bi-person-circle text-muted me-2"></i> <?= htmlspecialchars($s['full_name']) ?></strong></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($s['email']) ?></td>
                            <td class="py-3 px-4"><span class="badge badge-info"><?= htmlspecialchars($s['office']) ?></span></td>
                            <td class="py-3 px-4">
                                <?php if (!empty($s['temp_password'])): ?>
                                    <span class="badge bg-warning text-dark" style="font-family: monospace;" title="Temporary Password">Temp: <?= htmlspecialchars($s['temp_password']) ?></span>
                                <?php else: ?>
                                    <span class="password-pill">********</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div style="display:inline-flex; gap:.5rem; align-items:center;">
                                    <button class="btn btn-secondary btn-sm"
                                            onclick="openEditSignatory(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['full_name'])) ?>', '<?= htmlspecialchars(addslashes($s['email'])) ?>', '<?= htmlspecialchars(addslashes($s['office'])) ?>')">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <form action="<?= BASE_URL ?>admin/signatories/delete" method="POST" style="margin:0;"
                                          onsubmit="return confirmAction(this, 'Delete this signatory?', 'Delete', 'btn-danger')">
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
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

<!-- ====== Add Signatory Modal ====== -->
<div id="addSignatoryModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add New Signatory</h3>
            <button onclick="document.getElementById('addSignatoryModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/signatories/add" method="POST" class="modal-form">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" required placeholder="Ms. Maria Santos">
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required placeholder="signatory@school.edu">
            </div>
            <div class="form-group">
                <label>Office / Department *</label>
                <input type="text" name="office" required placeholder="Registrar's Office">
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="text" name="password" required placeholder="Set login password">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('addSignatoryModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Signatory</button>
            </div>
        </form>
    </div>
</div>

<!-- ====== Edit Signatory Modal ====== -->
<div id="editSignatoryModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Signatory</h3>
            <button onclick="document.getElementById('editSignatoryModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/signatories/edit" method="POST" class="modal-form" id="editSignatoryForm" onsubmit="return validateEditForm(event, this, 'editSigAdminPassword')">
            <input type="hidden" name="id" id="editSigId">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" id="editSigName" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" id="editSigEmail" required>
            </div>
            <div class="form-group">
                <label>Office / Department *</label>
                <input type="text" name="office" id="editSigOffice" required>
            </div>
            <div class="form-group">
                <label>New Password <small class="text-muted">(leave blank to keep current)</small></label>
                <input type="text" name="password" id="editSigPassword" placeholder="Leave blank to keep" oninput="toggleAdminPasswordReq(this, 'editSigAdminPassword')">
            </div>
            <div class="form-group" id="editSigAdminPasswordGroup" style="display:none;">
                <label>Admin Password <small class="text-danger">*Required for password change</small></label>
                <input type="password" name="admin_password" id="editSigAdminPassword" placeholder="Enter your admin password">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('editSignatoryModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditSignatory(id, name, email, office) {
    document.getElementById('editSigId').value     = id;
    document.getElementById('editSigName').value   = name;
    document.getElementById('editSigEmail').value  = email;
    document.getElementById('editSigOffice').value = office;
    document.getElementById('editSigPassword').value = '';
    document.getElementById('editSigAdminPassword').value = '';
    document.getElementById('editSigAdminPassword').required = false;
    document.getElementById('editSigAdminPasswordGroup').style.display = 'none';
    document.getElementById('editSignatoryModal').style.display = 'flex';
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
