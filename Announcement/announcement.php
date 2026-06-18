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
$user_fname    = $user_context['user']['fname'];
$user_lname    = $user_context['user']['lname'];
$user_barangay = $user_context['barangay'];
$user_city     = $user_context['city'];
$user_location = $user_context['location'];

$date_today = date('l, F j, Y');

/* ------------------------------------------------------------
   Load announcements visible to this user.
   Rule: show global posts (barangay IS NULL) + posts for the
   user's own barangay. Newest first.
------------------------------------------------------------ */
$barangay_safe = mysqli_real_escape_string($conn, $user_barangay);

$sql = "
    SELECT announcement_id, title, body, barangay, created_at
    FROM tblannouncements
    WHERE barangay IS NULL OR barangay = '$barangay_safe'
    ORDER BY created_at DESC
";

$result = mysqli_query($conn, $sql);

$announcements = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $announcements[] = $row;
    }
}

/* ------------------------------------------------------------
   Categorize a post by scanning title + body for keywords.
   Returns: icon, color, label (chip text), filter (bucket)
------------------------------------------------------------ */
function announce_meta($title, $body) {
    $haystack = strtolower($title . ' ' . $body);

    if (preg_match('/\b(fire|smoke|burn)\b/', $haystack)) {
        return ['icon' => 'bi-fire', 'color' => 'chip-red', 'label' => 'Fire', 'filter' => 'disaster'];
    }
    if (preg_match('/\b(earthquake|tremor)\b/', $haystack)) {
        return ['icon' => 'bi-globe-asia-australia', 'color' => 'chip-orange', 'label' => 'Earthquake', 'filter' => 'disaster'];
    }
    if (preg_match('/\b(flood|flooding)\b/', $haystack)) {
        return ['icon' => 'bi-cloud-rain-heavy', 'color' => 'chip-blue', 'label' => 'Flood', 'filter' => 'disaster'];
    }
    if (preg_match('/\b(storm|rain|typhoon|signal|weather|power interruption|power interuption)\b/', $haystack)) {
        return ['icon' => 'bi-cloud-rain-heavy', 'color' => 'chip-blue', 'label' => 'Weather', 'filter' => 'weather'];
    }
    if (preg_match('/\b(medical|health|vacc|clinic)\b/', $haystack)) {
        return ['icon' => 'bi-heart-pulse-fill', 'color' => 'chip-green', 'label' => 'Health', 'filter' => 'reminders'];
    }
    if (preg_match('/\b(meeting|assembly|brgy|barangay|invite)\b/', $haystack)) {
        return ['icon' => 'bi-people-fill', 'color' => 'chip-purple', 'label' => 'Community', 'filter' => 'reminders'];
    }

    return ['icon' => 'bi-megaphone-fill', 'color' => 'chip-green', 'label' => 'Notice', 'filter' => 'reminders'];
}

/* ------------------------------------------------------------
   Build the Open-Meteo request URL.
   Open-Meteo is free, requires no API key, and returns daily
   forecasts by lat/lng. The browser will fetch this directly;
   we only assemble the URL server-side for safety.
------------------------------------------------------------ */
$weather_query = http_build_query([
    'latitude'      => 14.5995,
    'longitude'     => 120.9842,
    'current'       => 'temperature_2m,weather_code',
    'daily'         => 'weather_code,temperature_2m_max,temperature_2m_min',
    'timezone'      => 'auto',
    'forecast_days' => 3,
]);
$weather_api_url = 'https://api.open-meteo.com/v1/forecast?' . $weather_query;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>survAIval - Announcement</title>
    <link rel="icon" type="image/png" href="<?php echo '../assets/logo-s.svg'; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../style.css">
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
        <p class="user-location">
            <i class="bi bi-geo-alt-fill"></i>
            <?php echo htmlspecialchars($user_location); ?>
        </p>
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
            <li class="active"><a href="announcement.php"><i class="bi bi-megaphone-fill"></i> Announcement</a></li>
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
            <p class="header-date"><?php echo $date_today; ?> - Brgy. <?php echo htmlspecialchars($user_location); ?></p>
        </div>
        <div class="header-actions">
            <button class="sos-btn">SOS</button>
            <button class="notif-btn"><i class="bi bi-bell-fill"></i></button>
        </div>
    </div>

    <h1 class="page-title">Announcement</h1>

    <div class="announce-layout">
        <div class="announce-main">

            <div class="announce-toolbar">
                <div class="announce-search">
                    <i class="bi bi-search"></i>
                    <input
                        type="text"
                        id="announceSearch"
                        placeholder="Search announcements..."
                        autocomplete="off"
                    >
                </div>
                <div class="announce-filters" id="announceFilters">
                    <button class="filter-chip active" data-filter="all">All</button>
                    <button class="filter-chip" data-filter="reminders">Reminders</button>
                    <button class="filter-chip" data-filter="weather">Weather</button>
                    <button class="filter-chip" data-filter="disaster">Disaster</button>
                </div>
            </div>

            <div class="announce-grid" id="announceGrid">
                <?php if (empty($announcements)): ?>
                    <div class="empty-state" id="emptyState">
                        <i class="bi bi-megaphone"></i>
                        <p class="empty-title">No announcements yet</p>
                        <p class="empty-sub">
                            You're all caught up. New barangay notices will appear here.
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $a): ?>
                        <?php $meta = announce_meta($a['title'], $a['body']); ?>
                        <div class="announce-card-item"
                             data-filter="<?php echo $meta['filter']; ?>"
                             data-search="<?php echo htmlspecialchars(mb_strtolower($a['title'] . ' ' . $a['body']), ENT_QUOTES); ?>"
                             onclick="openAnnouncement(<?php echo htmlspecialchars(json_encode([
                                 'id'         => (int) $a['announcement_id'],
                                 'title'      => $a['title'],
                                 'body'       => $a['body'],
                                 'barangay'   => $a['barangay'],
                                 'created_at' => $a['created_at'],
                                 'icon'       => $meta['icon'],
                                 'color'      => $meta['color'],
                                 'label'      => $meta['label'],
                             ])); ?>)">
                            <div class="card-top-row">
                                <span class="card-kicker <?php echo $meta['color']; ?>"><?php echo htmlspecialchars($meta['label']); ?></span>
                                <div class="card-icon-wrap <?php echo $meta['color']; ?>">
                                    <i class="bi <?php echo $meta['icon']; ?>"></i>
                                </div>
                            </div>
                            <h3 class="card-title"><?php echo htmlspecialchars($a['title']); ?></h3>
                            <p class="card-date">
                                <?php echo date('F j, Y', strtotime($a['created_at'])); ?>
                                <span class="dot-sep">|</span>
                                <?php echo date('g:i A', strtotime($a['created_at'])); ?>
                            </p>
                            <p class="card-preview">
                                <?php echo htmlspecialchars(mb_strimwidth($a['body'], 0, 140, '…')); ?>
                            </p>
                            <button class="card-readmore" type="button" tabindex="-1">Read More</button>
                        </div>
                    <?php endforeach; ?>
                    <p class="no-results" id="noResults" style="display:none;">
                        No announcements match your search or filter.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <aside class="weather-sidebar">
            <div class="weather-card">
                <div class="weather-card-header">
                    <h3 class="weather-title">Weather Outlook</h3>
                    <span class="weather-location" id="weatherLocation">
                        <i class="bi bi-geo-alt-fill"></i>
                        <?php echo htmlspecialchars($user_city); ?>
                    </span>
                </div>

                <!-- Skeleton shown until the API responds. JS replaces it. -->
                <div class="weather-list" id="weatherOutlookList">
                    <div class="weather-row weather-skeleton">
                        <div class="weather-day-group">
                            <div class="weather-day"><span class="day-label">&nbsp;</span><div class="skel-circle"></div><span class="day-temp">--°</span></div>
                            <div class="weather-day"><span class="day-label">&nbsp;</span><div class="skel-circle"></div><span class="day-temp">--°</span></div>
                            <div class="weather-day"><span class="day-label">&nbsp;</span><div class="skel-circle"></div><span class="day-temp">--°</span></div>
                        </div>
                        <p class="weather-risk">Loading forecast…</p>
                    </div>
                </div>

                <p class="weather-attribution">
                    Data from <a href="https://open-meteo.com" target="_blank" rel="noopener">Open-Meteo</a>
                </p>
            </div>
        </aside>
    </div>
</main>

<!-- View modal -->
<div class="modal-overlay" id="announceOverlay" onclick="closeAnnouncement()"></div>
<div class="announce-modal" id="announceModal" role="dialog" aria-modal="true">
    <div class="announce-modal-header">
        <div class="announce-modal-icon" id="modalIconWrap">
            <i id="modalIcon" class="bi bi-megaphone-fill"></i>
        </div>
        <button class="modal-close" onclick="closeAnnouncement()" aria-label="Close">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="announce-modal-body">
        <span class="announce-chip" id="modalChip">Notice</span>
        <h2 class="announce-modal-title" id="modalTitle">—</h2>
        <p class="announce-modal-meta">
            <i class="bi bi-clock"></i> <span id="modalDate">—</span>
            <span class="dot-sep">•</span>
            <i class="bi bi-geo-alt"></i> <span id="modalLocation">—</span>
        </p>
        <div class="announce-modal-text" id="modalBody">—</div>
    </div>
</div>

<!-- Weather config consumed by script.js -->
<script>
    window.SURVAIVAL_WEATHER = {
        lat: 14.5995,
        lng: 120.9842,
        cityLabel: <?php echo json_encode($user_city); ?>,
        apiUrl: <?php echo json_encode($weather_api_url); ?>
    };
</script>
<script src="script.js"></script>
</body>
</html>