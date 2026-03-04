<div class="page-header">
    <h2>Manage Advisers</h2>
    <button class="btn btn-primary" onclick="document.getElementById('addAdviserModal').style.display='flex'">+ Add Adviser</button>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($advisers)): ?>
                <tr><td colspan="4" class="text-center">No advisers found.</td></tr>
            <?php else: ?>
                <?php foreach ($advisers as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['full_name']) ?></td>
                    <td><?= htmlspecialchars($a['email']) ?></td>
                    <td><?= htmlspecialchars($a['department']) ?></td>
                    <td>
                        <form action="<?= BASE_URL ?>admin/advisers/delete" method="POST" onsubmit="return confirm('Delete this adviser?')">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Adviser Modal -->
<div id="addAdviserModal" class="modal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add New Adviser</h3>
            <button onclick="document.getElementById('addAdviserModal').style.display='none'" class="close-btn">✕</button>
        </div>
        <form action="<?= BASE_URL ?>admin/advisers/add" method="POST" class="modal-form">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required placeholder="Prof. Juan Dela Cruz">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="adviser@school.edu">
            </div>
            <div class="form-group">
                <label>Department</label>
                <input type="text" name="department" required placeholder="College of Computing">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Password">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addAdviserModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Adviser</button>
            </div>
        </form>
    </div>
</div>
