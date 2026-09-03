<?php
// $current is set to the current file name so we can highlight the active link
$current = basename($_SERVER['PHP_SELF']);

function navClass($file, $current) {
    return $file === $current ? 'active' : '';
}
?>
<aside class="app-sidebar">
    <div class="brand">🌿 Smart Farm</div>
    <nav>
        <a href="dashboard.php" class="<?= navClass('dashboard.php', $current) ?>"><?= t('dashboard') ?></a>
        <a href="records.php" class="<?= navClass('records.php', $current) ?>"><?= t('farm_records') ?></a>
        <a href="market.php" class="<?= navClass('market.php', $current) ?>"><?= t('market_linkage') ?></a>
        <a href="decision.php" class="<?= navClass('decision.php', $current) ?>"><?= t('decision_support') ?></a>
        <a href="reports.php" class="<?= navClass('reports.php', $current) ?>"><?= t('reports') ?></a>
    </nav>
    <a href="toggle_lang.php" style="color:#ded3c2; font-size:0.85rem; padding:10px 12px; margin-bottom:6px;">
        🌐 <?= ($_SESSION['lang'] ?? 'en') === 'en' ? 'Swahili' : 'English' ?>
    </a>
    <div class="signout">
        <a href="logout.php"><?= t('log_out') ?></a>
    </div>
</aside>
