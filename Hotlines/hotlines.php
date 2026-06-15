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

$hotline_groups = [

    'Landlines' => [
        [
            'icon'       => 'bi-telephone-fill',
            'name'       => 'Barangay Emergency Hotline',
            'subtitle'   => 'Brgy. Sta. Cruz',
            'number'     => '(049) 123-4567',
            'tel'        => '+630491234567',
            'status'     => '24/7',
        ],
        [
            'icon'       => 'bi-truck-front-fill',
            'name'       => 'Bureau of Fire Protection',
            'subtitle'   => 'Laguna BFP Station',
            'number'     => '160',
            'tel'        => '160',
            'status'     => '24/7',
        ],
        [
            'icon'       => 'bi-shield-fill',
            'name'       => 'Philippine National Police',
            'subtitle'   => 'Sta. Cruz Municipal Station',
            'number'     => '166',
            'tel'        => '166',
            'status'     => '24/7',
        ],
        [
            'icon'       => 'bi-hospital-fill',
            'name'       => 'Emergency Medical Services',
            'subtitle'   => 'Laguna Provincial Hospital',
            'number'     => '911',
            'tel'        => '911',
            'status'     => '24/7',
        ],
        [
            'icon'       => 'bi-water',
            'name'       => 'NDRRMC Operations Center',
            'subtitle'   => 'National hotline',
            'number'     => '(02) 911-5061',
            'tel'        => '+6329115061',
            'status'     => '24/7',
        ],
    ],

    'Mobile Numbers' => [
        [
            'icon'       => 'bi-person-badge-fill',
            'name'       => 'Barangay Captain',
            'subtitle'   => 'Brgy. Sta. Cruz Official',
            'number'     => '0917-123-4567',
            'tel'        => '+639171234567',
            'status'     => 'office',
        ],
        [
            'icon'       => 'bi-person-badge-fill',
            'name'       => 'DRRM Coordinator',
            'subtitle'   => 'Disaster Risk Response',
            'number'     => '0918-765-4321',
            'tel'        => '+639187654321',
            'status'     => '24/7',
        ],
        [
            'icon'       => 'bi-heart-pulse-fill',
            'name'       => 'Barangay Health Worker',
            'subtitle'   => 'Health Center — Sta. Cruz',
            'number'     => '0920-111-2222',
            'tel'        => '+639201112222',
            'status'     => 'office',
        ],
    ],

    'Online / Chat' => [
        [
            'icon'       => 'bi-facebook',
            'name'       => 'Barangay Sta. Cruz FB Page',
            'subtitle'   => 'Message via Facebook',
            'number'     => 'fb.com/brgystacruznews',
            'tel'        => 'https://facebook.com/brgystacruznews',
            'status'     => 'office',
            'is_link'    => true,
        ],
        [
            'icon'       => 'bi-envelope-fill',
            'name'       => 'Barangay Email',
            'subtitle'   => 'Official email address',
            'number'     => 'brgystacruznews@gmail.com',
            'tel'        => 'mailto:brgystacruznews@gmail.com',
            'status'     => 'office',
            'is_link'    => true,
        ],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>survAIval - Emergency Hotlines</title>
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
                <li><a href="../index.php"><i class="bi bi-house-fill"></i> Home</a></li>
                <li><a href="../Report_Incidents/report_incidents.php"><i class="bi bi-exclamation-triangle-fill"></i> Report Incidents</a></li>
                <li><a href="../My_Reports/my_reports.php"><i class="bi bi-clock-history"></i> My Reports</a></li>
                <li><a href="../Incident_Map/incident_map.php"><i class="bi bi-geo-alt-fill"></i> Incident Map</a></li>
            </ul>
            <p class="nav-label">INFORMATION</p>
            <ul>
                <li><a href="../Announcement/announcement.php"><i class="bi bi-megaphone-fill"></i> Announcement</a></li>
                <li class="active"><a href="hotlines.php"><i class="bi bi-telephone-fill"></i> Hotlines</a></li>
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

        <h1 class="page-title">Emergency Hotlines</h1>

        <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" placeholder="Search hotlines..." class="search-input">
        </div>

        <div class="hotlines-card">
            <?php foreach ($hotline_groups as $group_name => $hotlines): ?>

                <p class="section-label"><?php echo $group_name; ?></p>

                <div class="hotline-list">
                    <?php foreach ($hotlines as $hotline): ?>
                        <div class="hotline-row"
                             data-search="<?php echo strtolower($hotline['name'] . ' ' . $hotline['subtitle']); ?>">

                            <div class="hotline-icon">
                                <i class="bi <?php echo $hotline['icon']; ?>"></i>
                            </div>

                            <div class="hotline-info">
                                <p class="hotline-name"><?php echo $hotline['name']; ?></p>
                                <div class="hotline-meta">
                                    <span class="hotline-subtitle"><?php echo $hotline['subtitle']; ?></span>
                                    <?php if ($hotline['status'] === '24/7'): ?>
                                        <span class="status-dot status-active"></span>
                                        <span class="status-text active">24/7 active</span>
                                    <?php else: ?>
                                        <span class="status-dot status-office"></span>
                                        <span class="status-text office">Office hours only</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="hotline-right">
                                <p class="hotline-number"><?php echo $hotline['number']; ?></p>

                                <button class="copy-btn"
                                        onclick="copyNumber('<?php echo $hotline['number']; ?>', this)"
                                        title="Copy number">
                                    <i class="bi bi-clipboard"></i>
                                </button>

                                <?php if (!empty($hotline['is_link'])): ?>
                                    <a href="<?php echo $hotline['tel']; ?>"
                                       target="_blank"
                                       class="call-btn link-btn"
                                       title="Open">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                <?php else: ?>
                                    <button class="call-btn"
                                            onclick="confirmCall('<?php echo $hotline['name']; ?>', '<?php echo $hotline['number']; ?>', '<?php echo $hotline['tel']; ?>')"
                                            title="Call <?php echo $hotline['name']; ?>">
                                        <i class="bi bi-telephone-fill"></i>
                                    </button>
                                <?php endif; ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="group-divider"></div>

            <?php endforeach; ?>
        </div>

        <div class="no-results" id="noResults" style="display:none;">
            <i class="bi bi-inbox"></i>
            <p>No hotlines found.</p>
        </div>

    </main>

    <div class="modal-overlay" id="modalOverlay"></div>
    <div class="modal" id="callModal">
        <div class="modal-icon">
            <i class="bi bi-telephone-fill"></i>
        </div>
        <h3 class="modal-title" id="modalTitle"></h3>
        <p class="modal-number" id="modalNumber"></p>
        <p class="modal-msg">Are you sure you want to call this number?</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal()">Cancel</button>
            <a class="btn-call" id="callLink" href="#">Call Now</a>
        </div>
    </div>

    <div class="toast" id="copyToast">
        <i class="bi bi-check-circle-fill"></i> Number copied!
    </div>

    <script src="script.js"></script>
</body>
</html>
