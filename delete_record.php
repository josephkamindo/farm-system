<?php
require_once "includes/config.php";
require_once "includes/auth.php";

$userId = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $recordId = (int) $_GET['id'];

    // The "AND user_id = ?" makes sure a farmer can only delete their own records
    $stmt = $conn->prepare("DELETE FROM farm_records WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $recordId, $userId);
    $stmt->execute();
}

header("Location: records.php");
exit();
