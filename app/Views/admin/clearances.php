<div class="page-header mb-4 d-flex justify-content-between align-items-center" style="padding-bottom: 1.25rem; border-bottom: 1px solid var(--border);">
    <div>
        <h2 style="font-size: 1.6rem; font-weight: 700; margin-bottom: .2rem;">Clearances</h2>
        <p class="text-muted" style="margin: 0; font-size: .95rem;">Manage all active clearances.</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('createClearanceModal').style.display='flex'">
        <i class="bi bi-plus-lg"></i> New Clearance
    </button>
</div>

<?php if (empty($clearances)): ?>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 3rem; text-align: center;">
        <i class="bi bi-folder2-open" style="font-size: 2.5rem; color: var(--text-muted); opacity: .4;"></i>
        <h3 style="margin-top: .75rem; font-size: 1.1rem;">No clearances yet</h3>
        <p class="text-muted" style="font-size: .9rem;">Create your first clearance to get started.</p>
        <button class="btn btn-primary mt-3" onclick="document.getElementById('createClearanceModal').style.display='flex'">
            <i class="bi bi-plus-lg"></i> Create Clearance
        </button>
    </div>
<?php else: ?>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
        <div class="table-responsive" style="width:100%;">
            <table class="data-table" style="width: 100%; font-size: 0.86rem;">
                <thead>
                    <tr style="background: var(--surface2);">
                        <th>Clearance Name</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">School Year</th>
                        <th class="text-center">Signatories</th>
                        <th class="text-center">Enrollment Committee</th>
                        <th class="text-center">Students</th>
                        <th class="text-end" style="white-space: nowrap;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clearances as $c): ?>
                        <tr class="border-bottom" style="transition: background .15s;" onmouseenter="this.style.background='var(--surface2)'" onmouseleave="this.style.background=''">
                            <td data-label="Clearance Name">
                                <strong><?= htmlspecialchars($c['name']) ?></strong>
                                <?php if (!empty($c['description'])): ?>
                                    <br><small class="text-muted" style="font-size:0.78rem;"><?= htmlspecialchars($c['description']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center" data-label="Type">
                                <?php if (($c['type'] ?? 'regular') === 'exit'): ?>
                                    <span class="badge badge-warning" style="font-size:.7rem; padding: 3px 6px;">Exit</span>
                                <?php else: ?>
                                    <span class="badge badge-info" style="font-size:.7rem; padding: 3px 6px;">Semestral</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center" data-label="School Year" style="white-space:nowrap;"><?= htmlspecialchars($c['school_year']) ?></td>
                            <td class="text-center" data-label="Signatories"><span class="badge badge-info"><?= $c['signatory_count'] ?></span></td>
                            <td class="text-center" data-label="Enrollment Committee"><span class="badge badge-info"><?= $c['enrollment_committee_count'] ?></span></td>
                            <td class="text-center" data-label="Students"><span class="badge badge-info"><?= $c['student_count'] ?></span></td>
                            <td class="text-end" data-label="Actions" style="white-space: nowrap;">
                                <div class="action-cell">
                                    <a href="<?= BASE_URL ?>admin/clearances/detail?id=<?= $c['id'] ?>"
                                       class="btn btn-primary btn-sm">Manage</a>
                                    <button class="btn btn-secondary btn-sm"
                                            onclick="openEditClearance(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['name'])) ?>', '<?= htmlspecialchars(addslashes($c['description'])) ?>', '<?= htmlspecialchars($c['school_year']) ?>', '<?= $c['type'] ?? 'regular' ?>')">
                                        Edit
                                    </button>
                                    <form action="<?= BASE_URL ?>admin/clearances/archive" method="POST" style="margin:0; display:inline;"
                                          onsubmit="return confirmAction(this, 'Archive this clearance? It will be hidden from active list but all data is preserved.', 'Archive', 'btn-warning')">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-warning btn-sm">Archive</button>
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
                <label>School Year <span class="required">*</span></label>
                <input type="text" name="school_year" required placeholder="e.g. 2024-2025">
            </div>
            <div class="form-group">
                <label>Clearance Type <span class="required">*</span></label>
                <div style="display:flex; flex-direction:row; gap:1.5rem; align-items:center; margin-top:.25rem;">
                    <label style="display:inline-flex; align-items:center; gap:.4rem; cursor:pointer; font-size:.9rem; font-weight:500; color:var(--text); text-transform:none; letter-spacing:normal; width:auto;">
                        <input type="radio" name="type" value="regular" style="width:16px; height:16px; margin:0; padding:0; flex-shrink:0; accent-color:var(--primary); cursor:pointer;" checked> Semestral Clearance
                    </label>
                    <label style="display:inline-flex; align-items:center; gap:.4rem; cursor:pointer; font-size:.9rem; font-weight:500; color:var(--text); text-transform:none; letter-spacing:normal; width:auto;">
                        <input type="radio" name="type" value="exit" style="width:16px; height:16px; margin:0; padding:0; flex-shrink:0; accent-color:var(--primary); cursor:pointer;"> Exit Clearance
                    </label>
                </div>
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
                <label>School Year <span class="required">*</span></label>
                <input type="text" name="school_year" id="editClearanceYear" required>
            </div>
            <div class="form-group">
                <label>Clearance Type <span class="required">*</span></label>
                <div style="display:flex; flex-direction:row; gap:1.5rem; align-items:center; margin-top:.25rem;">
                    <label style="display:inline-flex; align-items:center; gap:.4rem; cursor:pointer; font-size:.9rem; font-weight:500; color:var(--text); text-transform:none; letter-spacing:normal; width:auto;">
                        <input type="radio" name="type" id="editTypeRegular" value="regular" style="width:16px; height:16px; margin:0; padding:0; flex-shrink:0; accent-color:var(--primary); cursor:pointer;"> Semestral Clearance
                    </label>
                    <label style="display:inline-flex; align-items:center; gap:.4rem; cursor:pointer; font-size:.9rem; font-weight:500; color:var(--text); text-transform:none; letter-spacing:normal; width:auto;">
                        <input type="radio" name="type" id="editTypeExit" value="exit" style="width:16px; height:16px; margin:0; padding:0; flex-shrink:0; accent-color:var(--primary); cursor:pointer;"> Exit Clearance
                    </label>
                </div>
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
function openEditClearance(id, name, desc, year, type) {
    document.getElementById('editClearanceId').value   = id;
    document.getElementById('editClearanceName').value = name;
    document.getElementById('editClearanceDesc').value = desc;
    document.getElementById('editClearanceYear').value = year;
    document.getElementById('editTypeRegular').checked = (type !== 'exit');
    document.getElementById('editTypeExit').checked    = (type === 'exit');
    document.getElementById('editClearanceModal').style.display = 'flex';
}
</script>