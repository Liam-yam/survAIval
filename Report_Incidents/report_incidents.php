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

$success_message = $_SESSION['success_message'] ?? '';
$error_message   = $_SESSION['error_message']   ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

$drafts = [];
$draft_result = mysqli_query($conn, "SELECT * FROM tblreports WHERE user_id = '$user_id' AND status = 'draft' ORDER BY updated_at DESC");
while ($row = mysqli_fetch_assoc($draft_result)) {
    $drafts[] = $row;
}

$prefill_titles = [
    'Fire'    => 'Fire Incident',
    'Flood'   => 'Flood Incident',
    'Crime'   => 'Crime Incident',
    'Medical' => 'Medical Emergency',
];

$allowed_types = ['Fire', 'Flood', 'Crime', 'Medical'];
$prefill_type  = '';
$prefill_title = '';

if (isset($_GET['type']) && in_array($_GET['type'], $allowed_types)) {
    $prefill_type  = $_GET['type'];
    $prefill_title = $prefill_titles[$prefill_type];
}

$date_today = date('l, F j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>survAIval - Report Incidents</title>
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
                <li class="active">
                    <a href="report_incidents.php"><i class="bi bi-exclamation-triangle-fill"></i> Report Incidents</a>
                </li>
                <li>
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

        <h1 class="page-title">Incident Report</h1>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="form-card">

            <div class="card-top-row">
                <h2 class="card-section-title">Report Details</h2>
                <button class="drafts-btn" id="draftsBtn" title="Saved Drafts">
                    <i class="bi bi-archive-fill"></i>
                    <?php if (count($drafts) > 0): ?>
                        <span class="draft-badge"><?php echo count($drafts); ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <form id="reportForm" method="POST" action="report_process.php" enctype="multipart/form-data">

                <input type="hidden" name="report_id" id="report_id" value="">
                <input type="hidden" name="action"    id="formAction" value="">

                <div class="form-layout">

                    <div class="form-left">

                        <div class="form-group">
                            <label>Reporter Name</label>
                            <input type="text" name="reporter_name" id="reporter_name"
                                   class="input-field" placeholder="Enter your name">
                        </div>

                        <div class="form-group">
                            <label>Incident Title</label>
                            <input type="text" name="incident_title" id="incident_title"
                                   class="input-field" placeholder="Brief title of the incident"
                                   value="<?php echo $prefill_title; ?>">
                        </div>

                                                <div class="form-group">
                            <label>Location</label>
                                                        <div class="location-row">
                                <div class="location-search">
                                    <i class="bi bi-search location-search-icon"></i>
                                    <input type="text" name="location" id="location"
                                           class="input-field location-input"
                                           placeholder="Search an address or place..."
                                           autocomplete="off"
                                           value="<?php echo htmlspecialchars($user_location); ?>">
                                    <span id="locationSpinner" class="location-spinner" aria-hidden="true"></span>
                                    <button type="button" id="locationClear" class="location-clear" aria-label="Clear search" title="Clear">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                    <ul id="locationSuggest" class="location-suggest" role="listbox" aria-label="Address suggestions"></ul>
                                </div>
                                <button type="button" id="locateBtn" class="locate-btn"
                                        title="Use my current location">
                                    <i class="bi bi-crosshair"></i> LOCATE
                                </button>
                            </div>
                            <input type="hidden" name="latitude"  id="latitude"  value="">
                            <input type="hidden" name="longitude" id="longitude" value="">
                            <p class="location-status" id="locationStatus">
                                <i class="bi bi-info-circle"></i>
                                <span>No location set. Search, click LOCATE, or pick on the map.</span>
                            </p>
                        </div>

                        <div class="form-group">
                            <label>Incident Description</label>
                            <textarea name="description" id="description"
                                      class="input-field textarea"
                                      placeholder="Describe what happened..."></textarea>
                        </div>

                    </div>

                    <div class="form-right">

                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="tel" name="contact_number" id="contact_number"
                                   class="input-field" placeholder="Enter contact number">
                        </div>

                        <div class="form-group">
                            <label>Incident Type</label>
                            <select name="incident_type" id="incident_type"
                                    class="input-field select-field">
                                <option value="" disabled <?php echo empty($prefill_type) ? 'selected' : ''; ?>></option>
                                <option value="Fire"    <?php echo $prefill_type === 'Fire'    ? 'selected' : ''; ?>>Fire</option>
                                <option value="Flood"   <?php echo $prefill_type === 'Flood'   ? 'selected' : ''; ?>>Flood</option>
                                <option value="Crime"   <?php echo $prefill_type === 'Crime'   ? 'selected' : ''; ?>>Crime</option>
                                <option value="Medical" <?php echo $prefill_type === 'Medical' ? 'selected' : ''; ?>>Medical</option>
                            </select>
                        </div>

                                                <div class="map-preview-card">
                            <p class="map-preview-label">
                                <i class="bi bi-geo-alt-fill"></i>
                                Pin the incident location on the map
                            </p>
                            <div id="reportMap" class="report-map"></div>
                            <p class="map-preview-hint">
                                Click anywhere on the map or drag the pin to set the exact location.
                            </p>
                        </div>

                        <div class="form-group">
                            <label>Upload Photos/Videos</label>
                            <div class="upload-area" id="uploadArea">
                                <i class="bi bi-camera-fill"></i>
                                <span>Capture / Drag photos here</span>
                                <input type="file" name="photo" id="photoInput"
                                       accept="image/*,video/*" multiple>
                            </div>

                            <div class="photo-preview-grid" id="photoPreviewGrid"></div>
                        </div>

                    </div>

                </div>

                <div class="form-actions">
                    <button type="button" class="btn-submit" onclick="submitReport()">Submit Report</button>
                    <button type="button" class="btn-draft"  onclick="saveDraft()">Save as Draft</button>
                </div>

            </form>
        </div>

    </main>

    <div class="drafts-overlay" id="draftsOverlay" onclick="closeDrafts()"></div>
    <div class="drafts-panel" id="draftsPanel">
        <div class="drafts-header">
            <h3><i class="bi bi-archive-fill"></i> Saved Drafts</h3>
            <button onclick="closeDrafts()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="drafts-list">
            <?php if (empty($drafts)): ?>
                <p class="no-drafts">No saved drafts yet.</p>
            <?php else: ?>
                <?php foreach ($drafts as $draft): ?>
                    <div class="draft-item" onclick="loadDraft(<?php echo htmlspecialchars(json_encode($draft)); ?>)">
                        <div class="draft-info">
                            <p class="draft-title"><?php echo $draft['incident_title'] ?: 'Untitled Draft'; ?></p>
                            <p class="draft-meta">
                                <span class="draft-type"><?php echo $draft['incident_type']; ?></span>
                                &bull; <?php echo date('M j, g:i A', strtotime($draft['updated_at'])); ?>
                            </p>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

        <script>
        const userDrafts = <?php echo json_encode($drafts); ?>;
        // Default map center: Barangay San Pablo, Sto. Tomas, Batangas
        window.SURVAIVAL_MAP_CENTER = {
            lat: 14.1074,
            lng: 121.1416,
            label: <?php echo json_encode('Barangay San Pablo, Sto. Tomas, Batangas'); ?>
        };
    </script>

    <!-- Leaflet (OpenStreetMap) - free, no API key -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script src="script.js"></script>
</body>
</html>
