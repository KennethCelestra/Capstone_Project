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
                    <th class="py-3 px-4" style="min-width: 180px;">Full Name</th>
                    <th class="py-3 px-4" style="min-width: 180px;">Email</th>
                    <th class="py-3 px-4">Office</th>
                    <th class="py-3 px-4">Scope</th>
                    <th class="py-3 px-4">Password</th>
                    <th class="py-3 px-4 text-end" style="white-space: nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($signatories)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No signatories found.</td></tr>
                <?php else: ?>
                    <?php foreach ($signatories as $s): ?>
                        <tr class="border-bottom">
                            <td class="py-3 px-4" data-label="Full Name"><strong><?= htmlspecialchars($s['full_name']) ?></strong></td>
                            <td class="py-3 px-4" data-label="Email"><?= htmlspecialchars($s['email']) ?></td>
                            <td class="py-3 px-4" data-label="Office"><span class="badge badge-info"><?= htmlspecialchars($s['office']) ?></span></td>
                            <td class="py-3 px-4" data-label="Scope">
                                <?php if (empty($s['scope_type'])): ?>
                                    <span class="text-muted" style="font-size:0.85rem;">All Students</span>
                                <?php else: ?>
                                    <?= ucfirst(htmlspecialchars($s['scope_type'])) ?>: <?= htmlspecialchars($s['scope_value']) ?>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4" data-label="Password">
                                <?php if (!empty($s['temp_password'])): ?>
                                    <span class="badge bg-warning text-dark" style="font-family: monospace;" title="Temporary Password"><?= htmlspecialchars($s['temp_password']) ?></span>
                                <?php else: ?>
                                    <span class="password-pill">********</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-end" data-label="Actions">
                                <div class="action-cell">
                                    <button class="btn btn-secondary btn-sm"
                                            onclick="openEditSignatory(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['full_name'])) ?>', '<?= htmlspecialchars(addslashes($s['email'])) ?>', '<?= htmlspecialchars(addslashes($s['office'])) ?>', '<?= htmlspecialchars(addslashes($s['scope_type'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($s['scope_value'] ?? '')) ?>')">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <form action="<?= BASE_URL ?>admin/signatories/delete" method="POST" style="margin:0;"
                                          onsubmit="return confirmAction(this, 'Delete this signatory?', 'Delete', 'btn-danger')">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
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
                <label>Office / Department *</label>
                <input type="text" name="office" required>
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="text" name="password" required>
            </div>

            <!-- Add Scope Selection -->
            <div class="form-group" style="margin-top: 1rem;">
                <label>Student Scope <small class="text-muted">(Which students must this signatory clear?)</small></label>
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:8px; margin-top:8px;">
                    <label style="display:flex; flex-direction:column; align-items:center; gap:6px; padding:12px 8px; background:var(--surface2); border:2px solid var(--border-color); border-radius:8px; cursor:pointer; text-align:center; transition:border-color .15s;">
                        <input type="radio" name="scope_type" value="" checked onchange="toggleAddScopeInputs(this.value)" style="margin:0; accent-color:var(--primary);">
                        <span style="font-weight:600; font-size:0.85rem;">All Students</span>
                    </label>
                    <label style="display:flex; flex-direction:column; align-items:center; gap:6px; padding:12px 8px; background:var(--surface2); border:2px solid var(--border-color); border-radius:8px; cursor:pointer; text-align:center; transition:border-color .15s;">
                        <input type="radio" name="scope_type" value="college" onchange="toggleAddScopeInputs(this.value)" style="margin:0; accent-color:#0ea5e9;">
                        <span style="font-weight:600; font-size:0.85rem;">By College</span>
                    </label>
                    <label style="display:flex; flex-direction:column; align-items:center; gap:6px; padding:12px 8px; background:var(--surface2); border:2px solid var(--border-color); border-radius:8px; cursor:pointer; text-align:center; transition:border-color .15s;">
                        <input type="radio" name="scope_type" value="course" onchange="toggleAddScopeInputs(this.value)" style="margin:0; accent-color:#22c55e;">
                        <span style="font-weight:600; font-size:0.85rem;">By Course</span>
                    </label>
                </div>
                <div id="add_scope_college_container" style="display:none; margin-top:10px;">
                    <input type="text" name="scope_college" id="add_scope_college_input" list="college_list" style="width:100%;">
                </div>
                <div id="add_scope_course_container" style="display:none; margin-top:10px;">
                    <input type="text" name="scope_course" id="add_scope_course_input" list="course_list" style="width:100%;">
                </div>
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
        <form action="<?= BASE_URL ?>admin/signatories/edit" method="POST" class="modal-form" id="editSignatoryForm">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
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
            
            <!-- Edit Scope Selection -->
            <div class="form-group" style="margin-top: 1rem;">
                <label>Student Scope</label>
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:8px; margin-top:8px;">
                    <label style="display:flex; flex-direction:column; align-items:center; gap:6px; padding:12px 8px; background:var(--surface2); border:2px solid var(--border-color); border-radius:8px; cursor:pointer; text-align:center; transition:border-color .15s;">
                        <input type="radio" name="scope_type" id="editSigScopeGlobal" value="" onchange="toggleEditScopeInputs(this.value)" style="margin:0; accent-color:var(--primary);">
                        <span style="font-weight:600; font-size:0.85rem;">All Students</span>
                    </label>
                    <label style="display:flex; flex-direction:column; align-items:center; gap:6px; padding:12px 8px; background:var(--surface2); border:2px solid var(--border-color); border-radius:8px; cursor:pointer; text-align:center; transition:border-color .15s;">
                        <input type="radio" name="scope_type" id="editSigScopeCollege" value="college" onchange="toggleEditScopeInputs(this.value)" style="margin:0; accent-color:#0ea5e9;">
                        <span style="font-weight:600; font-size:0.85rem;">By College</span>
                    </label>
                    <label style="display:flex; flex-direction:column; align-items:center; gap:6px; padding:12px 8px; background:var(--surface2); border:2px solid var(--border-color); border-radius:8px; cursor:pointer; text-align:center; transition:border-color .15s;">
                        <input type="radio" name="scope_type" id="editSigScopeCourse" value="course" onchange="toggleEditScopeInputs(this.value)" style="margin:0; accent-color:#22c55e;">
                        <span style="font-weight:600; font-size:0.85rem;">By Course</span>
                    </label>
                </div>
                <div id="edit_scope_college_container" style="display:none; margin-top:10px;">
                    <input type="text" name="scope_college" id="edit_scope_college_input" list="college_list" placeholder="College" style="width:100%;">
                </div>
                <div id="edit_scope_course_container" style="display:none; margin-top:10px;">
                    <input type="text" name="scope_course" id="edit_scope_course_input" list="course_list" placeholder="Course" style="width:100%;">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('editSignatoryModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<datalist id="college_list">
    <?php foreach ($colleges as $col): ?>
        <option value="<?= htmlspecialchars($col) ?>"></option>
    <?php endforeach; ?>
</datalist>
<datalist id="course_list">
    <?php foreach ($courses as $cur): ?>
        <option value="<?= htmlspecialchars($cur) ?>"></option>
    <?php endforeach; ?>
</datalist>

<script>
function toggleAddScopeInputs(val) {
    document.getElementById('add_scope_college_container').style.display = 'none';
    document.getElementById('add_scope_course_container').style.display = 'none';
    document.getElementById('add_scope_college_input').required = false;
    document.getElementById('add_scope_course_input').required = false;
    
    if (val === 'college') {
        document.getElementById('add_scope_college_container').style.display = 'block';
        document.getElementById('add_scope_college_input').required = true;
    } else if (val === 'course') {
        document.getElementById('add_scope_course_container').style.display = 'block';
        document.getElementById('add_scope_course_input').required = true;
    }
}

function toggleEditScopeInputs(val) {
    document.getElementById('edit_scope_college_container').style.display = 'none';
    document.getElementById('edit_scope_course_container').style.display = 'none';
    document.getElementById('edit_scope_college_input').required = false;
    document.getElementById('edit_scope_course_input').required = false;
    
    if (val === 'college') {
        document.getElementById('edit_scope_college_container').style.display = 'block';
        document.getElementById('edit_scope_college_input').required = true;
    } else if (val === 'course') {
        document.getElementById('edit_scope_course_container').style.display = 'block';
        document.getElementById('edit_scope_course_input').required = true;
    }
}

function openEditSignatory(id, name, email, office, scopeType, scopeValue) {
    document.getElementById('editSigId').value     = id;
    document.getElementById('editSigName').value   = name;
    document.getElementById('editSigEmail').value  = email;
    document.getElementById('editSigOffice').value = office;
    
    document.getElementById('editSigScopeGlobal').checked = true;
    document.getElementById('edit_scope_college_input').value = '';
    document.getElementById('edit_scope_course_input').value = '';
    
    if (scopeType === 'college') {
        document.getElementById('editSigScopeCollege').checked = true;
        document.getElementById('edit_scope_college_input').value = scopeValue;
    } else if (scopeType === 'course') {
        document.getElementById('editSigScopeCourse').checked = true;
        document.getElementById('edit_scope_course_input').value = scopeValue;
    }
    toggleEditScopeInputs(scopeType);
    document.getElementById('editSignatoryModal').style.display = 'flex';
}

</script>
