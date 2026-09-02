<?php
require_once "includes/config.php";
require_once "includes/auth.php";

$userId = $_SESSION['user_id'];

/**
 * Rule-based recommendation logic.
 * Looks at the average market price for a crop across the months we have
 * data for, finds the historically best-priced month, and compares it
 * to the closest month we have data for right now. This is the simple
 * "rule-based decision support mechanism" described in the proposal —
 * it doesn't predict prices, it reasons from recorded seasonal averages.
 */
function getCropRecommendation($conn, $cropName) {
    $stmt = $conn->prepare("SELECT month, average_price FROM market_prices WHERE crop_name = ? ORDER BY month");
    $stmt->bind_param("s", $cropName);
    $stmt->execute();
    $result = $stmt->get_result();

    $priceByMonth = [];
    while ($row = $result->fetch_assoc()) {
        $priceByMonth[(int)$row['month']] = (float)$row['average_price'];
    }

    if (empty($priceByMonth)) {
        return [
            'type' => 'unknown',
            'text' => 'No market price history recorded for this crop yet. Add price data to get a recommendation.'
        ];
    }

    // Find the best (highest-priced) month on record
    $bestMonth = array_search(max($priceByMonth), $priceByMonth);
    $bestPrice = $priceByMonth[$bestMonth];

    // Find the month in our data closest to the current calendar month
    $currentMonth = (int) date('n');
    $closestMonth = null;
    $smallestGap = 13;
    foreach ($priceByMonth as $month => $price) {
        $gap = min(abs($month - $currentMonth), 12 - abs($month - $currentMonth));
        if ($gap < $smallestGap) {
            $smallestGap = $gap;
            $closestMonth = $month;
        }
    }
    $currentApproxPrice = $priceByMonth[$closestMonth];
    $bestMonthName = date('F', mktime(0, 0, 0, $bestMonth, 1));

    if ($currentApproxPrice >= $bestPrice * 0.9) {
        return [
            'type' => 'good',
            'text' => "Prices are currently near their seasonal high (around KES " . number_format($currentApproxPrice, 2) . "). This looks like a good time to sell."
        ];
    } else {
        return [
            'type' => 'wait',
            'text' => "Current prices (~KES " . number_format($currentApproxPrice, 2) . ") are below the seasonal peak. Prices have historically been highest around $bestMonthName (~KES " . number_format($bestPrice, 2) . "). Consider holding stock if it can keep, or explore other buyers now."
        ];
    }
}

// Gather the distinct crops this farmer is growing and/or has listed
$crops = [];
$stmt = $conn->prepare("SELECT DISTINCT crop_name FROM farm_records WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $crops[$row['crop_name']] = true;
}

$stmt = $conn->prepare("SELECT DISTINCT crop_name FROM market_listings WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $crops[$row['crop_name']] = true;
}

$crops = array_keys($crops);

$pageTitle = "Decision Support";
include "includes/header.php";
?>

<div class="page-heading">
    <h1>Decision Support</h1>
    <p>Rule-based recommendations on the best time to sell, based on recorded seasonal market prices.</p>
</div>

<div class="card">
    <?php if (empty($crops)): ?>
        <div class="empty-state">
            Add a farm record or a market listing first — recommendations are generated per crop.
        </div>
    <?php else: ?>
        <?php foreach ($crops as $crop):
            $rec = getCropRecommendation($conn, $crop);
        ?>
            <div class="rec-card rec-<?= $rec['type'] ?>">
                <div class="rec-crop"><?= htmlspecialchars($crop) ?></div>
                <div class="rec-text"><?= htmlspecialchars($rec['text']) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="card">
    <h2 style="font-size:1rem; margin-bottom:8px;">How this works</h2>
    <p style="color:#7a6f5c; font-size:0.9rem;">
        For each crop you grow or list, the system checks the <code>market_prices</code> table for recorded
        average prices across different months. It compares the current month's closest known price to the
        historical peak for that crop, and recommends selling now if prices are near that peak, or waiting
        if a better-priced month is approaching. This is a simplified, rule-based approach — not a prediction —
        matching objective 3 of the proposal.
    </p>
</div>

<?php include "includes/footer.php"; ?>
