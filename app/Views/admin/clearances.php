<div class="page-header mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2>Clearances</h2>
        <p class="text-muted">Manage all active clearances.</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('createClearanceModal').style.display='flex'">
        <i class="bi bi-plus-lg"></i> New Clearance
    </button>
</div>

<?php if (empty($clearances)): ?>
    <div class="empty-state text-center p-5 gold-card" style="background:#fff; border-radius:8px;">
        <i class="bi bi-folder2-open display-1 text-muted mb-3 opacity-50"></i>
        <h3>No clearances yet</h3>
        <p class="text-muted">Create your first clearance to get started.</p>
        <button class="btn btn-primary mt-3" onclick="document.getElementById('createClearanceModal').style.display='flex'">
            <i class="bi bi-plus-lg"></i> Create Clearance
        </button>
    </div>
<?php else: ?>
    <div class="gold-card" style="background:#fff; border-radius:8px; overflow:hidden;">
        <div class="table-responsive">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr style="background: var(--surface2);">
                        <th class="py-3 px-4">Clearance Name</th>
                        <th class="py-3 px-4">School Year</th>
                        <th class="py-3 px-4 text-center">Signatories</th>
                        <th class="py-3 px-4 text-center">Enrollment Committee</th>
                        <th class="py-3 px-4 text-center">Students</th>
                        <th class="py-3 px-4 text-end" style="white-space: nowrap;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clearances as $c): ?>
                        <tr class="border-bottom">
                            <td class="py-3 px-4" data-label="Clearance Name">
                                <strong><?= htmlspecialchars($c['name']) ?></strong>
                                <?php if (!empty($c['description'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($c['description']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4" data-label="School Year"><?= htmlspecialchars($c['school_year']) ?></td>
                            <td class="py-3 px-4 text-center" data-label="Signatories"><span class="badge badge-info"><i class="bi bi-pen"></i> <?= $c['signatory_count'] ?></span></td>
                            <td class="py-3 px-4 text-center" data-label="Enrollment Committee"><span class="badge badge-info"><i class="bi bi-people"></i> <?= $c['enrollment_committee_count'] ?></span></td>
                            <td class="py-3 px-4 text-center" data-label="Students"><span class="badge badge-info"><i class="bi bi-mortarboard"></i> <?= $c['student_count'] ?></span></td>
                            <td class="py-3 px-4 text-end" data-label="Actions">
                                <div class="action-cell">
                                    <a href="<?= BASE_URL ?>admin/clearances/detail?id=<?= $c['id'] ?>"
                                       class="btn btn-primary btn-sm"><i class="bi bi-gear"></i> Manage</a>
                                    <button class="btn btn-secondary btn-sm"
                                            onclick="openEditClearance(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['name'])) ?>', '<?= htmlspecialchars(addslashes($c['description'])) ?>', '<?= htmlspecialchars($c['school_year']) ?>')">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <form action="<?= BASE_URL ?>admin/clearances/archive" method="POST" style="margin:0;"
                                          onsubmit="return confirmAction(this, 'Archive this clearance? It will be hidden from active list but all data is preserved.', 'Archive', 'btn-warning')">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-archive"></i> Archive</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
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
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
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