<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Registration/registration.php");
    exit();
}

require_once '../../Registration/db.php';

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn, "SELECT * FROM tblusers WHERE user_id = '$user_id'");
$user   = mysqli_fetch_assoc($result);

$prefix   = ($user['gender'] === 'Female') ? 'Ms.' : 'Mr.';
$fullname = $user['fname'] . ' ' . ($user['mname'] ? $user['mname'] . ' ' : '') . $user['lname'];

$avatar_letter = strtoupper(substr($user['fname'], 0, 1));

$colors = [
    'A' => '#9b59b6', 'B' => '#2980b9', 'C' => '#27ae60', 'D' => '#e67e22',
    'E' => '#c0392b', 'F' => '#16a085', 'G' => '#8e44ad', 'H' => '#2c3e50',
    'I' => '#d35400', 'J' => '#1abc9c', 'K' => '#e74c3c', 'L' => '#3498db',
    'M' => '#9b59b6', 'N' => '#27ae60', 'O' => '#f39c12', 'P' => '#2ecc71',
    'Q' => '#e67e22', 'R' => '#9b59b6', 'S' => '#2980b9', 'T' => '#16a085',
    'U' => '#8e44ad', 'V' => '#c0392b', 'W' => '#1abc9c', 'X' => '#d35400',
    'Y' => '#3498db', 'Z' => '#27ae60',
];
$avatar_color = $colors[$avatar_letter] ?? '#2d5a27';

$success_message = $_SESSION['success_message'] ?? '';
$error_message   = $_SESSION['error_message']   ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

$log_result = mysqli_query($conn, "
    SELECT incident_title, incident_type, status, created_at, updated_at
    FROM tblreports
    WHERE user_id = '$user_id'
    ORDER BY updated_at DESC
    LIMIT 8
");
$action_logs = [];
while ($row = mysqli_fetch_assoc($log_result)) {
    $action_logs[] = $row;
}

function time_ago($datetime) {
    $now   = new DateTime();
    $past  = new DateTime($datetime);
    $diff  = $now->diff($past);
    $mins  = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
    if ($mins < 1)  return 'just now';
    if ($mins < 60) return $mins . 'm ago';
    if ($diff->days < 1) return floor($mins / 60) . 'h ago';
    return $diff->days . 'd ago';
}

function getActionLabel($status) {
    switch ($status) {
        case 'draft':      return ['Saved Draft',       'log-draft'];
        case 'pending':    return ['Submitted Report',  'log-success'];
        case 'responding': return ['Report Responding', 'log-responding'];
        case 'resolved':   return ['Report Resolved',   'log-resolved'];
        default:           return ['Activity',          'log-draft'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>survAIval - Profile</title>
    <link rel="icon" type="image/png" href="<?php echo '../../assets/logo-s.svg'; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="../../assets/logo.svg" alt="survAIval Logo">
        </div>
        <div class="user-card">
            <p class="user-name"><?php echo $user['fname'] . ' ' . $user['lname']; ?></p>
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

        <a href="../settings.php" class="back-btn">
            <i class="bi bi-arrow-left"></i> Back to Settings
        </a>

        <h1 class="page-title">Profile</h1>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="profile-layout">

            <div class="profile-card">
                <p class="card-label">Account Profile</p>

                <div class="avatar-wrap">
                    <?php if (!empty($user['profile_pic'])): ?>
                        <img src="../../<?php echo $user['profile_pic']; ?>"
                             alt="Profile Photo" class="avatar-img">
                    <?php else: ?>
                        <div class="avatar-letter"
                             style="background-color: <?php echo $avatar_color; ?>">
                            <?php echo $avatar_letter; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <p class="profile-display-name"><?php echo $user['fname']; ?></p>

                <div class="profile-info">
                    <div class="info-row">
                        <span class="info-label">Name</span>
                        <span class="info-value"><?php echo $prefix . ' ' . $fullname; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo $user['email']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Contact</span>
                        <span class="info-value"><?php echo $user['cellphone_no']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gender</span>
                        <span class="info-value"><?php echo $user['gender']; ?></span>
                    </div>
                </div>

                <button class="edit-btn" onclick="openEditModal()">
                    <i class="bi bi-pencil-fill"></i> Edit Profile
                </button>
            </div>

            <div class="right-column">

                <div class="platform-card">
                    <div class="platform-layout">

                        <div class="platform-left">
                            <p class="card-label">Platform Reference</p>

                            <div class="pref-group">
                                <label class="pref-label">Language</label>
                                <select class="pref-select" disabled>
                                    <option>English</option>
                                </select>
                                <p class="pref-note">More languages coming soon</p>
                            </div>

                            <div class="pref-group">
                                <label class="pref-label">Time Format</label>
                                <select class="pref-select" id="timeFormat" onchange="saveTimeFormat(this.value)">
                                    <option value="12hr">12-Hour (AM/PM)</option>
                                    <option value="24hr">24-Hour (Military)</option>
                                </select>
                            </div>
                        </div>

                        <div class="platform-right">
                            <p class="card-label">User Action Log</p>

                            <?php if (empty($action_logs)): ?>
                                <p class="no-log">No activity yet.</p>
                            <?php else: ?>
                                <?php foreach ($action_logs as $log):
                                    [$label, $badge_class] = getActionLabel($log['status']);
                                ?>
                                    <div class="log-item">
                                        <div class="log-info">
                                            <p class="log-action"><?php echo $label; ?></p>
                                            <p class="log-title"><?php echo htmlspecialchars($log['incident_title']); ?></p>
                                        </div>
                                        <div class="log-meta">
                                            <p class="log-time"><?php echo time_ago($log['updated_at']); ?></p>
                                            <span class="log-badge <?php echo $badge_class; ?>">
                                                <?php echo strtoupper($log['status'] === 'pending' ? 'SENT' : $log['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>

    <div class="modal-overlay" id="modalOverlay" onclick="closeEditModal()"></div>
    <div class="modal" id="editModal">
        <div class="modal-header">
            <h3><i class="bi bi-pencil-fill"></i> Edit Profile</h3>
            <button onclick="closeEditModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <form method="POST" action="profile_process.php" enctype="multipart/form-data">

                <div class="photo-upload-wrap">
                    <?php if (!empty($user['profile_pic'])): ?>
                        <img src="../../<?php echo $user['profile_pic']; ?>"
                             id="photoPreview" class="photo-preview" alt="Preview">
                    <?php else: ?>
                        <div class="avatar-letter small"
                             id="avatarFallback"
                             style="background-color: <?php echo $avatar_color; ?>">
                            <?php echo $avatar_letter; ?>
                        </div>
                        <img id="photoPreview" class="photo-preview" alt="Preview" style="display:none;">
                    <?php endif; ?>

                    <label class="photo-upload-btn">
                        <i class="bi bi-camera-fill"></i> Change Photo
                        <input type="file" name="profile_pic" id="photoInput"
                               accept="image/*" onchange="previewPhoto(this)">
                    </label>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>First Name <span class="required">*</span></label>
                        <input type="text" name="fname" class="input-field"
                               value="<?php echo $user['fname']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name <span class="required">*</span></label>
                        <input type="text" name="lname" class="input-field"
                               value="<?php echo $user['lname']; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Middle Name <span class="optional">(Optional)</span></label>
                    <input type="text" name="mname" class="input-field"
                           value="<?php echo $user['mname']; ?>">
                </div>

                <div class="form-group">
                    <label>Cellphone Number <span class="required">*</span></label>
                    <input type="tel" name="cellphone_no" class="input-field"
                           value="<?php echo $user['cellphone_no']; ?>" required>
                </div>

                <div class="locked-info">
                    <i class="bi bi-lock-fill"></i>
                    Email and barangay info cannot be changed here.
                </div>

                <button type="submit" class="submit-btn">Save Changes</button>

            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
