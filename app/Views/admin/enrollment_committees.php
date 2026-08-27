<div class="page-header mb-4 d-flex justify-content-between align-items-center" style="padding-bottom: 1.25rem; border-bottom: 1px solid var(--border);">
    <div>
        <h2 style="font-size: 1.6rem; font-weight: 700; margin-bottom: .2rem;">Manage Enrollment Committee</h2>
        <p class="text-muted" style="margin: 0; font-size: .95rem;">Add or edit enrollment committee members.</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addEnrollmentCommitteeModal').style.display='flex'">
        Add Member
    </button>
</div>

<div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%;">
            <thead>
                <tr style="background: var(--surface2);">
                    <th style="width: 28%;">Full Name</th>
                    <th style="width: 28%;">Email</th>
                    <th style="width: 16%;">College</th>
                    <th style="width: 16%;">Password</th>
                    <th class="text-end" style="width: 12%; white-space: nowrap;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($enrollment_committees)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No enrollment committee members found.</td></tr>
                <?php else: ?>
                    <?php foreach ($enrollment_committees as $a): ?>
                        <tr class="border-bottom" style="transition: background .15s;" onmouseenter="this.style.background='var(--surface2)'" onmouseleave="this.style.background=''">
                            <td data-label="Full Name"><strong><?= htmlspecialchars($a['full_name']) ?></strong></td>
                            <td data-label="Email"><?= htmlspecialchars($a['email']) ?></td>
                            <td data-label="College"><span class="badge badge-info"><?= htmlspecialchars($a['department']) ?></span></td>
                            <td data-label="Password">
                                <?php if (!empty($a['temp_password'])): ?>
                                    <span class="badge bg-warning text-dark" style="font-family: monospace;" title="Temporary Password"><?= htmlspecialchars($a['temp_password']) ?></span>
                                <?php else: ?>
                                    <span class="password-pill">********</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end" data-label="Actions">
                                <div class="action-cell">
                                    <button class="btn btn-secondary btn-sm"
                                            onclick="openEditEnrollmentCommittee(<?= $a['id'] ?>, '<?= htmlspecialchars(addslashes($a['full_name'])) ?>', '<?= htmlspecialchars(addslashes($a['email'])) ?>', '<?= htmlspecialchars(addslashes($a['department'])) ?>')">
                                        Edit
                                    </button>
                                    <form action="<?= BASE_URL ?>admin/enrollment-committees/delete" method="POST" style="margin:0;"
                                          onsubmit="return confirmAction(this, 'Delete this enrollment committee member?', 'Delete', 'btn-danger')">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
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
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>College *</label>
                <input type="text" name="department" required>
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="text" name="password" required>
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
        <form action="<?= BASE_URL ?>admin/enrollment-committees/edit" method="POST" class="modal-form" id="editEnrollmentCommitteeForm">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
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
                <label>College *</label>
                <input type="text" name="department" id="editAdvDept" required>
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
    document.getElementById('editEnrollmentCommitteeModal').style.display = 'flex';
}
</script>
