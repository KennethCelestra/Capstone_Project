<div class="page-header mb-4">
    <div>
        <a href="<?= BASE_URL ?>admin/clearances" class="back-link text-decoration-none mb-2 d-inline-block"><i class="bi bi-arrow-left"></i> Active Clearances</a>
        <h2>Archived Clearances</h2>
        <p class="text-muted">These clearances are archived and hidden from active use. All data is preserved.</p>
    </div>
</div>

<?php if (empty($clearances)): ?>
    <div class="empty-state text-center p-5 blue-card" style="background:#fff; border-radius:8px;">
        <i class="bi bi-archive display-1 text-muted mb-3 opacity-50"></i>
        <h3>No archived clearances</h3>
        <p class="text-muted">Clearances you archive will appear here. You can restore them at any time.</p>
        <a href="<?= BASE_URL ?>admin/clearances" class="btn btn-primary mt-3"><i class="bi bi-arrow-left"></i> Back to Active Clearances</a>
    </div>
<?php else: ?>
    <div class="blue-card" style="background:#fff; border-radius:8px; overflow:hidden;">
        <div class="table-responsive">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr style="background: var(--surface2);">
                        <th class="py-3 px-4">Clearance Name</th>
                        <th class="py-3 px-4">School Year</th>
                        <th class="py-3 px-4 text-center">Signatories</th>
                        <th class="py-3 px-4 text-center">Students</th>
                        <th class="py-3 px-4">Archived Since</th>
                        <th class="py-3 px-4 text-end" style="white-space: nowrap;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clearances as $c): ?>
                        <tr class="row-archived border-bottom" style="opacity: 0.85;">
                            <td class="py-3 px-4" data-label="Clearance Name">
                                <strong><i class="bi bi-archive text-muted me-2"></i><?= htmlspecialchars($c['name']) ?></strong>
                                <?php if (!empty($c['description'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($c['description']) ?></small>
                                <?php endif; ?>
                                <br><span class="badge bg-secondary mt-1">Archived</span>
                            </td>
                            <td class="py-3 px-4" data-label="School Year"><?= htmlspecialchars($c['school_year']) ?></td>
                            <td class="py-3 px-4 text-center" data-label="Signatories"><span class="badge badge-info"><i class="bi bi-pen"></i> <?= $c['signatory_count'] ?></span></td>
                            <td class="py-3 px-4 text-center" data-label="Students"><span class="badge badge-info"><i class="bi bi-mortarboard"></i> <?= $c['student_count'] ?></span></td>
                            <td class="py-3 px-4 text-muted" style="font-size:.85rem" data-label="Archived Since">
                                <?= date('M d, Y', strtotime($c['created_at'])) ?>
                            </td>
                            <td class="py-3 px-4 text-end" data-label="Actions">
                                <div class="action-cell">
                                    <a href="<?= BASE_URL ?>admin/clearances/detail?id=<?= $c['id'] ?>"
                                       class="btn btn-secondary btn-sm"><i class="bi bi-eye"></i> View</a>
                                    <form action="<?= BASE_URL ?>admin/clearances/unarchive" method="POST" style="margin:0;"
                                          onsubmit="return confirmAction(this, 'Restore this clearance to active?', 'Restore', 'btn-success')">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-arrow-counterclockwise"></i> Restore</button>
                                    </form>
                                    <form action="<?= BASE_URL ?>admin/clearances/delete" method="POST" style="margin:0;"
                                          onsubmit="return confirmAction(this, 'PERMANENTLY delete this clearance? All student data, flags, and statuses will be gone forever. This cannot be undone.', 'Permanently Delete', 'btn-danger')">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="redirect_to" value="admin/archived-clearances">
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
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
