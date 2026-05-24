<?php
$rootPath = '../';
require_once '../config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: ../dashboard.php");
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SleepTrack</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-box">
        <div class="auth-logo"><span>🌙</span> SleepTrack</div>
        <div class="auth-sub">Track your sleep, improve your life</div>
        <h2 class="auth-title">Sign In</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Your password" required>
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>

        <div class="auth-footer">
            No account yet? <a href="register.php">Create one</a>
        </div>
    </div>
</div>
</body>
</html>