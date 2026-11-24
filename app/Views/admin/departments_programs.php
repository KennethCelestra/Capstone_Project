<?php if (!empty($flash)): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> mb-4" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="page-header mb-4" style="padding-bottom: 1.25rem; border-bottom: 1px solid var(--border);">
    <h2 style="font-size: 1.6rem; font-weight: 700; margin-bottom: .2rem;">Departments, Programs & Offices</h2>
    <p class="text-muted" style="margin: 0; font-size: .95rem;">Manage academic colleges, degree programs, and signatory offices.</p>
</div>

<!-- Nav Tabs -->
<div class="filter-bar mb-4" style="display:flex; gap:0.5rem; border-bottom:1px solid var(--border); padding-bottom:0.5rem;">
    <button type="button" class="btn btn-secondary active tab-btn" id="tab-btn-programs" onclick="switchDeptTab('programs')">
        <i class="bi bi-mortarboard me-1"></i> Programs (<?= count($programs) ?>)
    </button>
    <button type="button" class="btn btn-secondary tab-btn" id="tab-btn-colleges" onclick="switchDeptTab('colleges')">
        <i class="bi bi-building me-1"></i> Colleges (<?= count($colleges) ?>)
    </button>
    <button type="button" class="btn btn-secondary tab-btn" id="tab-btn-offices" onclick="switchDeptTab('offices')">
        <i class="bi bi-briefcase me-1"></i> Offices (<?= count($offices) ?>)
    </button>
</div>

<!-- ========================================== -->
<!-- PROGRAMS TAB                               -->
<!-- ========================================== -->
<div id="dept-section-programs">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="font-weight:700; margin:0;">Academic Programs</h4>
        <button class="btn btn-primary" onclick="openAddProgramModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Program
        </button>
    </div>

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
        <div class="table-responsive">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr style="background: var(--surface2);">
                        <th style="width: 15%;">College</th>
                        <th style="width: 20%;">Program Code</th>
                        <th style="width: 50%;">Program Name</th>
                        <th class="text-end" style="width: 15%; white-space: nowrap;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($programs)): ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">No programs registered.</td></tr>
                    <?php else: ?>
                        <?php foreach ($programs as $prog): ?>
                            <tr class="border-bottom">
                                <td data-label="College"><span class="badge badge-info"><?= htmlspecialchars($prog['college_name'] ?? '') ?></span></td>
                                <td data-label="Program Code"><strong><?= htmlspecialchars($prog['code']) ?></strong></td>
                                <td data-label="Program Name"><?= htmlspecialchars($prog['name']) ?></td>
                                <td class="text-end" data-label="Actions">
                                    <div class="action-cell">
                                        <button class="btn btn-secondary btn-sm"
                                                onclick="openEditProgramModal(<?= $prog['id'] ?>, <?= $prog['college_id'] ?>, '<?= htmlspecialchars(addslashes($prog['name'])) ?>', '<?= htmlspecialchars(addslashes($prog['code'])) ?>')">
                                            Edit
                                        </button>
                                        <form action="<?= BASE_URL ?>admin/departments/programs/delete" method="POST" style="margin:0;"
                                              onsubmit="return confirmAction(this, 'Delete this program (<?= htmlspecialchars(addslashes($prog['code'])) ?>)?', 'Delete', 'btn-danger')">
                                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                            <input type="hidden" name="id" value="<?= $prog['id'] ?>">
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
</div>

<!-- ========================================== -->
<!-- COLLEGES TAB                               -->
<!-- ========================================== -->
<div id="dept-section-colleges" style="display:none;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="font-weight:700; margin:0;">Colleges</h4>
        <button class="btn btn-primary" onclick="document.getElementById('addCollegeModal').style.display='flex'">
            <i class="bi bi-plus-lg me-1"></i> Add College
        </button>
    </div>

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
        <div class="table-responsive">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr style="background: var(--surface2);">
                        <th style="width: 20%;">ID</th>
                        <th style="width: 60%;">College Code / Name</th>
                        <th class="text-end" style="width: 20%; white-space: nowrap;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($colleges)): ?>
                        <tr><td colspan="3" class="text-center py-4 text-muted">No colleges found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($colleges as $col): ?>
                            <tr class="border-bottom">
                                <td data-label="ID">#<?= $col['id'] ?></td>
                                <td data-label="College Code"><strong><?= htmlspecialchars($col['name']) ?></strong></td>
                                <td class="text-end" data-label="Actions">
                                    <div class="action-cell">
                                        <form action="<?= BASE_URL ?>admin/departments/colleges/delete" method="POST" style="margin:0;"
                                              onsubmit="return confirmAction(this, 'Delete college <?= htmlspecialchars(addslashes($col['name'])) ?> and its associated programs?', 'Delete', 'btn-danger')">
                                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                            <input type="hidden" name="id" value="<?= $col['id'] ?>">
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
</div>

<!-- ========================================== -->
<!-- OFFICES TAB                                -->
<!-- ========================================== -->
<div id="dept-section-offices" style="display:none;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="font-weight:700; margin:0;">Signatory Offices</h4>
        <button class="btn btn-primary" onclick="document.getElementById('addOfficeModal').style.display='flex'">
            <i class="bi bi-plus-lg me-1"></i> Add Office
        </button>
    </div>

    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden;">
        <div class="table-responsive">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr style="background: var(--surface2);">
                        <th style="width: 20%;">ID</th>
                        <th style="width: 60%;">Office Name</th>
                        <th class="text-end" style="width: 20%; white-space: nowrap;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($offices)): ?>
                        <tr><td colspan="3" class="text-center py-4 text-muted">No offices found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($offices as $off): ?>
                            <tr class="border-bottom">
                                <td data-label="ID">#<?= $off['id'] ?></td>
                                <td data-label="Office Name"><strong><?= htmlspecialchars($off['name']) ?></strong></td>
                                <td class="text-end" data-label="Actions">
                                    <div class="action-cell">
                                        <button class="btn btn-secondary btn-sm"
                                                onclick="openEditOfficeModal(<?= $off['id'] ?>, '<?= htmlspecialchars(addslashes($off['name'])) ?>')">
                                            Edit
                                        </button>
                                        <form action="<?= BASE_URL ?>admin/offices/delete" method="POST" style="margin:0;"
                                              onsubmit="return confirmAction(this, 'Delete office <?= htmlspecialchars(addslashes($off['name'])) ?>?', 'Delete', 'btn-danger')">
                                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                            <input type="hidden" name="id" value="<?= $off['id'] ?>">
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
</div>

<!-- ========================================== -->
<!-- MODALS                                     -->
<!-- ========================================== -->

<!-- Add Program Modal -->
<div id="addProgramModal" class="modal" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add New Program</h3>
            <button class="modal-close" onclick="document.getElementById('addProgramModal').style.display='none'">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/departments/programs/add" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label>College *</label>
                    <select name="college_id" required class="form-control" style="width:100%;">
                        <option value="">-- Select College --</option>
                        <?php foreach ($colleges as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label>Program Code (e.g. BSIT) *</label>
                    <input type="text" name="code" required class="form-control" placeholder="BSIT" style="width:100%;">
                </div>
                <div class="form-group mb-3">
                    <label>Program Full Name *</label>
                    <input type="text" name="name" required class="form-control" placeholder="Bachelor of Science in Information Technology" style="width:100%;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addProgramModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Program</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Program Modal -->
<div id="editProgramModal" class="modal" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Program</h3>
            <button class="modal-close" onclick="document.getElementById('editProgramModal').style.display='none'">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/departments/programs/edit" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="id" id="editProgId">
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label>College *</label>
                    <select name="college_id" id="editProgCollegeId" required class="form-control" style="width:100%;">
                        <option value="">-- Select College --</option>
                        <?php foreach ($colleges as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label>Program Code *</label>
                    <input type="text" name="code" id="editProgCode" required class="form-control" style="width:100%;">
                </div>
                <div class="form-group mb-3">
                    <label>Program Full Name *</label>
                    <input type="text" name="name" id="editProgName" required class="form-control" style="width:100%;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editProgramModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Add College Modal -->
<div id="addCollegeModal" class="modal" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add New College</h3>
            <button class="modal-close" onclick="document.getElementById('addCollegeModal').style.display='none'">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/departments/colleges/add" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label>College Code/Name (e.g. CAS, CCI, CEA) *</label>
                    <input type="text" name="name" required class="form-control" placeholder="CCI" style="width:100%;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addCollegeModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Add College</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Office Modal -->
<div id="addOfficeModal" class="modal" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add New Office</h3>
            <button class="modal-close" onclick="document.getElementById('addOfficeModal').style.display='none'">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/offices/add" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label>Office Name *</label>
                    <input type="text" name="name" required class="form-control" placeholder="e.g. Guidance Office" style="width:100%;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addOfficeModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Office</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Office Modal -->
<div id="editOfficeModal" class="modal" style="display:none;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Office</h3>
            <button class="modal-close" onclick="document.getElementById('editOfficeModal').style.display='none'">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/offices/edit" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <input type="hidden" name="id" id="editOfficeId">
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label>Office Name *</label>
                    <input type="text" name="name" id="editOfficeName" required class="form-control" style="width:100%;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editOfficeModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchDeptTab(tab) {
    ['programs', 'colleges', 'offices'].forEach(t => {
        const sec = document.getElementById('dept-section-' + t);
        const btn = document.getElementById('tab-btn-' + t);
        if (t === tab) {
            sec.style.display = 'block';
            btn.classList.add('active', 'btn-primary');
            btn.classList.remove('btn-secondary');
        } else {
            sec.style.display = 'none';
            btn.classList.remove('active', 'btn-primary');
            btn.classList.add('btn-secondary');
        }
    });
}

function openAddProgramModal() {
    document.getElementById('addProgramModal').style.display = 'flex';
}

function openEditProgramModal(id, collegeId, name, code) {
    document.getElementById('editProgId').value = id;
    document.getElementById('editProgCollegeId').value = collegeId;
    document.getElementById('editProgName').value = name;
    document.getElementById('editProgCode').value = code;
    document.getElementById('editProgramModal').style.display = 'flex';
}

function openEditOfficeModal(id, name) {
    document.getElementById('editOfficeId').value = id;
    document.getElementById('editOfficeName').value = name;
    document.getElementById('editOfficeModal').style.display = 'flex';
}
</script>

