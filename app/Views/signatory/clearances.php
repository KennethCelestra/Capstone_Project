<div class="page-header">
    <h2>Sign Clearances</h2>
</div>

<?php if (empty($clearances)): ?>
    <div class="empty-state">
        <div class="empty-icon">✍️</div>
        <h3>No clearances assigned</h3>
        <p>You haven't been assigned to any clearance yet. Contact the administrator.</p>
    </div>
<?php else: ?>
    <?php foreach ($clearances as $c): ?>
        <div class="table-container" style="margin-bottom:2rem;">

            <!-- Clearance header bar -->
            <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--border);
                        display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong><?= htmlspecialchars($c['clearance_name']) ?></strong>
                    <?php if (!empty($c['school_year'])): ?>
                        <span class="text-muted" style="font-size:.85rem; margin-left:.75rem">
                            <?= htmlspecialchars($c['school_year']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div style="display:flex;gap:.5rem;align-items:center;">
                    <span class="badge badge-warning"><?= (int)$c['total_students'] - (int)$c['signed_count'] ?> pending</span>
                    <span class="badge badge-success"><?= (int)$c['signed_count'] ?> signed</span>
                </div>
            </div>

            <?php if (empty($c['students'])): ?>
                <p class="text-muted text-center" style="padding:1.5rem">No students enrolled in this clearance.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Course</th>
                            <th>Year / Section</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($c['students'] as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['student_number']) ?></td>
                                <td><?= htmlspecialchars($s['full_name']) ?></td>
                                <td><?= htmlspecialchars($s['course']) ?></td>
                                <td><?= $s['year_level'] ?> – <?= htmlspecialchars($s['section']) ?></td>
                                <td>
                                    <?php if ($s['status'] === 'signed'): ?>
                                        <span class="badge badge-success">✓ Signed</span>
                                        <?php if ($s['signed_at']): ?>
                                            <br><small class="text-muted"><?= date('M d, Y', strtotime($s['signed_at'])) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($s['status'] !== 'signed'): ?>
                                        <form action="<?= BASE_URL ?>signatory/clearances/sign" method="POST"
                                              onsubmit="return confirm('Sign clearance for <?= htmlspecialchars(addslashes($s['full_name'])) ?>?')">
                                            <input type="hidden" name="clearance_id" value="<?= $c['clearance_id'] ?>">
                                            <input type="hidden" name="student_id"   value="<?= $s['id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm">✍️ Sign</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:.8rem">Already signed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>