<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Registration/registration.php");
    exit();
}

$user_fname = $_SESSION['user_fname'];
$user_lname = $_SESSION['user_lname'];

$success_message = $_SESSION['success_message'] ?? '';
$error_message   = $_SESSION['error_message']   ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>survAIval - Change Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="../../assets/logo.svg" alt="survAIval Logo">
        </div>
        <div class="user-card">
            <p class="user-name"><?php echo $user_fname . ' ' . $user_lname; ?></p>
            <p class="user-role">Resident | Purok 8</p>
            <p class="user-location"><i class="bi bi-geo-alt-fill"></i> San Pablo - Valle Pio</p>
        </div>
        <nav class="sidebar-nav">
            <p class="nav-label">MENU</p>
            <ul>
                <li><a href="../../index.php"><i class="bi bi-house-fill"></i> Home</a></li>
                <li><a href="../../Report_Incidents/report_incidents.php"><i class="bi bi-exclamation-triangle-fill"></i> Report Incidents</a></li>
                <li><a href="../../My_Reports/my_reports.php"><i class="bi bi-clock-history"></i> My Reports</a></li>
                <li><a href="../../Incident_Map/incident_map.php"><i class="bi bi-geo-alt-fill"></i> Incident Map</a></li>
            </ul>
            <p class="nav-label">INFORMATION</p>
            <ul>
                <li><a href="../../Announcement/announcement.php"><i class="bi bi-megaphone-fill"></i> Announcement</a></li>
                <li><a href="../../Hotlines/hotlines.php"><i class="bi bi-telephone-fill"></i> Hotlines</a></li>
                <li class="active"><a href="../settings.php"><i class="bi bi-gear-fill"></i> Settings</a></li>
            </ul>
        </nav>
        <div class="sidebar-logout">
            <a href="../../logout.php"><i class="bi bi-box-arrow-right"></i> Log Out</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <div class="top-header">
            <div class="header-text">
                <p class="header-subtitle">Barangay Smart Disaster Risk Monitoring</p>
                <p class="header-date"><?php echo date('l, F j, Y'); ?> — Brgy. San Pablo - Sto. Tomas City</p>
            </div>
            <div class="header-actions">
                <button class="sos-btn">SOS</button>
                <button class="notif-btn"><i class="bi bi-bell-fill"></i></button>
            </div>
        </div>

        <!-- Back Button -->
        <a href="../settings.php" class="back-btn">
            <i class="bi bi-arrow-left"></i> Back to Settings
        </a>

        <h1 class="page-title">Change Password</h1>

        <!-- Flash Messages -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Change Password Card -->
        <div class="password-card">

            <div class="card-icon">
                <i class="bi bi-lock-fill"></i>
            </div>
            <p class="card-desc">Enter your current password then set a new one. Must be at least 8 characters.</p>

            <form method="POST" action="password_process.php">

                <div class="form-group">
                    <label>Current Password</label>
                    <div class="input-wrap">
                        <input type="password" name="current_password"
                               id="current_password"
                               class="input-field" placeholder="Enter current password" required>
                        <button type="button" class="eye-toggle" onclick="togglePass('current_password', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <div class="input-wrap">
                        <input type="password" name="new_password"
                               id="new_password"
                               class="input-field" placeholder="Enter new password" required>
                        <button type="button" class="eye-toggle" onclick="togglePass('new_password', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <div class="input-wrap">
                        <input type="password" name="confirm_password"
                               id="confirm_password"
                               class="input-field" placeholder="Confirm new password" required>
                        <button type="button" class="eye-toggle" onclick="togglePass('confirm_password', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Update Password</button>

            </form>
        </div>

    </main>

    <script src="script.js"></script>
</body>
</html>