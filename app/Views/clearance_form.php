<?php
/**
 * Standalone Printable Regular Semestral Clearance Form (QF-VPAA-08)
 * Variables available: $student, $clearance, $signatories, $semester, $yearLabel
 */

$studentFullName = htmlspecialchars(trim($student['first_name'] . ' ' . $student['last_name']));
$studentNumber   = htmlspecialchars($student['student_number'] ?? $student['student_id'] ?? '');
$college         = htmlspecialchars($student['college'] ?? '');
$course          = htmlspecialchars($student['course'] ?? '');
$section         = htmlspecialchars($student['section'] ?? '');
$schoolYear      = htmlspecialchars($clearance['school_year'] ?? '');
$clearanceName   = htmlspecialchars($clearance['name'] ?? '');
$logoUrl         = BASE_URL . 'css/logo.png';

// Department label: Use course or college
$department = $course ?: $college;

// Format Curriculum Year & Section nicely (e.g. "BSIT 4-A" or "4th Year - A")
$currYearSection = trim(($course ? $course . ' ' : '') . ($student['year_level'] ?? '') . ($section ? '-' . $section : ''));
if (empty($currYearSection)) {
    $currYearSection = $yearLabel . ($section ? ' — ' . $section : '');
}

/**
 * Regular semestral signatories grid (QF-VPAA-08 style: 2-column rows of single signature slots, last slot centered if odd)
 */
function renderRegularSignatories(array $signatories): string {
    if (empty($signatories)) {
        return '<p style="text-align:center;font-style:italic;padding:8px 0;">No signatories assigned.</p>';
    }

    $total = count($signatories);
    $html = '<div class="reg-sig-section">';

    for ($i = 0; $i < $total; $i += 2) {
        if ($i + 1 < $total) {
            // Pair of 2
            $html .= '<div class="reg-sig-row">';
            $html .= renderRegularSigSlot($signatories[$i]);
            $html .= renderRegularSigSlot($signatories[$i + 1]);
            $html .= '</div>';
        } else {
            // Single remaining signatory (centered)
            $html .= '<div class="reg-sig-row-center">';
            $html .= renderRegularSigSlot($signatories[$i], true);
            $html .= '</div>';
        }
    }

    $html .= '</div>';
    return $html;
}

function renderRegularSigSlot(array $sig, bool $isCentered = false): string {
    $office   = htmlspecialchars($sig['office'] ?? 'Signatory');
    $signed   = (!empty($sig) && ($sig['status'] ?? '') === 'cleared');
    $signedAt = ($signed && !empty($sig['signed_at'])) ? date('M j, Y', strtotime($sig['signed_at'])) : '';

    $html = '<div class="reg-sig-item ' . ($isCentered ? 'reg-sig-centered' : '') . '">';
    $html .= '  <div class="reg-sig-status">' . ($signed ? '<span class="signed-badge">SIGNED</span>' : '&nbsp;') . '</div>';
    $html .= '  <div class="reg-sig-date-text">' . ($signedAt ? '<span class="date-badge">' . htmlspecialchars($signedAt) . '</span>' : '&nbsp;') . '</div>';
    $html .= '  <div class="reg-sig-line"></div>';
    $html .= '  <div class="reg-sig-label">' . $office . '</div>';
    $html .= '</div>';

    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student's Semestral Clearance (QF-VPAA-08) — <?= $studentFullName ?></title>
    <style>
        /* ── Reset & Base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            background: #eef1f5;
            color: #000;
            padding: 15px 0;
            line-height: 1.25;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            overflow-x: hidden;
        }

        .page-wrapper {
            width: 100%;
            max-width: 840px;
            margin: 0 auto;
            overflow: hidden;
            padding: 0 8px;
        }

        .print-bar {
            text-align: center;
            margin-bottom: 15px;
        }
        .print-bar button {
            background: #1a56a0;
            color: #fff;
            border: none;
            padding: 10px 28px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            letter-spacing: .4px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            transition: background .2s;
        }
        .print-bar button:hover { background: #154a8a; }

        .sheet {
            background: #fff;
            padding: 14px 20px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            border: 1px solid #ccc;
            width: 800px;
            margin: 0 auto;
            transform-origin: top left;
        }

        .form-copy {
            padding: 2px 4px;
        }

        /* ── Header Box ── */
        .form-header-box {
            display: flex;
            border: 1px solid #000;
            margin-bottom: 6px;
        }
        .header-logo-cell {
            width: 72px;
            min-width: 72px;
            border-right: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
        }
        .header-logo-cell img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .header-center-cell {
            flex: 1;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #000;
            text-align: center;
        }
        .header-univ-info {
            padding: 4px 6px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            flex: 1;
        }
        .univ-tagline {
            font-size: 7.5pt;
            line-height: 1.2;
        }
        .univ-name {
            font-size: 9.5pt;
            font-weight: bold;
            line-height: 1.2;
            letter-spacing: .2px;
        }
        .univ-location {
            font-size: 7.5pt;
            line-height: 1.2;
        }
        .header-title-bar {
            border-top: 1px solid #000;
            padding: 3px 0;
            background: #fff;
        }
        .header-title-bar h1 {
            font-size: 10.5pt;
            font-weight: bold;
            letter-spacing: .6px;
            margin: 0;
        }

        .header-meta-table {
            width: 220px;
            min-width: 220px;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .header-meta-table td {
            padding: 2px 4px;
            vertical-align: middle;
            border-bottom: 1px solid #000;
        }
        .header-meta-table tr:last-child td {
            border-bottom: none;
        }
        .meta-lbl {
            font-weight: bold;
            width: 82px;
            border-right: 1px solid #000;
            white-space: nowrap;
        }
        .meta-val {
            white-space: nowrap;
        }

        /* ── Semester / School Year Line ── */
        .sem-line {
            text-align: center;
            font-size: 9.5pt;
            margin: 4px 0 6px;
        }
        .underline-box {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 80px;
            text-align: center;
            font-weight: bold;
            padding: 0 4px;
        }

        /* ── Student Info List (QF-VPAA-08 2-Column Exact Layout) ── */
        .student-info-block-reg {
            margin: 6px 0 8px;
            font-size: 9.5pt;
        }
        .info-row-2col {
            display: flex;
            gap: 20px;
            margin-bottom: 4px;
            align-items: baseline;
        }
        .info-item-full {
            display: flex;
            align-items: baseline;
            width: 100%;
        }
        .info-item-left {
            display: flex;
            align-items: baseline;
            flex: 1.1;
        }
        .info-item-right {
            display: flex;
            align-items: baseline;
            flex: 1;
        }
        .info-lbl-inline {
            font-size: 9.5pt;
            white-space: nowrap;
            margin-right: 4px;
        }
        .info-val-inline {
            border-bottom: 1px solid #000;
            font-weight: bold;
            padding-left: 4px;
            min-height: 14px;
            font-size: 9.5pt;
        }

        /* ── Body Certification ── */
        .cert-body {
            font-size: 9.5pt;
            line-height: 1.5;
            margin-bottom: 10px;
        }
        .cert-indent {
            text-indent: 32px;
            margin-top: 4px;
        }

        /* ── Regular Clearance Signatories Grid (QF-VPAA-08) ── */
        .reg-sig-section {
            margin: 10px 0 6px;
        }
        .reg-sig-row {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            margin-bottom: 12px;
        }
        .reg-sig-row-center {
            display: flex;
            justify-content: center;
            margin-bottom: 12px;
        }
        .reg-sig-item {
            width: 220px;
            text-align: center;
        }
        .reg-sig-status {
            min-height: 14px;
            font-size: 8.5pt;
            font-weight: bold;
        }
        .reg-sig-date-text {
            font-size: 7.5pt;
            color: #333;
            min-height: 12px;
        }
        .signed-badge {
            display: inline-block;
            font-size: 8.5pt;
            font-weight: bold;
            letter-spacing: .3px;
            color: #000;
        }
        .date-badge {
            font-size: 8pt;
            color: #000;
        }
        .reg-sig-line {
            border-top: 1px solid #000;
            margin: 1px 0 2px;
        }
        .reg-sig-label {
            font-size: 9pt;
        }

        /* ── Distributions ── */
        .dist-block {
            margin-top: 4px;
            font-size: 8.5pt;
            line-height: 1.25;
        }
        .dist-block em {
            font-style: italic;
            font-weight: bold;
        }
        .dist-block p {
            margin-left: 14px;
        }

        /* ── Cut Line ── */
        .cut-line {
            text-align: center;
            border-top: 1px dashed #666;
            margin: 8px 0 8px;
            padding-top: 3px;
            font-size: 8pt;
            color: #555;
            letter-spacing: 2px;
        }

        /* ── Print Setup (Fits 2 copies on exact 1 A4 Page) ── */
        @media print {
            body {
                background: #fff;
                padding: 0;
                font-size: 9pt;
                overflow: visible !important;
            }
            .print-bar { display: none !important; }
            .page-wrapper { max-width: 100% !important; min-width: 0 !important; padding: 0 !important; overflow: visible !important; height: auto !important; }
            .sheet {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                min-width: 0 !important;
                width: 100% !important;
                transform: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 8mm 12mm;
            }
            .form-copy {
                page-break-inside: avoid;
            }
            .cut-line {
                page-break-inside: avoid;
                margin: 6px 0 6px;
            }
        }
    </style>
</head>
<body>

<div class="page-wrapper">

    <!-- Print Button (Hidden on Print) -->
    <div class="print-bar">
        <button onclick="window.print()">🖨️ Print Semestral Clearance Form</button>
    </div>

    <div class="sheet">

        <?php for ($copy = 1; $copy <= 2; $copy++): ?>
        <div class="form-copy">

            <!-- ══ HEADER BOX ══ -->
            <div class="form-header-box">
                <!-- Logo -->
                <div class="header-logo-cell">
                    <img src="<?= $logoUrl ?>" alt="ISAT U Logo" onerror="this.style.display='none'">
                </div>

                <!-- Center Univ Info & Title -->
                <div class="header-center-cell">
                    <div class="header-univ-info">
                        <div class="univ-tagline">Republic of the Philippines</div>
                        <div class="univ-name">ILOILO SCIENCE AND TECHNOLOGY UNIVERSITY</div>
                        <div class="univ-location">La Paz, Iloilo City</div>
                    </div>
                    <div class="header-title-bar">
                        <h1>STUDENT'S SEMESTRAL CLEARANCE</h1>
                    </div>
                </div>

                <!-- Right Meta Table -->
                <table class="header-meta-table">
                    <tr>
                        <td class="meta-lbl">Department:</td>
                        <td class="meta-val">VP FOR ACADEMIC AFFAIRS</td>
                    </tr>
                    <tr>
                        <td class="meta-lbl">Document Code:</td>
                        <td class="meta-val">QF-VPAA-08</td>
                    </tr>
                    <tr>
                        <td class="meta-lbl">Rev. No.:</td>
                        <td class="meta-val">02</td>
                    </tr>
                    <tr>
                        <td class="meta-lbl">Effective Date:</td>
                        <td class="meta-val">July 15, 2015</td>
                    </tr>
                </table>
            </div>

            <!-- ══ SEMESTER / SY LINE ══ -->
            <div class="sem-line">
                <span class="underline-box" style="min-width: 90px;"><?= htmlspecialchars($semester) ?></span>
                &nbsp;Semester/Summer&nbsp;
                <span class="underline-box" style="min-width: 50px;">&nbsp;&nbsp;&nbsp;&nbsp;</span>
                ,&nbsp;SY&nbsp;
                <span class="underline-box" style="min-width: 100px;"><?= $schoolYear ?></span>
            </div>

            <!-- ══ STUDENT INFO (QF-VPAA-08 2-Column Exact Layout) ══ -->
            <div class="student-info-block-reg">
                <div class="info-row-2col">
                    <div class="info-item-full">
                        <span class="info-lbl-inline">Student ID No.:</span>
                        <span class="info-val-inline" style="min-width: 220px;"><?= $studentNumber ?></span>
                    </div>
                </div>
                <div class="info-row-2col">
                    <div class="info-item-left">
                        <span class="info-lbl-inline">Name of Student:</span>
                        <span class="info-val-inline" style="flex:1;"><?= $studentFullName ?></span>
                    </div>
                    <div class="info-item-right">
                        <span class="info-lbl-inline">Curriculum Year &amp; Section:</span>
                        <span class="info-val-inline" style="flex:1;"><?= htmlspecialchars($currYearSection) ?></span>
                    </div>
                </div>
                <div class="info-row-2col">
                    <div class="info-item-left">
                        <span class="info-lbl-inline">College:</span>
                        <span class="info-val-inline" style="flex:1;"><?= $college ?></span>
                    </div>
                    <div class="info-item-right">
                        <span class="info-lbl-inline">Department:</span>
                        <span class="info-val-inline" style="flex:1;"><?= htmlspecialchars($department) ?></span>
                    </div>
                </div>
            </div>

            <!-- ══ BODY CERTIFICATION (QF-VPAA-08) ══ -->
            <div class="cert-body">
                <p>To Whom It May Concern:</p>
                <p class="cert-indent">
                    This is to certify that Mr./Ms.
                    <span class="underline-box" style="min-width: 260px; text-align:center; display:inline-block; position:relative; margin-bottom:12px;">
                        <?= $studentFullName ?>
                        <span style="position:absolute; top:100%; left:0; right:0; text-align:center; font-size:7.5pt; font-style:italic; font-weight:normal; display:block; margin-top:-1px;">(Name of Student)</span>
                    </span>
                    is cleared from any financial
                    and property accountability as of
                    <span class="underline-box" style="min-width: 60px; text-align:center;"><?= htmlspecialchars($semester) ?></span>
                    Semester/Summer, School Year
                    <span class="underline-box" style="min-width: 90px; text-align:center;"><?= $schoolYear ?></span>.
                </p>
            </div>

            <!-- ══ SIGNATORIES (QF-VPAA-08) ══ -->
            <?= renderRegularSignatories($signatories) ?>

            <!-- ══ DISTRIBUTIONS (QF-VPAA-08) ══ -->
            <div class="dist-block">
                <em>Distributions:</em>
                <p>1 – Student</p>
                <p>2 – Registrar</p>
            </div>

        </div><!-- /.form-copy -->

        <?php if ($copy === 1): ?>
        <!-- Cut line separating Copy 1 and Copy 2 -->
        <div class="cut-line">✂ - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -</div>
        <?php endif; ?>

        <?php endfor; ?>

    </div><!-- /.sheet -->

</div><!-- /.page-wrapper -->

<script>
function autoFitSheet() {
    var wrapper = document.querySelector('.page-wrapper');
    var sheet = document.querySelector('.sheet');
    var printBar = document.querySelector('.print-bar');
    if (!wrapper || !sheet) return;
    
    if (window.matchMedia && window.matchMedia('print').matches) return;

    var containerWidth = wrapper.clientWidth - 16;
    if (containerWidth < 800 && containerWidth > 0) {
        var scale = containerWidth / 800;
        sheet.style.transform = 'scale(' + scale + ')';
        sheet.style.transformOrigin = 'top left';
        sheet.style.marginLeft = '0';
        sheet.style.marginRight = '0';
        var printBarHeight = printBar ? printBar.offsetHeight + 15 : 0;
        var scaledHeight = sheet.offsetHeight * scale;
        wrapper.style.height = (scaledHeight + printBarHeight + 20) + 'px';
    } else {
        sheet.style.transform = 'none';
        sheet.style.marginLeft = 'auto';
        sheet.style.marginRight = 'auto';
        wrapper.style.height = 'auto';
    }
}
window.addEventListener('DOMContentLoaded', autoFitSheet);
window.addEventListener('load', autoFitSheet);
window.addEventListener('resize', autoFitSheet);
</script>

</body>
</html>
