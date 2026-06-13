<?php
session_start();
$_SESSION['error_message'] = "";
$_SESSION['success_message'] = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $_SESSION['form_data'] = array_map('htmlspecialchars', $_POST);
    if ($_POST['action'] === 'signup') {
        $_SESSION['active_tab'] = "signup";
        $fullname  = htmlspecialchars($_POST['fullname'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm_p = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm_p) {
            $_SESSION['error_message'] = "Passwords do not match!";
            header("Location: registration.php");
            exit();
        } else {
            
            $_SESSION['success_message'] = "Account successfully created for " . $fullname . "!";
            unset($_SESSION['form_data']);
            header("Location: registration.php");
            exit();
        }

    } elseif ($_POST['action'] === 'login') {
        $_SESSION['active_tab'] = "login";
        
        $email = htmlspecialchars($_POST['email'] ?? '');

        
        $_SESSION['success_message'] = "Logged in successfully as " . $email . "!";
        unset($_SESSION['form_data']);
        header("Location: registration.php");
        exit();
    }
}

header("Location: registration.php");
exit();