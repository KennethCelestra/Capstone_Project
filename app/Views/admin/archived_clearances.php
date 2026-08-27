<div class="page-header mb-4 d-flex justify-content-between align-items-center" style="padding-bottom: 1.25rem; border-bottom: 1px solid var(--border);">
    <div>
        <a href="<?= BASE_URL ?>admin/clearances" class="back-link text-decoration-none mb-2 d-inline-block" style="font-size: .9rem; color: var(--text-muted);"><i class="bi bi-arrow-left"></i> Active Clearances</a>
        <h2 style="font-size: 1.6rem; font-weight: 700; margin-bottom: .2rem;">Archived Clearances</h2>
        <p class="text-muted" style="margin: 0; font-size: .95rem;">These clearances are archived and hidden from active use. All data is preserved.</p>
    </div>
</div>

<?php if (empty($clearances)): ?>
    <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 3rem; text-align: center;">
        <i class="bi bi-archive" style="font-size: 2.5rem; color: var(--text-muted); opacity: .4;"></i>
        <h3 style="margin-top: .75rem; font-size: 1.1rem;">No archived clearances</h3>
        <p class="text-muted" style="font-size: .9rem;">Clearances you archive will appear here. You can restore them at any time.</p>
        <a href="<?= BASE_URL ?>admin/clearances" class="btn btn-primary mt-3"><i class="bi bi-arrow-left"></i> Back to Active Clearances</a>
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
                        <tr class="row-archived border-bottom" style="transition: background .15s;" onmouseenter="this.style.background='var(--surface2)'" onmouseleave="this.style.background=''">
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
                                       class="btn btn-secondary btn-sm">View</a>
                                    <form action="<?= BASE_URL ?>admin/clearances/unarchive" method="POST" style="margin:0; display:inline;"
                                          onsubmit="return confirmAction(this, 'Restore this clearance to active?', 'Restore', 'btn-success')">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Restore</button>
                                    </form>
                                    <form action="<?= BASE_URL ?>admin/clearances/delete" method="POST" style="margin:0; display:inline;"
                                          onsubmit="return confirmAction(this, 'PERMANENTLY delete this clearance? All student data, flags, and statuses will be gone forever. This cannot be undone.', 'Permanently Delete', 'btn-danger')">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="redirect_to" value="admin/archived-clearances">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
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
