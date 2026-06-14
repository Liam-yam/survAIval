<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Registration/registration.php");
    exit();
}

require_once '../../Registration/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: profile.php");
    exit();
}

$user_id     = $_SESSION['user_id'];
$fname       = mysqli_real_escape_string($conn, trim($_POST['fname']        ?? ''));
$mname       = mysqli_real_escape_string($conn, trim($_POST['mname']        ?? ''));
$lname       = mysqli_real_escape_string($conn, trim($_POST['lname']        ?? ''));
$cellphone_no = mysqli_real_escape_string($conn, trim($_POST['cellphone_no'] ?? ''));

// Validate required fields
if (empty($fname) || empty($lname) || empty($cellphone_no)) {
    $_SESSION['error_message'] = "Please fill in all required fields.";
    header("Location: profile.php");
    exit();
}

// Handle photo upload
$photo_sql = '';
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
    $upload_dir = '../../assets/profile_pics/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Only allow images
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $file_type     = $_FILES['profile_pic']['type'];

    if (!in_array($file_type, $allowed_types)) {
        $_SESSION['error_message'] = "Only JPG, PNG, WEBP, or GIF images are allowed.";
        header("Location: profile.php");
        exit();
    }

    $ext      = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
    move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_dir . $filename);

    $photo_path = mysqli_real_escape_string($conn, 'assets/profile_pics/' . $filename);
    $photo_sql  = ", profile_pic = '$photo_path'";
}

// Update tblusers
$mname_val = !empty($mname) ? "'$mname'" : "NULL";

$sql = "UPDATE tblusers SET
            fname        = '$fname',
            mname        = $mname_val,
            lname        = '$lname',
            cellphone_no = '$cellphone_no'
            $photo_sql
        WHERE user_id = '$user_id'";

if (mysqli_query($conn, $sql)) {
    // Update session
    $_SESSION['user_fname'] = $fname;
    $_SESSION['user_lname'] = $lname;
    $_SESSION['success_message'] = "Profile updated successfully!";
} else {
    $_SESSION['error_message'] = "Something went wrong. Please try again.";
}

header("Location: profile.php");
exit();