<div class="page-header">
    <h2>Clearance Overview</h2>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Full Name</th>
                <th>Course</th>
                <th>Year / Section</th>
                <th>Adviser</th>
                <th>Progress</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($clearances)): ?>
                <tr>
                    <td colspan="7" class="text-center">No clearance records found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($clearances as $c): ?>
                    <?php
                    $pct = ($c['total'] > 0) ? round(($c['signed'] / $c['total']) * 100) : 0;
                    $statusClass = $c['clearance_status'] === 'cleared' ? 'badge-success' : 'badge-warning';
                    ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($c['student_number']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($c['full_name']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($c['course']) ?>
                        </td>
                        <td>
                            <?= $c['year_level'] ?> –
                            <?= htmlspecialchars($c['section']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($c['adviser_name'] ?? 'N/A') ?>
                        </td>
                        <td>
                            <div class="progress-bar-wrap">
                                <div class="progress-bar" style="width:<?= $pct ?>%"></div>
                            </div>
                            <small>
                                <?= $c['signed'] ?>/
                                <?= $c['total'] ?> signed
                            </small>
                        </td>
                        <td><span class="badge <?= $statusClass ?>">
                                <?= ucfirst($c['clearance_status'] ?? 'Pending') ?>
                            </span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>