<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Registration/registration.php");
    exit();
}

require_once '../../Registration/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: change_password.php");
    exit();
}

$user_id         = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password     = $_POST['new_password']     ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$result = mysqli_query($conn, "SELECT password FROM tblusers WHERE user_id = '$user_id'");
$user   = mysqli_fetch_assoc($result);

if (!password_verify($current_password, $user['password'])) {
    $_SESSION['error_message'] = "Current password is incorrect.";
    header("Location: change_password.php");
    exit();
}

if ($new_password !== $confirm_password) {
    $_SESSION['error_message'] = "New passwords do not match.";
    header("Location: change_password.php");
    exit();
}

if (strlen($new_password) < 8) {
    $_SESSION['error_message'] = "New password must be at least 8 characters.";
    header("Location: change_password.php");
    exit();
}

if (password_verify($new_password, $user['password'])) {
    $_SESSION['error_message'] = "New password cannot be the same as your current password.";
    header("Location: change_password.php");
    exit();
}

$hashed = password_hash($new_password, PASSWORD_BCRYPT);
$hashed = mysqli_real_escape_string($conn, $hashed);

if (mysqli_query($conn, "UPDATE tblusers SET password = '$hashed' WHERE user_id = '$user_id'")) {
    $_SESSION['success_message'] = "Password updated successfully!";
    header("Location: change_password.php");
} else {
    $_SESSION['error_message'] = "Something went wrong. Please try again.";
    header("Location: change_password.php");
}

exit();
