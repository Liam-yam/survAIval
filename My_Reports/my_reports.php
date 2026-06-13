<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Registration/registration.php");
    exit();
}

require_once '../Registration/db.php';

$user_id    = $_SESSION['user_id'];
$user_fname = $_SESSION['user_fname'];
$user_lname = $_SESSION['user_lname'];

// Filter by status
$filter        = $_GET['filter'] ?? 'all';
$date_today    = date('l, F j, Y');

$where = "WHERE user_id = '$user_id' AND status != 'draft'";
if ($filter !== 'all') {
    $filter_safe = mysqli_real_escape_string($conn, $filter);
    $where .= " AND status = '$filter_safe'";
}

$result  = mysqli_query($conn, "SELECT * FROM tblreports $where ORDER BY created_at DESC");
$reports = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reports[] = $row;
}

// Label mapping: DB value → UI label
function statusLabel($status) {
    $map = [
        'pending'    => 'Reported',
        'responding' => 'Responding',
        'resolved'   => 'Resolved',
    ];
    return $map[$status] ?? ucfirst($status);
}

// Dot color per status
function statusDot($status) {
    $map = [
        'pending'    => 'dot-red',
        'responding' => 'dot-orange',
        'resolved'   => 'dot-green',
    ];
    return $map[$status] ?? 'dot-gray';
}

// Badge class per status
function statusBadge($status) {
    $map = [
        'pending'    => 'badge-reported',
        'responding' => 'badge-responding',
        'resolved'   => 'badge-resolved',
    ];
    return $map[$status] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>survAIval - My Reports</title>
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

        <div class="sidebar-logo">
            <img src="../assets/logo.svg" alt="survAIval Logo">
        </div>

        <div class="user-card">
            <p class="user-name"><?php echo $user_fname . ' ' . $user_lname; ?></p>
            <p class="user-role">Resident | Purok 8</p>
            <p class="user-location"><i class="bi bi-geo-alt-fill"></i> San Pablo - Valle Pio</p>
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
                    <a href="my_reports.php"><i class="bi bi-clock-history"></i> My Reports</a>
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

        <div class="sidebar-logout">
            <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Log Out</a>
        </div>

    </aside>

    <!-- ================================ -->
    <!-- MAIN CONTENT                     -->
    <!-- ================================ -->
    <main class="main-content">

        <!-- Top Header -->
        <div class="top-header">
            <div class="header-text">
                <p class="header-subtitle">Barangay Smart Disaster Risk Monitoring</p>
                <p class="header-date"><?php echo $date_today; ?> — Brgy. San Pablo - Sto. Tomas City</p>
            </div>
            <div class="header-actions">
                <button class="sos-btn">SOS</button>
                <button class="notif-btn"><i class="bi bi-bell-fill"></i></button>
            </div>
        </div>

        <!-- Page Title -->
        <h1 class="page-title">My Reports</h1>

        <!-- Reports Card -->
        <div class="reports-card">

            <!-- Card Header -->
            <div class="card-header">
                <p class="card-label">Review</p>
                <div class="filter-wrapper">
                    <button class="filter-btn" id="filterBtn">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <!-- Filter Dropdown -->
                    <div class="filter-dropdown" id="filterDropdown">
                        <a href="my_reports.php?filter=all"        class="<?php echo $filter === 'all'        ? 'active' : ''; ?>">All</a>
                        <a href="my_reports.php?filter=pending"    class="<?php echo $filter === 'pending'    ? 'active' : ''; ?>">Reported</a>
                        <a href="my_reports.php?filter=responding" class="<?php echo $filter === 'responding' ? 'active' : ''; ?>">Responding</a>
                        <a href="my_reports.php?filter=resolved"   class="<?php echo $filter === 'resolved'   ? 'active' : ''; ?>">Resolved</a>
                    </div>
                </div>
            </div>

            <!-- Report List -->
            <div class="report-list">
                <?php if (empty($reports)): ?>
                    <div class="no-reports">
                        <i class="bi bi-inbox"></i>
                        <p>No reports found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reports as $report): ?>
                        <div class="report-row">

                            <span class="dot <?php echo statusDot($report['status']); ?>"></span>

                            <div class="report-info">
                                <p class="report-title"><?php echo htmlspecialchars($report['incident_title']); ?></p>
                                <p class="report-loc">
                                    <i class="bi bi-geo-alt"></i>
                                    <?php echo htmlspecialchars($report['location']); ?>
                                </p>
                            </div>

                            <div class="report-center">
                                <span class="badge <?php echo statusBadge($report['status']); ?>">
                                    <?php echo statusLabel($report['status']); ?>
                                </span>
                                <p class="report-time">
                                    <?php echo date('M j, g:i A', strtotime($report['created_at'])); ?>
                                </p>
                            </div>

                            <!-- Eye icon — opens modal -->
                            <button class="eye-btn"
                                onclick="openModal(<?php echo htmlspecialchars(json_encode($report)); ?>)">
                                <i class="bi bi-eye"></i>
                            </button>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>

    </main>

    <!-- ================================ -->
    <!-- REPORT DETAIL MODAL              -->
    <!-- ================================ -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal()"></div>
    <div class="modal" id="reportModal">

        <div class="modal-header">
            <h3 id="modalTitle">Report Details</h3>
            <button onclick="closeModal()"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="modal-body">

            <!-- Report Info -->
            <div class="modal-info-grid">
                <div>
                    <p class="modal-label">Incident Type</p>
                    <p class="modal-value" id="modalType">—</p>
                </div>
                <div>
                    <p class="modal-label">Location</p>
                    <p class="modal-value" id="modalLocation">—</p>
                </div>
                <div>
                    <p class="modal-label">Reporter</p>
                    <p class="modal-value" id="modalReporter">—</p>
                </div>
                <div>
                    <p class="modal-label">Contact</p>
                    <p class="modal-value" id="modalContact">—</p>
                </div>
            </div>

            <div class="modal-desc-group">
                <p class="modal-label">Description</p>
                <p class="modal-value" id="modalDesc">—</p>
            </div>

            <!-- Photo -->
            <div id="modalPhotoWrap" style="display:none;">
                <p class="modal-label">Photo</p>
                <img id="modalPhoto" src="" alt="Report Photo" class="modal-photo">
            </div>

            <!-- Status Tracker -->
            <div class="status-tracker">
                <p class="modal-label">Status Tracker</p>
                <div class="tracker-steps">

                    <div class="tracker-step" id="step-reported">
                        <span class="tracker-dot"></span>
                        <span class="tracker-label">Reported</span>
                    </div>

                    <div class="tracker-line" id="line-responding"></div>

                    <div class="tracker-step" id="step-responding">
                        <span class="tracker-dot"></span>
                        <span class="tracker-label">Responding</span>
                    </div>

                    <div class="tracker-line" id="line-resolved"></div>

                    <div class="tracker-step" id="step-resolved">
                        <span class="tracker-dot"></span>
                        <span class="tracker-label">Resolved</span>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>