<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Registration/registration.php");
    exit();
}

require_once '../Registration/db.php';
require_once '../Registration/user_context.php';

$user_id = (int) $_SESSION['user_id'];
$user_context = loadCurrentUserContext($conn, $user_id);
$user = $user_context['user'];
$user_location = $user_context['location'];

$prefix   = ($user['gender'] === 'Female') ? 'Ms.' : 'Mr.';
$fullname = $prefix . ' ' . $user['fname'] . ' ' . $user['lname'];

$success_message = $_SESSION['success_message'] ?? '';
$error_message   = $_SESSION['error_message']   ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>survAIval - Settings</title>
    <link rel="icon" type="image/png" href="<?php echo '../assets/logo-s.svg'; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="../assets/logo.svg" alt="survAIval Logo">
        </div>
        <div class="user-card">
            <p class="user-name"><?php echo $user['fname'] . ' ' . $user['lname']; ?></p>
            <p class="user-role">Resident</p>
            <p class="user-location"><i class="bi bi-geo-alt-fill"></i> <?php echo htmlspecialchars($user_location); ?></p>
        </div>
        <nav class="sidebar-nav">
            <p class="nav-label">MENU</p>
            <ul>
                <li><a href="../index.php"><i class="bi bi-house-fill"></i> Home</a></li>
                <li><a href="../Report_Incidents/report_incidents.php"><i class="bi bi-exclamation-triangle-fill"></i> Report Incidents</a></li>
                <li><a href="../My_Reports/my_reports.php"><i class="bi bi-clock-history"></i> My Reports</a></li>
                <li><a href="../Incident_Map/incident_map.php"><i class="bi bi-geo-alt-fill"></i> Incident Map</a></li>
            </ul>
            <p class="nav-label">INFORMATION</p>
            <ul>
                <li><a href="../Announcement/announcement.php"><i class="bi bi-megaphone-fill"></i> Announcement</a></li>
                <li><a href="../Hotlines/hotlines.php"><i class="bi bi-telephone-fill"></i> Hotlines</a></li>
                <li class="active"><a href="settings.php"><i class="bi bi-gear-fill"></i> Settings</a></li>
            </ul>
        </nav>
        <div class="sidebar-logout">
            <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Log Out</a>
        </div>
    </aside>

    <main class="main-content">

        <div class="top-header">
            <div class="header-text">
                <p class="header-subtitle">Barangay Smart Disaster Risk Monitoring</p>
                <p class="header-date"><?php echo date('l, F j, Y'); ?> - Brgy. <?php echo htmlspecialchars($user_location); ?></p>
            </div>
            <div class="header-actions">
                <button class="sos-btn">SOS</button>
                <button class="notif-btn"><i class="bi bi-bell-fill"></i></button>
            </div>
        </div>

        <h1 class="page-title">Settings</h1>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="settings-card">
            <p class="card-label">USER PROFILE</p>

            <a href="../Settings/Profile/profile.php" class="settings-row">
                <div class="row-left">
                    <div class="row-icon"><i class="bi bi-person-fill"></i></div>
                    <div class="row-info">
                        <p class="row-title"><?php echo $fullname; ?></p>
                        <p class="row-sub"><?php echo $user['email']; ?> | <?php echo $user['cellphone_no']; ?></p>
                    </div>
                </div>
                <i class="bi bi-chevron-right row-arrow"></i>
            </a>

            <div class="row-divider"></div>

            <button class="settings-row" onclick="openBarangayModal()">
                <div class="row-left">
                    <div class="row-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <div class="row-info">
                        <p class="row-title">Barangay</p>
                        <p class="row-sub"><?php echo $user['barangay'] . ', ' . $user['city']; ?> — LGU verified account</p>
                    </div>
                </div>
                <i class="bi bi-chevron-right row-arrow"></i>
            </button>
        </div>

        <div class="settings-card">
            <p class="card-label">SYSTEM NOTIFICATION</p>

            <div class="settings-row no-hover">
                <div class="row-left">
                    <div class="row-icon"><i class="bi bi-chat-left-text-fill"></i></div>
                    <div class="row-info">
                        <p class="row-title">SMS broadcast alerts</p>
                        <p class="row-sub">Receive SMS notification</p>
                    </div>
                </div>
                <label class="toggle">
                    <input type="checkbox" id="sms_alerts" onchange="saveSetting('sms_alerts', this.checked)">
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="row-divider"></div>

            <div class="settings-row no-hover">
                <div class="row-left">
                    <div class="row-icon"><i class="bi bi-volume-up-fill"></i></div>
                    <div class="row-info">
                        <p class="row-title">Sound alarm trigger</p>
                        <p class="row-sub">Alarm on emergencies</p>
                    </div>
                </div>
                <label class="toggle">
                    <input type="checkbox" id="sound_alarm" onchange="saveSetting('sound_alarm', this.checked)">
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="row-divider"></div>

            <div class="settings-row no-hover">
                <div class="row-left">
                    <div class="row-icon"><i class="bi bi-robot"></i></div>
                    <div class="row-info">
                        <p class="row-title">ValAI</p>
                        <p class="row-sub">Open AI chatbot</p>
                    </div>
                </div>
                <label class="toggle">
                    <input type="checkbox" id="valai" checked disabled>
                    <span class="toggle-slider disabled"></span>
                </label>
            </div>

            <div class="row-divider"></div>

            <div class="settings-row no-hover">
                <div class="row-left">
                    <div class="row-icon"><i class="bi bi-bell-fill"></i></div>
                    <div class="row-info">
                        <p class="row-title">Push notifications</p>
                        <p class="row-sub">Receive alerts on this device</p>
                    </div>
                </div>
                <label class="toggle">
                    <input type="checkbox" id="push_notifications" onchange="saveSetting('push_notifications', this.checked)">
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <div class="settings-card">
            <p class="card-label">SECURITY</p>

            <a href="../Settings/Password/change_password.php" class="settings-row">
                <div class="row-left">
                    <div class="row-icon"><i class="bi bi-lock-fill"></i></div>
                    <div class="row-info">
                        <p class="row-title">Change password</p>
                    </div>
                </div>
                <i class="bi bi-chevron-right row-arrow"></i>
            </a>

            <div class="row-divider"></div>

            <div class="settings-row no-hover">
                <div class="row-left">
                    <div class="row-icon"><i class="bi bi-shield-check-fill"></i></div>
                    <div class="row-info">
                        <p class="row-title">Two-factor authentication</p>
                        <p class="row-sub">Enabled via SMS OTP</p>
                    </div>
                </div>
                <label class="toggle">
                    <input type="checkbox" id="two_factor" onchange="saveSetting('two_factor', this.checked)">
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

    </main>

    <div class="modal-overlay" id="modalOverlay" onclick="closeBarangayModal()"></div>
    <div class="modal" id="barangayModal">
        <div class="modal-header">
            <h3><i class="bi bi-geo-alt-fill"></i> Barangay Info</h3>
            <button onclick="closeBarangayModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <div class="info-row">
                <span class="info-label">Barangay</span>
                <span class="info-value"><?php echo $user['barangay']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">City / Town</span>
                <span class="info-value"><?php echo $user['city']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-value verified"><i class="bi bi-patch-check-fill"></i> LGU Verified Account</span>
            </div>
            <p class="modal-note">To update your barangay information, please contact your local barangay office.</p>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
