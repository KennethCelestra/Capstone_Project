<div class="page-header">
    <h2>Manage Signatories</h2>
    <button class="btn btn-primary" onclick="document.getElementById('addSignatoryModal').style.display='flex'">
        + Add Signatory
    </button>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Office</th>
                <th>Password</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($signatories)): ?>
                <tr><td colspan="5" class="text-center">No signatories found.</td></tr>
            <?php else: ?>
                <?php foreach ($signatories as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['full_name']) ?></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td><?= htmlspecialchars($s['office']) ?></td>
                        <td>
                            <span class="password-pill"><?= htmlspecialchars($s['plain_password']) ?></span>
                        </td>
                        <td class="action-cell">
                            <button class="btn btn-secondary btn-sm"
                                    onclick="openEditSignatory(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['full_name'])) ?>', '<?= htmlspecialchars(addslashes($s['email'])) ?>', '<?= htmlspecialchars(addslashes($s['office'])) ?>')">
                                Edit
                            </button>
                            <form action="<?= BASE_URL ?>admin/signatories/delete" method="POST" style="display:inline"
                                  onsubmit="return confirm('Delete this signatory?')">
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
        <form action="<?= BASE_URL ?>admin/signatories/edit" method="POST" class="modal-form">
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
                <input type="text" name="password" id="editSigPassword" placeholder="Leave blank to keep">
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
    document.getElementById('editSignatoryModal').style.display = 'flex';
}
</script>
