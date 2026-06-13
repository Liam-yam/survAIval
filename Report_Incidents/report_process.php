<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Registration/registration.php");
    exit();
}

require_once '../Registration/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: report_incidents.php");
    exit();
}

$user_id       = $_SESSION['user_id'];
$action        = $_POST['action']        ?? '';
$report_id     = $_POST['report_id']     ?? '';
$reporter_name = mysqli_real_escape_string($conn, trim($_POST['reporter_name']  ?? ''));
$contact_number= mysqli_real_escape_string($conn, trim($_POST['contact_number'] ?? ''));
$incident_title= mysqli_real_escape_string($conn, trim($_POST['incident_title'] ?? ''));
$incident_type = mysqli_real_escape_string($conn, trim($_POST['incident_type']  ?? ''));
$location      = mysqli_real_escape_string($conn, trim($_POST['location']       ?? ''));
$description   = mysqli_real_escape_string($conn, trim($_POST['description']    ?? ''));

// Handle photo upload
$photo_path = '';
if (isset($_FILES['photo']) && $_FILES['photo']['error'][0] === 0) {
    $upload_dir = '../assets/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $ext      = pathinfo($_FILES['photo']['name'][0], PATHINFO_EXTENSION);
    $filename = 'report_' . time() . '_' . $user_id . '.' . $ext;
    move_uploaded_file($_FILES['photo']['tmp_name'][0], $upload_dir . $filename);
    $photo_path = mysqli_real_escape_string($conn, 'assets/uploads/' . $filename);
}

// ============================================
// SUBMIT REPORT
// ============================================
if ($action === 'submit') {

    if (empty($reporter_name) || empty($contact_number) || empty($incident_title) ||
        empty($incident_type) || empty($location) || empty($description)) {
        $_SESSION['error_message'] = "Please fill in all required fields before submitting.";
        header("Location: report_incidents.php");
        exit();
    }

    // If submitting an existing draft, update it
    if (!empty($report_id)) {
        $photo_sql = !empty($photo_path) ? ", photo = '$photo_path'" : "";
        $sql = "UPDATE tblreports SET
                    reporter_name  = '$reporter_name',
                    contact_number = '$contact_number',
                    incident_title = '$incident_title',
                    incident_type  = '$incident_type',
                    location       = '$location',
                    description    = '$description',
                    status         = 'pending'
                    $photo_sql
                WHERE report_id = '$report_id' AND user_id = '$user_id'";
    } else {
        $sql = "INSERT INTO tblreports
                    (user_id, reporter_name, contact_number, incident_title, incident_type, location, description, photo, status)
                VALUES
                    ('$user_id', '$reporter_name', '$contact_number', '$incident_title', '$incident_type', '$location', '$description', '$photo_path', 'pending')";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success_message'] = "Report submitted successfully!";
    } else {
        $_SESSION['error_message'] = "Something went wrong. Please try again.";
    }

    header("Location: report_incidents.php");
    exit();
}

// ============================================
// SAVE AS DRAFT
// ============================================
elseif ($action === 'draft') {

    // If updating existing draft
    if (!empty($report_id)) {
        $photo_sql = !empty($photo_path) ? ", photo = '$photo_path'" : "";
        $sql = "UPDATE tblreports SET
                    reporter_name  = '$reporter_name',
                    contact_number = '$contact_number',
                    incident_title = '$incident_title',
                    incident_type  = '$incident_type',
                    location       = '$location',
                    description    = '$description',
                    status         = 'draft'
                    $photo_sql
                WHERE report_id = '$report_id' AND user_id = '$user_id'";
    } else {
        $sql = "INSERT INTO tblreports
                    (user_id, reporter_name, contact_number, incident_title, incident_type, location, description, photo, status)
                VALUES
                    ('$user_id', '$reporter_name', '$contact_number', '$incident_title', '$incident_type', '$location', '$description', '$photo_path', 'draft')";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success_message'] = "Draft saved!";
    } else {
        $_SESSION['error_message'] = "Could not save draft. Please try again.";
    }

    header("Location: report_incidents.php");
    exit();
}

header("Location: report_incidents.php");
exit();