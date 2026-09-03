<?php
require_once "includes/config.php";

$_SESSION['lang'] = ($_SESSION['lang'] ?? 'en') === 'en' ? 'sw' : 'en';

// Send the user back to whichever page they clicked the toggle from
$redirectTo = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
header("Location: $redirectTo");
exit();
