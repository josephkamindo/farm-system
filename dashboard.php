<?php
require_once "includes/config.php";
require_once "includes/auth.php";

$userId = $_SESSION['user_id'];

// Count crops that are not yet harvested
$stmt = $conn->prepare("SELECT COUNT(*) AS active_crops FROM farm_records WHERE user_id = ? AND status != 'harvested'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$activeCrops = $stmt->get_result()->fetch_assoc()['active_crops'];

// Sum up all costs recorded so far
$stmt = $conn->prepare("SELECT COALESCE(SUM(input_cost + labour_cost + other_cost), 0) AS total_cost FROM farm_records WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$totalCost = $stmt->get_result()->fetch_assoc()['total_cost'];

// Count active market listings
$stmt = $conn->prepare("SELECT COUNT(*) AS listing_count FROM market_listings WHERE user_id = ? AND status = 'available'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$listingCount = $stmt->get_result()->fetch_assoc()['listing_count'];

// Pull the 5 most recent farm records for a quick preview table
$stmt = $conn->prepare("SELECT crop_name, status, quantity_harvested, unit, created_at FROM farm_records WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $userId);
$stmt->execute();
$recentRecords = $stmt->get_result();

$pageTitle = "Dashboard";
include "includes/header.php";
?>

<div class="page-heading">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h1>
    <p>You're logged in as <?= htmlspecialchars($_SESSION['role']) ?>. Here's your farm at a glance.</p>
</div>

<div class="stat-grid">
    <div class="stat-box">
        <div class="value"><?= $activeCrops ?></div>
        <div class="label">Active crops</div>
    </div>
    <div class="stat-box">
        <div class="value">KES <?= number_format($totalCost, 2) ?></div>
        <div class="label">Total costs recorded</div>
    </div>
    <div class="stat-box">
        <div class="value"><?= $listingCount ?></div>
        <div class="label">Market listings</div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2 style="font-size:1.1rem;">Recent farm records</h2>
        <a href="records.php" class="btn-outline">Manage records →</a>
    </div>

    <?php if ($recentRecords->num_rows === 0): ?>
        <div class="empty-state">
            You haven't added any farm records yet.<br>
            <a href="records.php">Add your first crop record →</a>
        </div>
    <?php else: ?>
        <table>
            <tr>
                <th>Crop</th>
                <th>Status</th>
                <th>Harvested</th>
                <th>Date added</th>
            </tr>
            <?php while ($row = $recentRecords->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['crop_name']) ?></td>
                    <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                    <td><?= $row['quantity_harvested'] > 0 ? number_format($row['quantity_harvested'], 1) . ' ' . htmlspecialchars($row['unit']) : '—' ?></td>
                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
