<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    header("Location: registration.php");
    exit();
}

$action = $_POST['action'];

// ============================================
// SIGNUP
// ============================================
if ($action === 'signup') {
    $_SESSION['active_tab'] = 'signup';

    $fname        = mysqli_real_escape_string($conn, trim($_POST['fname']        ?? ''));
    $mname        = mysqli_real_escape_string($conn, trim($_POST['mname']        ?? ''));
    $lname        = mysqli_real_escape_string($conn, trim($_POST['lname']        ?? ''));
    $gender       = mysqli_real_escape_string($conn, trim($_POST['gender']       ?? ''));
    $cellphone_no = mysqli_real_escape_string($conn, trim($_POST['cellphone_no'] ?? ''));
    $barangay     = mysqli_real_escape_string($conn, trim($_POST['barangay']     ?? ''));
    $city         = mysqli_real_escape_string($conn, trim($_POST['city']         ?? ''));
    $email        = mysqli_real_escape_string($conn, trim($_POST['email']        ?? ''));
    $password     = $_POST['password']         ?? '';
    $confirm_p    = $_POST['confirm_password'] ?? '';

    // Save inputs so form repopulates on error
    $_SESSION['form_data'] = [
        'fname'        => $fname,
        'mname'        => $mname,
        'lname'        => $lname,
        'gender'       => $gender,
        'cellphone_no' => $cellphone_no,
        'barangay'     => $barangay,
        'city'         => $city,
        'email'        => $email,
    ];

    // Check empty fields
    if (empty($fname) || empty($lname) || empty($gender) || empty($cellphone_no) ||
        empty($barangay) || empty($city) || empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Please fill in all required fields.";
        header("Location: registration.php");
        exit();
    }

    // Check passwords match
    if ($password !== $confirm_p) {
        $_SESSION['error_message'] = "Passwords do not match!";
        header("Location: registration.php");
        exit();
    }

    // Check if email already exists
    $check = mysqli_query($conn, "SELECT user_id FROM tblusers WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error_message'] = "That email is already registered. Try logging in.";
        header("Location: registration.php");
        exit();
    }

    // Hash password and insert
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $mname_val = !empty($mname) ? "'$mname'" : "NULL";

    $sql = "INSERT INTO tblusers (fname, mname, lname, gender, cellphone_no, barangay, city, email, password)
            VALUES ('$fname', $mname_val, '$lname', '$gender', '$cellphone_no', '$barangay', '$city', '$email', '$hashed_password')";

    if (mysqli_query($conn, $sql)) {
        unset($_SESSION['form_data'], $_SESSION['active_tab']);
        $_SESSION['success_message'] = "Account created! Welcome, $fname. Please log in.";
        $_SESSION['active_tab'] = 'login';
    } else {
        $_SESSION['error_message'] = "Something went wrong. Please try again.";
    }

    header("Location: registration.php");
    exit();
}

// ============================================
// LOGIN
// ============================================
elseif ($action === 'login') {
    $_SESSION['active_tab'] = 'login';

    $email    = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $_SESSION['form_data'] = ['email' => $email];

    // Check empty fields
    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Please enter your email and password.";
        header("Location: registration.php");
        exit();
    }

    // Find user
    $result = mysqli_query($conn, "SELECT * FROM tblusers WHERE email = '$email'");
    $user   = mysqli_fetch_assoc($result);

    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['error_message'] = "Invalid email or password.";
        header("Location: registration.php");
        exit();
    }

    // Set session — then go to dashboard at root
    unset($_SESSION['form_data'], $_SESSION['active_tab']);
    $_SESSION['user_id']    = $user['user_id'];
    $_SESSION['user_fname'] = $user['fname'];
    $_SESSION['user_lname'] = $user['lname'];
    $_SESSION['user_email'] = $user['email'];

    header("Location: ../index.php"); // ← goes up to root
    exit();
}

header("Location: registration.php");
exit();