<?php
require_once "includes/config.php";
require_once "includes/auth.php";

$userId = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $listingId = (int) $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM market_listings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $listingId, $userId);
    $stmt->execute();
}

header("Location: market.php");
exit();
