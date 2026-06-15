<?php
function loadCurrentUserContext(mysqli $conn, int $user_id): array
{
    $stmt = $conn->prepare("SELECT * FROM tblusers WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        session_unset();
        session_destroy();
        header("Location: /survAIval/Registration/registration.php");
        exit();
    }

    $_SESSION['user_fname'] = $user['fname'];
    $_SESSION['user_lname'] = $user['lname'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_barangay'] = $user['barangay'];
    $_SESSION['user_city'] = $user['city'];

    $full_name = trim($user['fname'] . ' ' . $user['lname']);
    $location = trim($user['barangay'] . ' - ' . $user['city']);

    return [
        'user' => $user,
        'full_name' => $full_name,
        'location' => $location,
        'barangay' => $user['barangay'],
        'city' => $user['city'],
    ];
}
?>
