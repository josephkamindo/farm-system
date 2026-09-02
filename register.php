<?php
require_once "includes/config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $location = trim($_POST['location']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "That email is already registered.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insert = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role, location) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("ssssss", $fullName, $email, $phone, $hashedPassword, $role, $location);

        if ($insert->execute()) {
            header("Location: index.php?registered=1");
            exit();
        } else {
            $error = "Something went wrong. Please try again.";
        }
        $insert->close();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register — Smart Farm System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-shell">
        <div class="auth-visual">
            <div class="mark">🌿 Smart Farm System</div>
            <div class="pitch">
                <h2>Set up your farm, digitally, in minutes.</h2>
                <p>Join as a farmer, extension officer, or buyer — and start turning field data into better decisions.</p>
            </div>
            <?php include "includes/illustration.php"; ?>
        </div>

        <div class="auth-form-side">
            <div class="auth-card">
                <div class="eyebrow">Get started</div>
                <h1>Create your account</h1>
                <p class="subtitle">It takes less than a minute.</p>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="field">
                        <label for="full_name">Full name</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                    <div class="field">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="field">
                        <label for="phone">Phone number</label>
                        <input type="text" id="phone" name="phone" required>
                    </div>
                    <div class="field">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" placeholder="e.g. Kiambu">
                    </div>
                    <div class="field">
                        <label for="role">I am a</label>
                        <select id="role" name="role" required>
                            <option value="farmer">Farmer</option>
                            <option value="extension_officer">Agricultural Extension Officer</option>
                            <option value="buyer">Buyer / Trader</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>
                    <button type="submit" class="btn">Create account</button>
                </form>

                <p class="switch-link">Already have an account? <a href="index.php">Log in</a></p>
            </div>
        </div>
    </div>
</body>
</html>
