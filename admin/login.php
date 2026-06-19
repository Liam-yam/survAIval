<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db   = "dbsurvaival";

$conn = new mysqli($host, $user, $pass, $db);

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_input = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email_input === 'aeryllmoncayo@gmail.com' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = 'Aeryll Reign Moncayo';
        header("Location: admin.php");
        exit();
    } else {
        $error = "Authorized administrative account not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>survAlval | Portal Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f6f8; }
        .login-box { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); width: 100%; max-width: 400px; border: 1px solid #edf2f7; }
        .login-title { text-align: center; color: #1e3824; margin-bottom: 24px; font-weight: 800; font-size: 24px; }
        .login-error { color: #9b1c1c; background: #fdf2f2; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; text-align: center; font-weight: 600; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-title">surv<span>Al</span>val Control Portal</div>
        <?php if(!empty($error)): ?>
            <div class="login-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Account Username</label>
                <input type="text" name="email" required placeholder="Enter email address">
            </div>
            <div class="form-group">
                <label>Secure Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-action" style="width: 100%; border-radius: 8px; margin-top: 10px; padding: 12px;">Access Dashboard</button>
        </form>
    </div>
</body>
</html>