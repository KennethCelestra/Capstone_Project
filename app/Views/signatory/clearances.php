<div class="page-header">
    <h2>Pending Clearance Requests</h2>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Course</th>
                <th>Year / Section</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($clearances)): ?>
                <tr>
                    <td colspan="5" class="text-center">No pending clearances.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($clearances as $c): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($c['student_number']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($c['student_name']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($c['course']) ?>
                        </td>
                        <td>
                            <?= $c['year_level'] ?> –
                            <?= htmlspecialchars($c['section']) ?>
                        </td>
                        <td>
                            <form action="<?= BASE_URL ?>signatory/clearances/sign" method="POST"
                                onsubmit="return confirm('Sign clearance for this student?')">
                                <input type="hidden" name="student_id" value="<?= $c['student_id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">Sign</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>