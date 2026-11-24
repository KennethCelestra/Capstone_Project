<?php
$currentType = $activeType ?? 'regular';
$currentData = $currentType === 'exit' ? $exit : $regular;
?>

<div class="page-header mb-4" style="padding-bottom: 1.25rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2 style="font-size: 1.6rem; font-weight: 700; margin-bottom: .2rem;">Clearance Form Template Editor</h2>
        <p class="text-muted" style="margin: 0; font-size: .95rem;">Customize certificate wording, headings, and distribution notes for official clearance forms.</p>
    </div>
</div>

<!-- Tabs -->
<div class="tabs mb-4">
    <a href="<?= BASE_URL ?>admin/clearance-template?type=regular" class="tab-btn <?= $currentType === 'regular' ? 'active' : '' ?>" style="text-decoration:none;">
        <i class="bi bi-file-earmark-text me-1"></i> Regular Clearance Form
    </a>
    <a href="<?= BASE_URL ?>admin/clearance-template?type=exit" class="tab-btn <?= $currentType === 'exit' ? 'active' : '' ?>" style="text-decoration:none;">
        <i class="bi bi-box-arrow-right me-1"></i> Exit Clearance Form
    </a>
</div>

<div class="row" style="display:flex; flex-wrap:wrap; gap:1.5rem;">
    <!-- Form Editor Column -->
    <div style="flex: 1 1 520px;">
        <div class="card" style="background:var(--surface); border:1px solid var(--border); border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); padding:1.5rem;">
            <h4 style="font-weight:700; margin-bottom:1.25rem; font-size:1.1rem;">
                Editing: <?= $currentType === 'exit' ? 'Exit Clearance' : 'Regular Clearance' ?> Template
            </h4>

            <form action="<?= BASE_URL ?>admin/clearance-template/update" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                <input type="hidden" name="type" value="<?= htmlspecialchars($currentType) ?>">

                <div class="form-group mb-3">
                    <label class="form-label" style="font-weight:600;">Header Title <span class="required">*</span></label>
                    <input type="text" name="header_title" class="form-control"
                           value="<?= htmlspecialchars($currentData['header_title'] ?? '') ?>" required
                           id="tpl-header-input" oninput="updateLivePreview()">
                    <small class="text-muted">Title displayed in the header banner of the form.</small>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" style="font-weight:600;">Certification Body Paragraph <span class="required">*</span></label>
                    <textarea name="body_text" class="form-control" rows="5" required
                              id="tpl-body-input" oninput="updateLivePreview()"><?= htmlspecialchars($currentData['body_text'] ?? '') ?></textarea>
                    <small class="text-muted">The main certification paragraph text. Use dynamic placeholders below.</small>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label" style="font-weight:600;">Footer / Distribution Note</label>
                    <input type="text" name="footer_text" class="form-control"
                           value="<?= htmlspecialchars($currentData['footer_text'] ?? '') ?>"
                           id="tpl-footer-input" oninput="updateLivePreview()">
                    <small class="text-muted">Footnote or copy distribution note printed at bottom of form.</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Save Template Changes
                </button>
            </form>
        </div>

        <!-- Placeholders Helper -->
        <div class="card mt-3" style="background:var(--surface2); border:1px dashed var(--border); border-radius:10px; padding:1.2rem;">
            <h6 style="font-weight:700; margin-bottom:0.5rem; font-size:0.9rem;">
                <i class="bi bi-code-slash me-1"></i> Available Dynamic Placeholders
            </h6>
            <p class="text-muted small mb-2">Insert these placeholders in your body text; they are automatically replaced with real student data:</p>
            <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
                <code style="background:var(--surface); padding:3px 8px; border-radius:4px; border:1px solid var(--border); cursor:pointer;" onclick="insertTag('{student_name}')" title="Click to insert">{student_name}</code>
                <code style="background:var(--surface); padding:3px 8px; border-radius:4px; border:1px solid var(--border); cursor:pointer;" onclick="insertTag('{student_id}')" title="Click to insert">{student_id}</code>
                <code style="background:var(--surface); padding:3px 8px; border-radius:4px; border:1px solid var(--border); cursor:pointer;" onclick="insertTag('{college}')" title="Click to insert">{college}</code>
                <code style="background:var(--surface); padding:3px 8px; border-radius:4px; border:1px solid var(--border); cursor:pointer;" onclick="insertTag('{course}')" title="Click to insert">{course}</code>
                <code style="background:var(--surface); padding:3px 8px; border-radius:4px; border:1px solid var(--border); cursor:pointer;" onclick="insertTag('{semester}')" title="Click to insert">{semester}</code>
                <code style="background:var(--surface); padding:3px 8px; border-radius:4px; border:1px solid var(--border); cursor:pointer;" onclick="insertTag('{academic_year}')" title="Click to insert">{academic_year}</code>
            </div>
        </div>
    </div>

    <!-- Live Preview Column -->
    <div style="flex: 1 1 420px;">
        <div class="card" style="background:#ffffff; border:1px solid var(--border); border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); padding:1.5rem; position:sticky; top:1.5rem;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span style="font-size:0.78rem; text-transform:uppercase; font-weight:700; letter-spacing:0.06em; color:var(--text-muted);">
                    <i class="bi bi-eye me-1"></i> Sample Preview
                </span>
                <span class="badge bg-secondary" style="font-size:0.72rem;">Live Wording</span>
            </div>

            <!-- Simulated Form Box -->
            <div style="border:1px solid #000; padding:1.25rem; background:#fff; font-family:'Times New Roman', Times, serif; color:#000;">
                <div style="text-align:center; margin-bottom:1rem; border-bottom:1px solid #000; padding-bottom:0.5rem;">
                    <h4 id="preview-header" style="font-size:1.15rem; font-weight:bold; margin:0; letter-spacing:0.04em;">
                        <?= htmlspecialchars($currentData['header_title'] ?? 'CLEARANCE FORM') ?>
                    </h4>
                </div>
                <p id="preview-body" style="font-size:0.92rem; line-height:1.6; text-indent:1.5rem; text-align:justify; margin-bottom:1.5rem;">
                    <?= nl2br(htmlspecialchars(str_replace(
                        ['{student_name}', '{student_id}', '{college}', '{course}', '{semester}', '{academic_year}'],
                        ['JUAN DELA CRUZ', '2023-01234', 'CCI', 'BSIT', '2nd', '2025-2026'],
                        $currentData['body_text'] ?? ''
                    ))) ?>
                </p>
                <div style="display:flex; justify-content:space-around; text-align:center; margin-top:2rem; font-size:0.85rem;">
                    <div>
                        <div style="border-top:1px solid #000; width:120px; padding-top:2px;">Signatory</div>
                    </div>
                    <div>
                        <div style="border-top:1px solid #000; width:120px; padding-top:2px;">Dean</div>
                    </div>
                </div>
                <div style="margin-top:1.5rem; border-top:1px dashed #777; padding-top:0.35rem; font-size:0.75rem; color:#444;">
                    <span id="preview-footer"><?= htmlspecialchars($currentData['footer_text'] ?? '') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateLivePreview() {
    const h = document.getElementById('tpl-header-input').value;
    const b = document.getElementById('tpl-body-input').value;
    const f = document.getElementById('tpl-footer-input').value;

    document.getElementById('preview-header').textContent = h || 'CLEARANCE FORM';
    document.getElementById('preview-footer').textContent = f || '';

    // Sample replace
    const sample = b
        .replace(/\{student_name\}/g, 'JUAN DELA CRUZ')
        .replace(/\{student_id\}/g, '2023-01234')
        .replace(/\{college\}/g, 'CCI')
        .replace(/\{course\}/g, 'BSIT')
        .replace(/\{semester\}/g, '2nd')
        .replace(/\{academic_year\}/g, '2025-2026');

    document.getElementById('preview-body').textContent = sample;
}

function insertTag(tag) {
    const textarea = document.getElementById('tpl-body-input');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + tag + text.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + tag.length;
    updateLivePreview();
}
</script>
