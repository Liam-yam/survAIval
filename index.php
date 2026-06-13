<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Registration/registration.php");
    exit();
}

// Get user info from session
$user_fname = $_SESSION['user_fname'];
$user_lname = $_SESSION['user_lname'];
$user_email = $_SESSION['user_email'];

// Greeting based on time
$hour = (int) date('H');
if ($hour < 12) {
    $greeting = "Good morning";
} elseif ($hour < 18) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}

// Current date display
$date_today = date('l, F j, Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>survAIval - Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- ================================ -->
    <!-- SIDEBAR                          -->
    <!-- ================================ -->
    <aside class="sidebar">

        <!-- Logo -->
        <div class="sidebar-logo">
            <img src="assets/logo.svg" alt="survAIval Logo">
        </div>

        <!-- User Card -->
        <div class="user-card">
            <p class="user-name"><?php echo $user_fname . ' ' . $user_lname; ?></p>
            <p class="user-role">Resident | Purok 8</p>
            <p class="user-location">
                <i class="bi bi-geo-alt-fill"></i>
                <?php echo $user_fname; ?>'s Barangay
            </p>
        </div>

        <!-- Menu -->
        <nav class="sidebar-nav">
            <p class="nav-label">MENU</p>
            <ul>
                <li class="active">
                    <a href="index.php"><i class="bi bi-house-fill"></i> Home</a>
                </li>
                <li>
                    <a href="#"><i class="bi bi-exclamation-triangle-fill"></i> Report Incidents</a>
                </li>
                <li>
                    <a href="#"><i class="bi bi-clock-history"></i> My Reports</a>
                </li>
                <li>
                    <a href="#"><i class="bi bi-geo-alt-fill"></i> Incident Map</a>
                </li>
            </ul>

            <p class="nav-label">INFORMATION</p>
            <ul>
                <li>
                    <a href="#"><i class="bi bi-megaphone-fill"></i> Announcement</a>
                </li>
                <li>
                    <a href="#"><i class="bi bi-telephone-fill"></i> Hotlines</a>
                </li>
                <li>
                    <a href="#"><i class="bi bi-gear-fill"></i> Settings</a>
                </li>
            </ul>
        </nav>

        <!-- Logout -->
        <div class="sidebar-logout">
            <a href="Registration/logout.php"><i class="bi bi-box-arrow-right"></i> Log Out</a>
        </div>

    </aside>

    <!-- ================================ -->
    <!-- MAIN CONTENT                     -->
    <!-- ================================ -->
    <main class="main-content">

        <!-- Top Header -->
        <div class="top-header">
            <div class="header-greeting">
                <h1><?php echo $greeting . ', ' . $user_fname; ?></h1>
                <p><?php echo $date_today; ?> — Brgy. San Pablo - Sto. Tomas City</p>
            </div>
            <div class="header-actions">
                <button class="sos-btn">SOS</button>
                <button class="notif-btn"><i class="bi bi-bell-fill"></i></button>
            </div>
        </div>

        <!-- Weather Banner (Placeholder) -->
        <div class="weather-banner">
            <i class="bi bi-cloud-sun-fill"></i>
            <span>Weather alert information will appear here.</span>
        </div>

        <!-- Emergency Report Buttons -->
        <p class="section-label">REPORT AN EMERGENCY</p>
        <div class="emergency-grid">

            <div class="emergency-card fire">
                <img src="assets/Fire.svg" alt="Fire">
                <p>Tap to report</p>
            </div>

            <div class="emergency-card flood">
                <img src="assets/Vector.svg" alt="Flood">
                <p>Tap to report</p>
            </div>

            <div class="emergency-card alert">
                <img src="assets/Police.svg" alt="Alert">
                <p>Tap to report</p>
            </div>

            <div class="emergency-card medical">
                <img src="assets/Medical.svg" alt="Medical">
                <p>Tap to report</p>
            </div>

        </div>

        <!-- Bottom Two Columns -->
        <div class="bottom-grid">

            <!-- My Incident Reports -->
            <div class="card">
                <h2 class="card-title">
                    <i class="bi bi-list-ul"></i> My incident reports
                </h2>

                <div class="report-item">
                    <span class="dot dot-orange"></span>
                    <div class="report-info">
                        <p class="report-name">Flooding — Purok 3 road</p>
                        <p class="report-loc"><i class="bi bi-geo-alt"></i> Near barangay hall</p>
                    </div>
                    <div class="report-right">
                        <span class="badge badge-responding">Responding</span>
                        <p class="report-time">Today, 7:42 AM</p>
                    </div>
                </div>

                <div class="report-item">
                    <span class="dot dot-green"></span>
                    <div class="report-info">
                        <p class="report-name">Fallen tree — Main road</p>
                        <p class="report-loc"><i class="bi bi-geo-alt"></i> Brgy. boundary</p>
                    </div>
                    <div class="report-right">
                        <span class="badge badge-resolved">Resolved</span>
                        <p class="report-time">June 7, 4:18 PM</p>
                    </div>
                </div>

                <div class="report-item">
                    <span class="dot dot-red"></span>
                    <div class="report-info">
                        <p class="report-name">Fire — abandoned house</p>
                        <p class="report-loc"><i class="bi bi-geo-alt"></i> Sitio Malaya</p>
                    </div>
                    <div class="report-right">
                        <span class="badge badge-pending">Pending</span>
                        <p class="report-time">June 6, 9:05 PM</p>
                    </div>
                </div>

            </div>

            <!-- Announcements -->
            <div class="card">
                <h2 class="card-title">
                    <i class="bi bi-megaphone-fill"></i> Announcements
                </h2>

                <div class="announcement-item">
                    <div class="announce-icon"><i class="bi bi-building"></i></div>
                    <div class="announce-info">
                        <p class="announce-sender">Barangay Captain</p>
                        <p class="announce-msg">Evacuation center at Sta. Cruz Elementary is now open. Please bring IDs and essentials.</p>
                        <p class="announce-time">8:00 AM today</p>
                    </div>
                </div>

                <div class="announcement-item">
                    <div class="announce-icon"><i class="bi bi-building"></i></div>
                    <div class="announce-info">
                        <p class="announce-sender">DRRM Office</p>
                        <p class="announce-msg">Pre-emptive evacuation for flood-prone areas Purok 2 & 4 begins at 6 PM.</p>
                        <p class="announce-time">Yesterday, 3:45 PM</p>
                    </div>
                </div>

                <div class="announcement-item">
                    <div class="announce-icon"><i class="bi bi-building"></i></div>
                    <div class="announce-info">
                        <p class="announce-sender">Barangay Hall</p>
                        <p class="announce-msg">Free relief goods distribution on June 9. Bring your barangay ID.</p>
                        <p class="announce-time">June 6, 10:00 AM</p>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <script src="script.js"></script>
</body>

</html>