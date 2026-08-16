<?php
/**
 * Standalone Printable Clearance Form
 * Variables available: $student, $clearance, $signatories, $semester, $yearLabel
 */

$studentFullName = htmlspecialchars(trim($student['first_name'] . ' ' . $student['last_name']));
$studentNumber   = htmlspecialchars($student['student_number']);
$college         = htmlspecialchars($student['college']);
$course          = htmlspecialchars($student['course']);
$section         = htmlspecialchars($student['section']);
$schoolYear      = htmlspecialchars($clearance['school_year']);
$clearanceName   = htmlspecialchars($clearance['name']);
$clearanceType   = $clearance['type'] ?? 'regular';   // 'regular' | 'exit'
$logoUrl         = BASE_URL . 'css/logo.png';

// Build signatory grid HTML
function renderSignatories(array $signatories): string {
    if (empty($signatories)) return '<p><em>No signatories assigned.</em></p>';
    $html = '<div class="sig-grid">';
    foreach ($signatories as $sig) {
        $office   = htmlspecialchars($sig['office']);
        $signedAt = '';
        if ($sig['status'] === 'cleared' && !empty($sig['signed_at'])) {
            $signedAt = date('M j, Y g:i A', strtotime($sig['signed_at']));
        }
        $html .= '<div class="sig-slot">';
        if ($signedAt) {
            $html .= '<div class="sig-signed">SIGNED</div>';
            $html .= '<div class="sig-date">' . htmlspecialchars($signedAt) . '</div>';
        } else {
            $html .= '<div class="sig-signed">&nbsp;</div>';
            $html .= '<div class="sig-date">&nbsp;</div>';
        }
        $html .= '<div class="sig-line"></div>';
        $html .= '<div class="sig-label">' . $office . '</div>';
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

$sigHtml = renderSignatories($signatories);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student's Semestral Clearance — <?= $studentFullName ?></title>
    <style>
        /* ── Reset ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            background: #e8e8e8;
            padding: 20px;
            color: #000;
        }

        /* ── Page wrapper ── */
        .page-wrapper {
            max-width: 760px;
            margin: 0 auto;
        }

        /* ── Print button ── */
        .print-bar {
            text-align: center;
            margin-bottom: 20px;
        }
        .print-bar button {
            background: #1a56a0;
            color: #fff;
            border: none;
            padding: 12px 36px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            letter-spacing: .4px;
        }
        .print-bar button:hover { background: #154a8a; }

        /* ── Form card ── */
        .clearance-form {
            background: #fff;
            padding: 18px 22px 16px;
            border: 1px solid #999;
        }

        /* ── Cut line ── */
        .cut-line {
            text-align: center;
            border-top: 2px dashed #555;
            margin: 0;
            padding: 6px 0;
            font-size: 9pt;
            letter-spacing: 1px;
            color: #444;
            background: #fff;
        }

        /* ── Header ── */
        .form-header {
            display: flex;
            align-items: stretch;
            border: 1px solid #000;
            margin-bottom: 10px;
        }
        .header-logo {
            padding: 6px 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-right: 1px solid #000;
            flex-shrink: 0;
        }
        .header-logo img {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }
        .header-university {
            flex: 1;
            padding: 6px 12px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-right: 1px solid #000;
        }
        .univ-tagline {
            font-size: 7.5pt;
            line-height: 1.4;
        }
        .univ-name {
            font-size: 10pt;
            font-weight: bold;
            line-height: 1.3;
        }
        .univ-location {
            font-size: 8pt;
        }
        .header-meta {
            display: flex;
            flex-direction: column;
            min-width: 200px;
            flex-shrink: 0;
        }
        .meta-row {
            display: flex;
            border-bottom: 1px solid #000;
        }
        .meta-row:last-child { border-bottom: none; }
        .meta-label {
            font-size: 7.5pt;
            font-weight: bold;
            padding: 2px 5px;
            border-right: 1px solid #000;
            min-width: 100px;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }
        .meta-value {
            font-size: 7.5pt;
            padding: 2px 5px;
            display: flex;
            align-items: center;
            flex: 1;
        }
        .form-title-bar {
            text-align: center;
            border-top: 1px solid #000;
            padding: 4px 0;
        }
        .form-title-bar h1 {
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: .5px;
        }

        /* ── Semester line ── */
        .semester-line {
            text-align: center;
            margin: 10px 0 6px;
            font-size: 10.5pt;
        }
        .field-underline {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 80px;
            text-align: center;
            font-weight: bold;
        }

        /* ── Student info grid ── */
        .info-grid {
            margin: 4px 0 8px;
            font-size: 10.5pt;
        }
        .info-row {
            display: flex;
            gap: 0;
            margin-bottom: 3px;
            flex-wrap: wrap;
        }
        .info-field {
            display: flex;
            align-items: baseline;
            gap: 4px;
            flex: 1;
            min-width: 240px;
        }
        .info-label {
            white-space: nowrap;
            font-size: 10.5pt;
        }
        .info-value {
            border-bottom: 1px solid #000;
            flex: 1;
            font-weight: bold;
            padding: 0 3px;
            min-width: 60px;
        }

        /* ── Body text ── */
        .body-text {
            font-size: 10.5pt;
            margin: 10px 0 0;
            line-height: 1.7;
        }
        .body-text .indent {
            margin-left: 40px;
        }
        .student-name-label {
            display: block;
            text-align: center;
            font-style: italic;
            font-size: 9pt;
            margin-top: -4px;
            margin-left: 40px;
        }

        /* ── Signatory grid ── */
        .sig-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0 40px;
            margin: 20px 0 8px;
        }
        .sig-slot {
            width: 180px;
            text-align: center;
            margin-bottom: 18px;
        }
        .sig-signed {
            font-size: 10pt;
            font-weight: bold;
            color: #000;
            min-height: 16px;
            line-height: 1.2;
        }
        .sig-date {
            font-size: 8.5pt;
            color: #333;
            min-height: 14px;
            line-height: 1.2;
            margin-bottom: 2px;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin: 2px 0 3px;
        }
        .sig-label {
            font-size: 10pt;
        }

        /* ── Distribution ── */
        .distribution {
            margin-top: 6px;
            font-size: 9.5pt;
        }
        .distribution em { font-style: italic; font-weight: bold; }
        .distribution p { margin-left: 12px; }

        /* ── Print CSS ── */
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .print-bar { display: none; }
            .page-wrapper { max-width: 100%; }
            .clearance-form {
                border: none;
                padding: 0;
            }
            @page { size: A4 portrait; margin: 12mm 14mm; }
            .cut-line { page-break-inside: avoid; }
            .form-copy { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="page-wrapper">

    <!-- Print Button -->
    <div class="print-bar no-print">
        <button onclick="window.print()">🖨️ Print Clearance Form</button>
    </div>

    <div class="clearance-form">

        <?php for ($copy = 1; $copy <= 2; $copy++): ?>
        <div class="form-copy">

            <!-- ══ HEADER ══ -->
            <div class="form-header">
                <div class="header-university">
                    <div class="univ-tagline">Republic of the Philippines</div>
                    <div class="univ-name">ILOILO SCIENCE AND TECHNOLOGY UNIVERSITY</div>
                    <div class="univ-location">La Paz, Iloilo City</div>
                </div>
                <div class="header-meta">
                    <div class="meta-row">
                        <span class="meta-label">Department:</span>
                        <span class="meta-value">VP FOR ACADEMIC AFFAIRS</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Document Code:</span>
                        <span class="meta-value"><?= $clearanceType === 'exit' ? 'QF-VPAA-09' : 'QF-VPAA-08' ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Rev. No.:</span>
                        <span class="meta-value">02</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Effective Date:</span>
                        <span class="meta-value">July 15, 2015</span>
                    </div>
                </div>
            </div>

            <div class="form-title-bar">
                <h1><?= $clearanceType === 'exit' ? "STUDENT'S EXIT CLEARANCE" : "STUDENT'S SEMESTRAL CLEARANCE" ?></h1>
            </div>

            <!-- ══ SEMESTER LINE ══ -->
            <div class="semester-line">
                <span class="field-underline"><?= htmlspecialchars($semester) ?></span>
                &nbsp;Semester/Summer&nbsp;
                <span class="field-underline">&nbsp;&nbsp;&nbsp;&nbsp;</span>
                ,&nbsp;SY&nbsp;
                <span class="field-underline"><?= $schoolYear ?></span>
            </div>

            <!-- ══ STUDENT INFO ══ -->
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-field">
                        <span class="info-label">Student ID No.:</span>
                        <span class="info-value"><?= $studentNumber ?></span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-field">
                        <span class="info-label">Name of Student:</span>
                        <span class="info-value"><?= $studentFullName ?></span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">Curriculum Year &amp; Section:</span>
                        <span class="info-value"><?= $yearLabel ?> — <?= $section ?></span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-field">
                        <span class="info-label">College:</span>
                        <span class="info-value"><?= $college ?></span>
                    </div>
                    <div class="info-field">
                        <span class="info-label">Department:</span>
                        <span class="info-value"><?= $college ?></span>
                    </div>
                </div>
            </div>

            <!-- ══ BODY TEXT ══ -->
            <div class="body-text">
                <p>To Whom It May Concern:</p>
                <p class="indent">
                    This is to certify that Mr./Ms.
                    <span class="field-underline">&nbsp;<?= $studentFullName ?>&nbsp;</span>
                    is cleared from any financial
                </p>
                <span class="student-name-label">(Name of Student)</span>
                <p class="indent">
                    and property accountability as of
                    <span class="field-underline">&nbsp;<?= htmlspecialchars($semester) ?>&nbsp;</span>
                    Semester/Summer, School Year
                    <span class="field-underline">&nbsp;<?= $schoolYear ?>&nbsp;</span>.
                </p>
            </div>

            <!-- ══ SIGNATORIES ══ -->
            <?= $sigHtml ?>

            <!-- ══ DISTRIBUTION ══ -->
            <div class="distribution">
                <em>Distributions:</em>
                <p>1 – Student</p>
                <p>2 – Registrar</p>
            </div>

        </div><!-- /.form-copy -->

        <?php if ($copy === 1): ?>
        <!-- Cut line between copies -->
        <div class="cut-line">✂ &nbsp;- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -</div>
        <?php endif; ?>

        <?php endfor; ?>

    </div><!-- /.clearance-form -->

</div><!-- /.page-wrapper -->
</body>
</html>
