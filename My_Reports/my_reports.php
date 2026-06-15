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
$user_fname = $user_context['user']['fname'];
$user_lname = $user_context['user']['lname'];
$user_location = $user_context['location'];

$filter        = $_GET['status'] ?? 'all';
$date_today    = date('l, F j, Y');

if ($filter !== 'all') {
    $filter_safe = mysqli_real_escape_string($conn, $filter);
    $sql = "SELECT * FROM tblreports WHERE user_id = '$user_id' AND status = '$filter_safe' ORDER BY created_at DESC";
} else {
    $sql = "SELECT * FROM tblreports WHERE user_id = '$user_id' AND status != 'draft' ORDER BY created_at DESC";
}

$result  = mysqli_query($conn, $sql);
$reports = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reports[] = $row;
}

function getStatusLabel($status) {
    switch ($status) {
        case 'pending':    return 'Reported';
        case 'responding': return 'Responding';
        case 'resolved':   return 'Resolved';
        default:           return ucfirst($status);
    }
}

function getStatusClass($status) {
    switch ($status) {
        case 'pending':    return 'status-reported';
        case 'responding': return 'status-responding';
        case 'resolved':   return 'status-resolved';
        default:           return '';
    }
}

function getDotClass($status) {
    switch ($status) {
        case 'pending':    return 'dot-red';
        case 'responding': return 'dot-orange';
        case 'resolved':   return 'dot-green';
        default:           return '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>survAIval - My Reports</title>
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
            <p class="user-name"><?php echo $user_fname . ' ' . $user_lname; ?></p>
            <p class="user-role">Resident</p>
            <p class="user-location"><i class="bi bi-geo-alt-fill"></i> <?php echo htmlspecialchars($user_location); ?></p>
        </div>

        <nav class="sidebar-nav">
            <p class="nav-label">MENU</p>
            <ul>
                <li>
                    <a href="../index.php"><i class="bi bi-house-fill"></i> Home</a>
                </li>
                <li>
                    <a href="../Report_Incidents/report_incidents.php"><i class="bi bi-exclamation-triangle-fill"></i> Report Incidents</a>
                </li>
                <li class="active">
                    <a href="../My_Reports/my_reports.php"><i class="bi bi-clock-history"></i> My Reports</a>
                </li>
                <li>
                    <a href="../Incident_Map/incident_map.php"><i class="bi bi-geo-alt-fill"></i> Incident Map</a>
                </li>
            </ul>

            <p class="nav-label">INFORMATION</p>
            <ul>
                <li>
                    <a href="../Announcement/announcement.php"><i class="bi bi-megaphone-fill"></i> Announcement</a>
                </li>
                <li>
                    <a href="../Hotlines/hotlines.php"><i class="bi bi-telephone-fill"></i> Hotlines</a>
                </li>
                <li>
                    <a href="../Settings/settings.php"><i class="bi bi-gear-fill"></i> Settings</a>
                </li>
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
                <p class="header-date"><?php echo $date_today; ?> - Brgy. <?php echo htmlspecialchars($user_location); ?></p>
            </div>
            <div class="header-actions">
                <button class="sos-btn">SOS</button>
                <button class="notif-btn"><i class="bi bi-bell-fill"></i></button>
            </div>
        </div>

        <h1 class="page-title">My Reports</h1>

        <div class="reports-card">

            <div class="card-header-row">
                <p class="review-label">Review</p>
                <div class="filter-group">
                    <a href="my_reports.php"
                       class="filter-btn <?php echo $filter === 'all'        ? 'active' : ''; ?>">
                        All
                    </a>
                    <a href="my_reports.php?status=pending"
                       class="filter-btn <?php echo $filter === 'pending'    ? 'active' : ''; ?>">
                        Reported
                    </a>
                    <a href="my_reports.php?status=responding"
                       class="filter-btn <?php echo $filter === 'responding' ? 'active' : ''; ?>">
                        Responding
                    </a>
                    <a href="my_reports.php?status=resolved"
                       class="filter-btn <?php echo $filter === 'resolved'   ? 'active' : ''; ?>">
                        Resolved
                    </a>
                </div>
            </div>

            <div class="report-list">
                <?php if (empty($reports)): ?>
                    <div class="no-reports">
                        <i class="bi bi-inbox"></i>
                        <p>No reports found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reports as $report): ?>
                        <div class="report-row">

                            <div class="report-left">
                                <span class="dot <?php echo getDotClass($report['status']); ?>"></span>
                                <div class="report-info">
                                    <p class="report-title"><?php echo htmlspecialchars($report['incident_title']); ?></p>
                                    <p class="report-location">
                                        <i class="bi bi-geo-alt"></i>
                                        <?php echo htmlspecialchars($report['location']); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="report-right">
                                <div class="report-status-col">
                                    <span class="status-badge <?php echo getStatusClass($report['status']); ?>">
                                        <?php echo getStatusLabel($report['status']); ?>
                                    </span>
                                    <p class="report-date">
                                        <?php echo date('M j, g:i A', strtotime($report['created_at'])); ?>
                                    </p>
                                </div>
                                <button class="eye-btn"
                                        onclick="openModal(<?php echo htmlspecialchars(json_encode($report)); ?>)"
                                        title="View details">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>

    </main>

    <div class="modal-overlay" id="modalOverlay" onclick="closeModal()"></div>

    <div class="modal" id="reportModal">
        <div class="modal-header">
            <h3 id="modalTitle">—</h3>
            <button onclick="closeModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">

            <div class="modal-details">
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-tag-fill"></i> Type</span>
                    <span id="modalType">—</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-geo-alt-fill"></i> Location</span>
                    <span id="modalLocation">—</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-person-fill"></i> Reporter</span>
                    <span id="modalReporter">—</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-telephone-fill"></i> Contact</span>
                    <span id="modalContact">—</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-card-text"></i> Description</span>
                    <span id="modalDescription">—</span>
                </div>
            </div>

            <div id="modalPhotoWrap" class="modal-photo-wrap" style="display:none;">
                <p class="detail-label"><i class="bi bi-image"></i> Photo</p>
                <img id="modalPhoto" src="" alt="Incident Photo">
            </div>

            <div class="status-tracker">
                <p class="tracker-title">Status Tracker</p>
                <div class="tracker-steps">

                    <div class="tracker-step" id="step-reported">
                        <div class="step-dot"></div>
                        <div class="step-line"></div>
                        <p>Reported</p>
                    </div>

                    <div class="tracker-step" id="step-responding">
                        <div class="step-dot"></div>
                        <div class="step-line"></div>
                        <p>Responding</p>
                    </div>

                    <div class="tracker-step" id="step-resolved">
                        <div class="step-dot"></div>
                        <p>Resolved</p>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
