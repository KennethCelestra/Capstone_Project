<?php
/**
 * Shared "Student Logs" modal + JS.
 * Expects: $logsUrl (endpoint URL, without query string).
 * Open with: openStudentLogs(clearanceId, studentId, 'Student Name')
 */
?>
<div id="studentLogsModal" class="modal" style="display:none;">
    <div class="modal-box" style="max-width:560px;">
        <div class="modal-header">
            <h3 id="studentLogsTitle">Clearance Logs</h3>
            <button type="button" class="close-btn" onclick="closeStudentLogs()">✕</button>
        </div>
        <div id="studentLogsBody" style="padding:1.25rem 1.5rem; max-height:60vh; overflow-y:auto;"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeStudentLogs()">Close</button>
        </div>
    </div>
</div>

<script>
function closeStudentLogs() {
    document.getElementById('studentLogsModal').style.display = 'none';
}

function openStudentLogs(clearanceId, studentId, studentName) {
    const modal = document.getElementById('studentLogsModal');
    const body  = document.getElementById('studentLogsBody');
    document.getElementById('studentLogsTitle').textContent = 'Logs — ' + studentName;
    body.innerHTML = '<p class="text-muted">Loading…</p>';
    modal.style.display = 'flex';

    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const url = <?= json_encode($logsUrl) ?> + '?clearance_id=' + encodeURIComponent(clearanceId) + '&student_id=' + encodeURIComponent(studentId);

    fetch(url, {credentials: 'same-origin'})
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
            const logs = data.logs || [];
            if (!logs.length) {
                body.innerHTML = '<p class="text-muted" style="text-align:center;margin:1rem 0;">No activity recorded yet.</p>';
                return;
            }
            body.innerHTML = logs.map(l => {
                const flagged = l.action === 'flagged';
                const color   = flagged ? 'var(--danger, #dc3545)' : 'var(--success, #198754)';
                const d       = new Date(l.created_at.replace(' ', 'T'));
                const when    = isNaN(d) ? esc(l.created_at)
                    : d.toLocaleDateString(undefined, {year:'numeric', month:'short', day:'numeric'}) + ' ' +
                      d.toLocaleTimeString(undefined, {hour:'numeric', minute:'2-digit', second:'2-digit'});
                return '<div style="border-left:4px solid ' + color + ';padding:.5rem .85rem;margin-bottom:.85rem;background:var(--surface2,#f8f9fa);border-radius:0 8px 8px 0;">' +
                    '<div style="display:flex;justify-content:space-between;gap:.5rem;flex-wrap:wrap;">' +
                        '<strong style="color:' + color + ';">' + (flagged ? 'Flagged' : 'Cleared') + '</strong>' +
                        '<span class="text-muted" style="font-size:.8rem;">' + when + '</span>' +
                    '</div>' +
                    '<div style="font-size:.9rem;">' + esc(l.signatory_name) + ' · <span class="text-muted">' + esc(l.office) + '</span></div>' +
                    (flagged && l.flag_note ? '<div style="font-size:.85rem;margin-top:.35rem;white-space:pre-wrap;">' + esc(l.flag_note) + '</div>' : '') +
                '</div>';
            }).join('');
        })
        .catch(() => { body.innerHTML = '<p style="color:var(--danger,#dc3545);">Unable to load logs.</p>'; });
}
</script>
