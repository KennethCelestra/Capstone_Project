<?php
/**
 * Student-side extras for the (public, token-secured) clearance form pages.
 * Included by clearance_form.php and exit_clearance_form.php, right after the print bar.
 *
 * Expects: $progress = ['cleared' => int, 'total' => int, 'flagged' => int]
 *          $logsUrl  = full URL of the JSON logs endpoint (already contains the token)
 *
 * Everything here is hidden when printing.
 */
$stCleared = (int) ($progress['cleared'] ?? 0);
$stTotal   = (int) ($progress['total']   ?? 0);
$stFlagged = (int) ($progress['flagged'] ?? 0);
$stDone    = $stTotal > 0 && $stCleared === $stTotal;
$stPct     = $stTotal > 0 ? (int) round($stCleared / $stTotal * 100) : 0;

if ($stDone) {
    $stTitle = 'Fully cleared';
    $stColor = '#198754';
    $stBg    = '#e8f5ee';
} elseif ($stFlagged > 0) {
    $stTitle = 'Action needed';
    $stColor = '#c0392b';
    $stBg    = '#fdecea';
} else {
    $stTitle = 'In progress';
    $stColor = '#b7791f';
    $stBg    = '#fff7e0';
}
?>
<style>
    .st-tools { max-width: 800px; margin: 0 auto 15px; font-family: 'Segoe UI', Arial, sans-serif; }
    .st-panel { border-radius: 8px; padding: 12px 16px; border-left: 5px solid <?= $stColor ?>; background: <?= $stBg ?>; }
    .st-panel strong.st-title { color: <?= $stColor ?>; }
    .st-panel .st-sub { font-size: 13px; color: #444; margin-top: 2px; }
    .st-bar { height: 8px; background: rgba(0,0,0,.08); border-radius: 4px; margin-top: 8px; overflow: hidden; }
    .st-bar span { display: block; height: 100%; width: <?= $stPct ?>%; background: <?= $stColor ?>; transition: width .4s; }

    .st-modal { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: none; align-items: center; justify-content: center; z-index: 9999; padding: 16px; }
    .st-modal-box { background: #fff; border-radius: 10px; width: 100%; max-width: 560px; max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 10px 40px rgba(0,0,0,.25); }
    .st-modal-head { display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; border-bottom: 1px solid #e5e5e5; }
    .st-modal-head h3 { font-size: 1.05rem; margin: 0; }
    .st-modal-close { background: none; border: none; font-size: 18px; cursor: pointer; color: #666; }
    .st-modal-body { padding: 16px 18px; overflow-y: auto; }
    .st-log { border-left: 4px solid #999; padding: 8px 14px; margin-bottom: 12px; background: #f8f9fa; border-radius: 0 8px 8px 0; }
    .st-log-top { display: flex; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
    .st-log small { color: #777; }

    @media print { .st-tools, .st-modal { display: none !important; } }
</style>

<div class="st-tools">
    <div class="st-panel">
        <strong class="st-title"><?= htmlspecialchars($stTitle) ?></strong>
        <div class="st-sub">
            <?= $stCleared ?> of <?= $stTotal ?> office(s) signed<?php if ($stFlagged > 0): ?> &middot; <?= $stFlagged ?> flagged &mdash; open <em>View Logs</em> to see the reason<?php endif; ?>.
            Offices that haven't acted yet are left blank on the form.
        </div>
        <div class="st-bar"><span></span></div>
    </div>
</div>

<div id="stLogsModal" class="st-modal">
    <div class="st-modal-box">
        <div class="st-modal-head">
            <h3>Clearance Logs</h3>
            <button type="button" class="st-modal-close" onclick="closeStLogs()">&#10005;</button>
        </div>
        <div id="stLogsBody" class="st-modal-body"></div>
    </div>
</div>

<script>
function closeStLogs() { document.getElementById('stLogsModal').style.display = 'none'; }

function openStLogs() {
    const modal = document.getElementById('stLogsModal');
    const body  = document.getElementById('stLogsBody');
    body.innerHTML = '<p style="color:#777;">Loading…</p>';
    modal.style.display = 'flex';

    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    fetch(<?= json_encode($logsUrl) ?>)
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
            const logs = data.logs || [];
            if (!logs.length) {
                body.innerHTML = '<p style="color:#777;text-align:center;margin:12px 0;">No activity yet. Updates will appear here when an office clears or flags you.</p>';
                return;
            }
            body.innerHTML = logs.map(l => {
                const flagged = l.action === 'flagged';
                const color   = flagged ? '#c0392b' : '#198754';
                const d       = new Date(String(l.created_at).replace(' ', 'T'));
                const when    = isNaN(d) ? esc(l.created_at)
                    : d.toLocaleDateString(undefined, {year:'numeric', month:'short', day:'numeric'}) + ' ' +
                      d.toLocaleTimeString(undefined, {hour:'numeric', minute:'2-digit'});
                return '<div class="st-log" style="border-left-color:' + color + ';">' +
                    '<div class="st-log-top"><strong style="color:' + color + ';">' + (flagged ? 'Flagged' : 'Cleared') + '</strong><small>' + when + '</small></div>' +
                    '<div>' + esc(l.signatory_name) + ' &middot; <small>' + esc(l.office) + '</small></div>' +
                    (flagged && l.flag_note ? '<div style="margin-top:6px;font-size:13px;white-space:pre-wrap;">' + esc(l.flag_note) + '</div>' : '') +
                '</div>';
            }).join('');
        })
        .catch(() => { body.innerHTML = '<p style="color:#c0392b;">Unable to load logs. Please try again.</p>'; });
}

document.getElementById('stLogsModal').addEventListener('click', e => { if (e.target.id === 'stLogsModal') closeStLogs(); });
</script>
