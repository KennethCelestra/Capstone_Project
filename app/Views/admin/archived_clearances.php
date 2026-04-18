<?php
// admin/archived_clearances.php
?>
<div class="page-header">
    <div>
        <a href="<?= BASE_URL ?>admin/clearances" class="back-link">← Active Clearances</a>
        <h2>🗄️ Archived Clearances</h2>
        <p class="text-muted">These clearances are archived and hidden from active use. All data is preserved.</p>
    </div>
</div>

<?php if (empty($clearances)): ?>
    <div class="empty-state">
        <div class="empty-icon">🗄️</div>
        <h3>No archived clearances</h3>
        <p>Clearances you archive will appear here. You can restore them at any time.</p>
        <a href="<?= BASE_URL ?>admin/clearances" class="btn btn-primary">← Back to Active Clearances</a>
    </div>
<?php else: ?>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Clearance Name</th>
                    <th>School Year</th>
                    <th>Signatories</th>
                    <th>Students</th>
                    <th>Archived Since</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clearances as $c): ?>
                    <tr class="row-archived">
                        <td>
                            <strong><?= htmlspecialchars($c['name']) ?></strong>
                            <?php if (!empty($c['description'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($c['description']) ?></small>
                            <?php endif; ?>
                            <br><span class="badge badge-archived">🗄️ Archived</span>
                        </td>
                        <td><?= htmlspecialchars($c['school_year']) ?></td>
                        <td><span class="badge badge-info"><?= $c['signatory_count'] ?></span></td>
                        <td><span class="badge badge-info"><?= $c['student_count'] ?></span></td>
                        <td class="text-muted" style="font-size:.85rem">
                            <?= date('M d, Y', strtotime($c['created_at'])) ?>
                        </td>
                        <td class="action-cell">
                            <!-- View detail (read-only while archived) -->
                            <a href="<?= BASE_URL ?>admin/clearances/detail?id=<?= $c['id'] ?>"
                               class="btn btn-secondary btn-sm">View</a>
                            <!-- Restore -->
                            <form action="<?= BASE_URL ?>admin/clearances/unarchive" method="POST" style="display:inline"
                                  onsubmit="return confirm('Restore this clearance to active?')">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">↩️ Restore</button>
                            </form>
                            <!-- Permanent delete -->
                            <form action="<?= BASE_URL ?>admin/clearances/delete" method="POST" style="display:inline"
                                  onsubmit="return confirm('PERMANENTLY delete this clearance? All student data, flags, and statuses will be gone forever. This cannot be undone.')">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">🗑️ Delete Forever</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<style>
.back-link {
    display: inline-block; color: var(--text-muted, #94a3b8);
    text-decoration: none; font-size: .875rem;
    margin-bottom: .4rem; transition: color .15s;
}
.back-link:hover { color: var(--text, #e2e8f0); }

.row-archived { opacity: .85; }

.badge-archived {
    background: rgba(148,163,184,.15);
    color: #94a3b8;
    border: 1px solid rgba(148,163,184,.3);
    padding: .15rem .5rem; border-radius: 999px;
    font-size: .72rem; font-weight: 600;
}

.btn-warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff; border: none;
    padding: .4rem .85rem; border-radius: 8px;
    font-weight: 600; cursor: pointer;
    transition: opacity .2s, transform .15s;
    font-size: .8rem; text-decoration: none;
    display: inline-flex; align-items: center; gap: .3rem;
}
.btn-warning:hover { opacity: .9; transform: translateY(-1px); }
</style>
