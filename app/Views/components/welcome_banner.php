<?php
$rawName   = $userName ?? $_SESSION['user_name'] ?? 'User';
$parts     = explode(' ', trim($rawName));
$firstName = $parts[0] ?? $rawName;

$role   = $_SESSION['user_role'] ?? 'user';
$office = $_SESSION['office'] ?? '';

$subtitle = match($role) {
    'admin'                => 'Oversee clearances, student records, degree programs, and signatories.',
    'signatory'            => 'Review student clearance submissions and manage pending requirements.',
    'enrollment_committee' => 'Monitor student clearance completion and enrollment eligibility.',
    default                => 'Always stay updated about your academic clearance progress.'
};

$roleBadge = match($role) {
    'admin'                => 'System Administrator',
    'signatory'            => 'Signatory' . ($office ? " • {$office}" : ''),
    'enrollment_committee' => 'Enrollment Committee',
    default                => ucfirst($role)
};
?>

<!-- Welcome Hero Banner -->
<div class="welcome-hero-banner" style="
    position: relative;
    background: linear-gradient(135deg, #FFCA28 0%, #FDB813 55%, #F59E0B 100%);
    border-radius: 16px;
    padding: 1.85rem 2.25rem;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(253, 184, 19, 0.28), 0 2px 6px rgba(0, 33, 71, 0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 125px;
    margin-bottom: 2.5rem !important;
">
    <!-- Decorative SVG Waves / Curved Shapes -->
    <svg style="position: absolute; right: 0; top: 0; bottom: 0; height: 100%; width: 45%; min-width: 260px; max-width: 480px; pointer-events: none;"
         viewBox="0 0 480 140" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Deep ISAT U Blue swoosh -->
        <path d="M120 0 C 180 80, 240 140, 360 140 L 480 140 L 480 0 Z" fill="#002147"/>
        <!-- Soft accent wave -->
        <path d="M70 0 C 130 90, 200 140, 310 140 C 270 140, 160 85, 100 0 Z" fill="#001733" opacity="0.3"/>
        <!-- Gold ring accent on blue field -->
        <circle cx="430" cy="140" r="95" stroke="#FDB813" stroke-width="26" opacity="0.45" fill="none"/>
        <circle cx="430" cy="140" r="45" fill="#001733"/>
    </svg>

    <!-- Subtle left background accent curve -->
    <svg style="position: absolute; left: -25px; bottom: -30px; height: 130px; width: 130px; pointer-events: none; opacity: 0.14;"
         viewBox="0 0 100 100" fill="none">
        <circle cx="30" cy="70" r="60" fill="#002147"/>
    </svg>

    <div style="position: relative; z-index: 2; max-width: 68%;">
        <div style="display: inline-flex; align-items: center; gap: 0.4rem; background: rgba(0, 33, 71, 0.1); padding: 0.25rem 0.65rem; border-radius: 999px; margin-bottom: 0.5rem; font-size: 0.76rem; font-weight: 700; color: #002147; letter-spacing: 0.03em; text-transform: uppercase;">
            <i class="bi bi-shield-check"></i>
            <span><?= htmlspecialchars($roleBadge) ?></span>
        </div>
        <h2 style="font-size: 1.85rem; font-weight: 800; color: #002147; margin: 0 0 0.35rem 0; letter-spacing: -0.02em; line-height: 1.2;">
            Welcome back, <?= htmlspecialchars($firstName) ?>!
        </h2>
        <p style="margin: 0; font-size: 0.95rem; color: #2d2400; font-weight: 500; line-height: 1.4; opacity: 0.92;">
            <?= htmlspecialchars($subtitle) ?>
        </p>
    </div>

    <div class="d-none d-md-flex" style="position: relative; z-index: 2; text-align: right; flex-direction: column; align-items: flex-end; justify-content: center; padding-right: 0.75rem;">
        <span style="font-size: 0.74rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.06em; color: #ffffff; opacity: 0.85;">Today</span>
        <span style="font-size: 1.15rem; font-weight: 800; color: #ffffff; letter-spacing: -0.01em;"><?= date('M j, Y') ?></span>
        <span style="font-size: 0.75rem; color: #ffd54f; font-weight: 600;"><?= date('l') ?></span>
    </div>
</div>

