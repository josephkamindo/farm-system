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
        <a href="dashboard.php" class="<?= navClass('dashboard.php', $current) ?>">Dashboard</a>
        <a href="records.php" class="<?= navClass('records.php', $current) ?>">Farm Records</a>
        <a href="market.php" class="<?= navClass('market.php', $current) ?>">Market Linkage</a>
        <a href="decision.php" class="<?= navClass('decision.php', $current) ?>">Decision Support</a>
        <a href="reports.php" class="<?= navClass('reports.php', $current) ?>">Reports</a>
    </nav>
    <div class="signout">
        <a href="logout.php">Log out</a>
    </div>
</aside>
