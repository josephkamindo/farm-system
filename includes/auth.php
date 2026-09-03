<?php
// Include this at the top of any page that requires login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
