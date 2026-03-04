<div class="page-header">
    <h2>My Students' Clearance Status</h2>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Course</th>
                <th>Year / Section</th>
                <th>Signatures</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($clearances)): ?>
                <tr><td colspan="6" class="text-center">No students assigned to you.</td></tr>
            <?php else: ?>
                <?php foreach ($clearances as $c): ?>
                <?php
                    $pct = ($c['total'] > 0) ? round(($c['signed'] / $c['total']) * 100) : 0;
                    $isCleared = $c['signed'] == $c['total'] && $c['total'] > 0;
                ?>
                <tr>
                    <td><?= htmlspecialchars($c['student_number']) ?></td>
                    <td><?= htmlspecialchars($c['student_name']) ?></td>
                    <td><?= htmlspecialchars($c['course']) ?></td>
                    <td><?= $c['year_level'] ?> – <?= htmlspecialchars($c['section']) ?></td>
                    <td>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar" style="width:<?= $pct ?>%"></div>
                        </div>
                        <small><?= $c['signed'] ?>/<?= $c['total'] ?></small>
                    </td>
                    <td>
                        <span class="badge <?= $isCleared ? 'badge-success' : 'badge-warning' ?>">
                            <?= $isCleared ? 'Cleared' : 'Pending' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
