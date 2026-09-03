<?php
require_once "includes/config.php";
require_once "includes/auth.php";

$userId = $_SESSION['user_id'];

// ---------- Costs per crop (from farm records) ----------
$stmt = $conn->prepare("
    SELECT crop_name, SUM(input_cost + labour_cost + other_cost) AS total_cost, SUM(quantity_harvested) AS total_qty
    FROM farm_records
    WHERE user_id = ?
    GROUP BY crop_name
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$costResult = $stmt->get_result();

// ---------- Estimated revenue per crop (from sold market listings) ----------
$stmt = $conn->prepare("
    SELECT crop_name, SUM(quantity_available * asking_price) AS total_revenue
    FROM market_listings
    WHERE user_id = ? AND status = 'sold'
    GROUP BY crop_name
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$revenueResult = $stmt->get_result();

// ---------- Merge both into one summary per crop ----------
$summary = [];
while ($row = $costResult->fetch_assoc()) {
    $summary[$row['crop_name']] = [
        'cost' => (float) $row['total_cost'],
        'qty' => (float) $row['total_qty'],
        'revenue' => 0.0
    ];
}
while ($row = $revenueResult->fetch_assoc()) {
    if (!isset($summary[$row['crop_name']])) {
        $summary[$row['crop_name']] = ['cost' => 0.0, 'qty' => 0.0, 'revenue' => 0.0];
    }
    $summary[$row['crop_name']]['revenue'] = (float) $row['total_revenue'];
}

$totalCost = array_sum(array_column($summary, 'cost'));
$totalRevenue = array_sum(array_column($summary, 'revenue'));
$netProfit = $totalRevenue - $totalCost;

$costs = array_column($summary, 'cost');
$revenues = array_column($summary, 'revenue');
$maxValue = max(1, $costs ? max($costs) : 0, $revenues ? max($revenues) : 0);

$pageTitle = "Reports";
include "includes/header.php";
?>

<div class="page-heading" style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px;">
    <div>
        <h1><?= t('reports') ?></h1>
        <p><?= t('reports_intro') ?></p>
    </div>
    <button onclick="window.print()" class="btn-small no-print" style="width:auto;"><?= t('print_report') ?></button>
</div>

<div class="stat-grid">
    <div class="stat-box">
        <div class="value">KES <?= number_format($totalCost, 2) ?></div>
        <div class="label">Total costs</div>
    </div>
    <div class="stat-box">
        <div class="value">KES <?= number_format($totalRevenue, 2) ?></div>
        <div class="label">Total revenue (sold listings)</div>
    </div>
    <div class="stat-box">
        <div class="value <?= $netProfit >= 0 ? 'profit-positive' : 'profit-negative' ?>">
            KES <?= number_format($netProfit, 2) ?>
        </div>
        <div class="label"><?= $netProfit >= 0 ? 'Net profit' : 'Net loss' ?></div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2 style="font-size:1.1rem;">Cost per crop</h2>
    </div>
    <?php if (empty($summary)): ?>
        <div class="empty-state">No data yet — add farm records to see cost breakdowns here.</div>
    <?php else: ?>
        <?php foreach ($summary as $crop => $data): ?>
            <div class="bar-row">
                <div class="bar-label"><?= htmlspecialchars($crop) ?></div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: <?= $maxValue > 0 ? min(100, ($data['cost'] / $maxValue) * 100) : 0 ?>%;"></div>
                </div>
                <div class="bar-value">KES <?= number_format($data['cost'], 0) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-head">
        <h2 style="font-size:1.1rem;">Revenue per crop (sold listings)</h2>
    </div>
    <?php if (empty($summary)): ?>
        <div class="empty-state">No sold listings yet.</div>
    <?php else: ?>
        <?php foreach ($summary as $crop => $data): ?>
            <div class="bar-row">
                <div class="bar-label"><?= htmlspecialchars($crop) ?></div>
                <div class="bar-track">
                    <div class="bar-fill revenue" style="width: <?= $maxValue > 0 ? min(100, ($data['revenue'] / $maxValue) * 100) : 0 ?>%;"></div>
                </div>
                <div class="bar-value">KES <?= number_format($data['revenue'], 0) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-head">
        <h2 style="font-size:1.1rem;">Profit / loss by crop</h2>
    </div>
    <?php if (empty($summary)): ?>
        <div class="empty-state">No data yet.</div>
    <?php else: ?>
        <table>
            <tr>
                <th>Crop</th>
                <th>Qty harvested</th>
                <th>Total cost</th>
                <th>Cost efficiency</th>
                <th>Total revenue</th>
                <th>Profit / loss</th>
            </tr>
            <?php foreach ($summary as $crop => $data):
                $profit = $data['revenue'] - $data['cost'];
                $efficiency = $data['qty'] > 0 ? $data['cost'] / $data['qty'] : null;
            ?>
                <tr>
                    <td><?= htmlspecialchars($crop) ?></td>
                    <td><?= number_format($data['qty'], 1) ?></td>
                    <td>KES <?= number_format($data['cost'], 2) ?></td>
                    <td><?= $efficiency !== null ? 'KES ' . number_format($efficiency, 2) . ' / unit' : '—' ?></td>
                    <td>KES <?= number_format($data['revenue'], 2) ?></td>
                    <td class="<?= $profit >= 0 ? 'profit-positive' : 'profit-negative' ?>">
                        KES <?= number_format($profit, 2) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <p style="color:#7a6f5c; font-size:0.85rem;">
        Note: revenue is estimated from market listings you've marked as "Sold" in Market Linkage
        (quantity × asking price). This is a simplified figure for demonstration with sample data,
        as described in the study's scope. "Cost efficiency" (KES spent per unit harvested) is a
        simple resource-use indicator, inspired by data-driven efficiency practices used in
        high-productivity agricultural systems abroad — a lower figure means more output per shilling spent.
    </p>
</div>

<?php include "includes/footer.php"; ?>
