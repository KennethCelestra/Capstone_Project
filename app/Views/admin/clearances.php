<div class="page-header">
    <h2>Clearances</h2>
    <button class="btn btn-primary" onclick="document.getElementById('createClearanceModal').style.display='flex'">
        + New Clearance
    </button>
</div>

<?php if (empty($clearances)): ?>
    <div class="empty-state">
        <h3>No clearances yet</h3>
        <p>Create your first clearance to get started.</p>
        <button class="btn btn-primary" onclick="document.getElementById('createClearanceModal').style.display='flex'">
            Create Clearance
        </button>
    </div>
<?php else: ?>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Clearance Name</th>
                    <th>School Year</th>
                    <th>Signatories</th>
                    <th>Advisers</th>
                    <th>Students</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clearances as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['name']) ?></strong>
                            <?php if (!empty($c['description'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($c['description']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($c['school_year']) ?></td>
                        <td><span class="badge badge-info"><?= $c['signatory_count'] ?></span></td>
                        <td><span class="badge badge-info"><?= $c['adviser_count'] ?></span></td>
                        <td><span class="badge badge-info"><?= $c['student_count'] ?></span></td>
                        <td class="action-cell">
                            <a href="<?= BASE_URL ?>admin/clearances/detail?id=<?= $c['id'] ?>"
                               class="btn btn-primary btn-sm">Manage</a>
                            <button class="btn btn-secondary btn-sm"
                                    onclick="openEditClearance(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['name'])) ?>', '<?= htmlspecialchars(addslashes($c['description'])) ?>', '<?= htmlspecialchars($c['school_year']) ?>')">
                                Edit
                            </button>
                            <form action="<?= BASE_URL ?>admin/clearances/archive" method="POST" style="display:inline"
                                  onsubmit="return confirm('Archive this clearance? It will be hidden from active list but all data is preserved.')">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn btn-warning btn-sm">Archive</button>
                            </form>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>


<!-- ====== Create Clearance Modal ====== -->
<div id="createClearanceModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>New Clearance</h3>
            <button onclick="document.getElementById('createClearanceModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/clearances/create" method="POST" class="modal-form">
            <div class="form-group">
                <label>Clearance Name <span class="required">*</span></label>
                <input type="text" name="name" required placeholder="e.g. BSIT Clearance S2 2025">
            </div>
            <div class="form-group">
                <label>School Year</label>
                <input type="text" name="school_year" placeholder="e.g. 2024-2025">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="2" placeholder="Optional notes about this clearance"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('createClearanceModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Clearance</button>
            </div>
        </form>
    </div>
</div>

<!-- ====== Edit Clearance Modal ====== -->
<div id="editClearanceModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Clearance</h3>
            <button onclick="document.getElementById('editClearanceModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/clearances/edit" method="POST" class="modal-form">
            <input type="hidden" name="id" id="editClearanceId">
            <div class="form-group">
                <label>Clearance Name <span class="required">*</span></label>
                <input type="text" name="name" id="editClearanceName" required>
            </div>
            <div class="form-group">
                <label>School Year</label>
                <input type="text" name="school_year" id="editClearanceYear">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="editClearanceDesc" rows="2"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('editClearanceModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditClearance(id, name, desc, year) {
    document.getElementById('editClearanceId').value   = id;
    document.getElementById('editClearanceName').value = name;
    document.getElementById('editClearanceDesc').value = desc;
    document.getElementById('editClearanceYear').value = year;
    document.getElementById('editClearanceModal').style.display = 'flex';
}
</script>