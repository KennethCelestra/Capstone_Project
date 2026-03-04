<div class="page-header">
    <h2>Pending Clearances</h2>
</div>

<?php $pending = count($clearances); ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
            <span class="stat-value">
                <?= $pending ?>
            </span>
            <span class="stat-label">Pending Signatures</span>
        </div>
    </div>
</div>

<div class="quick-actions">
    <a href="<?= BASE_URL ?>signatory/clearances" class="btn btn-primary">Review & Sign Clearances</a>
</div>