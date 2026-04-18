<!-- ===== Signatory: Confirm & Send Deficiency Emails ===== -->
<?php $cid = (int)($_GET['cid'] ?? 0); ?>
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2>🚩 Confirm Deficiency Emails</h2>
        <p class="text-muted">Review all flagged students below. Click <strong>Send Emails</strong> to notify them.</p>
    </div>
    <a href="<?= BASE_URL ?>signatory/clearances<?= $cid > 0 ? '?cid='.$cid : '' ?>" class="btn btn-secondary">← Back to Students</a>
</div>

<?php if (empty($flagged)): ?>
    <div class="empty-state">
        <div class="empty-icon">✅</div>
        <h3>No flagged students</h3>
        <p>You have no students currently flagged for deficiencies. Go back and flag any students who have outstanding requirements.</p>
        <a href="<?= BASE_URL ?>signatory/clearances" class="btn btn-primary" style="margin-top:.75rem;">View Student List</a>
    </div>
<?php else: ?>

    <div class="table-container" style="margin-bottom:2rem;">
        <div class="clearance-header-bar">
            <div>
                <strong><?= count($flagged) ?> student(s) will receive a deficiency email</strong>
            </div>
            <span class="badge badge-danger">🚩 <?= count($flagged) ?> flagged</span>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Course / Year</th>
                    <th>Clearance</th>
                    <th>Deficiency Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($flagged as $f): ?>
                    <tr class="row-flagged">
                        <td><?= htmlspecialchars($f['student_number']) ?></td>
                        <td><strong><?= htmlspecialchars($f['full_name']) ?></strong></td>
                        <td><?= htmlspecialchars($f['email']) ?></td>
                        <td><?= htmlspecialchars($f['course']) ?> / Year <?= $f['year_level'] ?></td>
                        <td><?= htmlspecialchars($f['clearance_name']) ?></td>
                        <td>
                            <div class="flag-note-full"><?= nl2br(htmlspecialchars($f['flag_note'])) ?></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="confirm-actions">
        <div class="confirm-warning">
            <span>⚠️</span>
            <div>
                <strong>Sending emails cannot be undone.</strong>
                Each flagged student above will receive an email at their registered address describing the deficiency.
            </div>
        </div>
        <form action="<?= BASE_URL ?>signatory/confirm/submit" method="POST"
              onsubmit="return confirm('Send deficiency emails to all <?= count($flagged) ?> flagged student(s)?')">
            <input type="hidden" name="clearance_id" value="<?= $cid ?>">
            <button type="submit" class="btn btn-danger btn-lg" id="send-emails-btn">
                📧 Send Deficiency Emails to All Flagged Students
            </button>
        </form>
    </div>

<?php endif; ?>

<style>
.clearance-header-bar {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border, #334155);
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: .5rem;
}
.row-flagged { background: rgba(239,68,68,.06) !important; }
.flag-note-full { font-size: .875rem; color: var(--text-muted, #94a3b8); font-style: italic; max-width: 320px; }

.confirm-actions {
    display: flex; flex-direction: column; gap: 1.25rem;
    padding: 1.5rem;
    background: var(--card-bg, #1e293b);
    border: 1px solid var(--border, #334155);
    border-radius: 12px;
}
.confirm-warning {
    display: flex; align-items: flex-start; gap: 1rem;
    padding: 1rem 1.25rem;
    background: rgba(239,68,68,.08);
    border: 1px solid rgba(239,68,68,.3);
    border-radius: 8px;
    color: var(--text, #e2e8f0);
    font-size: .9rem;
    line-height: 1.5;
}
.confirm-warning span { font-size: 1.4rem; flex-shrink:0; }
.btn-lg { padding: .75rem 1.75rem; font-size: 1rem; }
.btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    border: none; border-radius: 8px;
    font-weight: 600; cursor: pointer;
    transition: opacity .2s, transform .15s;
    text-decoration: none;
    display: inline-flex; align-items: center; gap: .5rem;
}
.btn-danger:hover { opacity:.9; transform:translateY(-1px); }
</style>

<script>
// Prevent double submit
document.getElementById('send-emails-btn')?.closest('form')?.addEventListener('submit', function() {
    const btn = document.getElementById('send-emails-btn');
    if (btn) { btn.disabled = true; btn.textContent = '📧 Sending…'; }
});
</script>
