s
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
$user_fname   = $user_context['user']['fname'];
$user_lname   = $user_context['user']['lname'];
$user_location = $user_context['location'];

// Default center: Barangay San Pablo, Sto. Tomas, Batangas
$map_center_lat = 14.1074;
$map_center_lng = 121.1416;

// Whitelist status filter (also avoids SQL injection)
$allowed_filters = ['pending', 'responding', 'resolved', 'all'];
$filter = $_GET['status'] ?? 'all';
if (!in_array($filter, $allowed_filters, true)) {
    $filter = 'all';
}

// Pull reports for THIS user only — for now.
// (When you want community-wide: drop user_id and filter by barangay.)
$where  = "user_id = '$user_id'";
$where .= ($filter === 'all') ? " AND status != 'draft'" : " AND status = '$filter'";

$sql     = "SELECT * FROM tblreports WHERE $where ORDER BY created_at DESC";
$result  = mysqli_query($conn, $sql);
$reports = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reports[] = $row;
}

// Encode reports for JS use
$reports_json = json_encode($reports, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

function time_ago($datetime) {
    $now        = new DateTime();
    $created    = new DateTime($datetime);
    $diff       = $now->diff($created);
    $total_mins = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
    if ($total_mins < 1)        return "just now";
    elseif ($total_mins < 60)   return $total_mins . " minute" . ($total_mins > 1 ? "s" : "") . " ago";
    else {
        $hours = floor($total_mins / 60);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    }
}

function getTypeIcon($type) {
    switch ($type) {
        case 'Fire':    return '../assets/Fire.svg';
        case 'Flood':   return '../assets/Vector.svg';
        case 'Crime':   return '../assets/Police.svg';
        case 'Medical': return '../assets/Medical.svg';
        default:        return '';
    }
}

function getStatusLabel($status) {
    switch ($status) {
        case 'pending':    return 'Reported';
        case 'responding': return 'Responding';
        case 'resolved':   return 'Resolved';
        case 'verified':   return 'Verified';
        default:           return ucfirst($status);
    }
}

function getStatusClass($status) {
    switch ($status) {
        case 'pending':    return 'badge-reported';
        case 'responding': return 'badge-responding';
        case 'resolved':   return 'badge-resolved';
        default:           return '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>survAIval - Incident Map</title>
    <link rel="icon" type="image/png" href="<?php echo '../assets/logo-s.svg'; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Leaflet (OpenStreetMap) - free, no API key -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>

    <link rel="stylesheet" href="style.css">
</head>
<body>

    <aside class="sidebar">

        <div class="sidebar-logo">
            <img src="../assets/logo.svg" alt="survAIval Logo">
        </div>

        <div class="user-card">
            <p class="user-name"><?php echo htmlspecialchars($user_fname . ' ' . $user_lname); ?></p>
            <p class="user-role">Resident</p>
            <p class="user-location"><i class="bi bi-geo-alt-fill"></i> <?php echo htmlspecialchars($user_location); ?></p>
        </div>

        <nav class="sidebar-nav">
            <p class="nav-label">MENU</p>
            <ul>
                <li><a href="../index.php"><i class="bi bi-house-fill"></i> Home</a></li>
                <li><a href="../Report_Incidents/report_incidents.php"><i class="bi bi-exclamation-triangle-fill"></i> Report Incidents</a></li>
                <li><a href="../My_Reports/my_reports.php"><i class="bi bi-clock-history"></i> My Reports</a></li>
                <li class="active"><a href="incident_map.php"><i class="bi bi-geo-alt-fill"></i> Incident Map</a></li>
            </ul>

            <p class="nav-label">INFORMATION</p>
            <ul>
                <li><a href="../Announcement/announcement.php"><i class="bi bi-megaphone-fill"></i> Announcement</a></li>
                <li><a href="../Hotlines/hotlines.php"><i class="bi bi-telephone-fill"></i> Hotlines</a></li>
                <li><a href="../Settings/settings.php"><i class="bi bi-gear-fill"></i> Settings</a></li>
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

        <h1 class="page-title">Incident Map</h1>

        <div class="map-layout">

            <!-- LEFT: Incident list -->
            <div class="incident-list-card">

                <div class="list-header">
                    <p class="list-date"><?php echo date('F d, Y (l)'); ?></p>
                    <div class="filter-group">
                        <span class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                            <i class="bi bi-funnel"></i> <?php echo $filter === 'all' ? 'All Incidents' : 'Filtered'; ?>
                        </span>
                    </div>
                </div>

                <div class="filter-options">
                    <a href="incident_map.php"             class="filter-opt <?php echo $filter === 'all'        ? 'active' : ''; ?>">
                        <i class="bi bi-list-ul"></i> All
                    </a>
                    <a href="incident_map.php?status=pending"    class="filter-opt <?php echo $filter === 'pending'    ? 'active' : ''; ?>">
                        <span class="dot dot-red"></span> Reported
                    </a>
                    <a href="incident_map.php?status=responding" class="filter-opt <?php echo $filter === 'responding' ? 'active' : ''; ?>">
                        <span class="dot dot-orange"></span> Responding
                    </a>
                    <a href="incident_map.php?status=resolved"   class="filter-opt <?php echo $filter === 'resolved'   ? 'active' : ''; ?>">
                        <span class="dot dot-green"></span> Resolved
                    </a>
                </div>

                <div class="incident-list" id="incidentList">
                    <?php if (empty($reports)): ?>
                        <div class="no-reports">
                            <i class="bi bi-inbox"></i>
                            <p>No incidents found.</p>
                            <small>Submit a report with location to see it on the map.</small>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reports as $index => $report): ?>
                            <div class="incident-item"
                                 id="incident-<?php echo $index; ?>"
                                 data-report-id="<?php echo (int)$report['report_id']; ?>"
                                 data-lat="<?php echo htmlspecialchars($report['latitude']  ?? ''); ?>"
                                 data-lng="<?php echo htmlspecialchars($report['longitude'] ?? ''); ?>"
                                 onclick="highlightIncident(<?php echo $index; ?>, '<?php echo $report['status']; ?>')">

                                <div class="incident-left">
                                    <img src="<?php echo getTypeIcon($report['incident_type']); ?>"
                                         alt="<?php echo htmlspecialchars($report['incident_type']); ?>"
                                         class="type-icon">
                                    <div class="incident-info">
                                        <p class="incident-title">
                                            <?php echo strtoupper(htmlspecialchars($report['incident_type'])); ?>
                                            — <?php echo strtoupper(htmlspecialchars($report['incident_title'])); ?>
                                        </p>
                                        <p class="incident-location">
                                            <?php echo htmlspecialchars($report['location']); ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="incident-right">
                                    <span class="status-badge <?php echo getStatusClass($report['status']); ?>">
                                        <?php echo getStatusLabel($report['status']); ?>
                                    </span>
                                    <p class="incident-time"><?php echo time_ago($report['created_at']); ?></p>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>

            <!-- RIGHT: Map + address search -->
            <div class="map-card">

                <!-- Address search (Nominatim geocoding -> Leaflet map) -->
                <div class="map-search-bar">
                    <i class="bi bi-search"></i>
                    <input type="text"
                           id="addressSearch"
                           placeholder="Search an address in the Philippines..."
                           autocomplete="off">
                    <button id="searchBtn" class="search-btn">Search</button>
                </div>

                <!-- Inline status / feedback for the search bar -->
                <div id="searchStatus" class="search-status" role="status" aria-live="polite"></div>

                <!-- Real Leaflet map -->
                <div id="incidentMap" class="incident-map"></div>

                <!-- Map legend -->
                <div class="map-legend">
                    <div class="legend-item"><span class="dot dot-red"></span> Reported</div>
                    <div class="legend-item"><span class="dot dot-orange"></span> Responding</div>
                    <div class="legend-item"><span class="dot dot-green"></span> Resolved</div>
                </div>

            </div>

        </div>

    </main>

<!-- Toast container for inline feedback -->
    <div id="toastContainer" class="toast-container" aria-live="polite"></div>

    <script>
        window.SURVAIVAL_INCIDENT_MAP = {
            center: { lat: <?php echo $map_center_lat; ?>, lng: <?php echo $map_center_lng; ?> },
            label:  <?php echo json_encode('Barangay San Pablo, Sto. Tomas, Batangas'); ?>,
            reports: <?php echo $reports_json; ?>
        };
    </script>
    <script src="script.js"></script>
</body>
</html>