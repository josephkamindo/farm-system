<?php
require_once "includes/config.php";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect email or password.";
        }
    } else {
        $error = "Incorrect email or password.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log in — Smart Farm System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-shell">
        <div class="auth-visual">
            <div class="mark">🌿 Smart Farm System</div>
            <div class="pitch">
                <h2>Every harvest starts with a good record.</h2>
                <p>Track inputs, costs, and yields in one place — and turn that data into decisions that pay off at market.</p>
            </div>
            <?php include "includes/illustration.php"; ?>
        </div>

        <div class="auth-form-side">
            <div class="auth-card">
                <div class="eyebrow">Welcome back</div>
                <h1>Log in to your account</h1>
                <p class="subtitle">Pick up where you left off with your farm records.</p>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['registered'])): ?>
                    <div class="alert alert-success">Account created. You can log in now.</div>
                <?php endif; ?>

                <form method="POST" action="index.php">
                    <div class="field">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn">Log in</button>
                </form>

                <p class="switch-link">Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
