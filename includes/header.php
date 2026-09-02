<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>Smart Farm System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="app-shell">
        <?php include "includes/sidebar.php"; ?>
        <main class="app-main">
