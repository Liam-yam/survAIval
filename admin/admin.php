<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "dbsurvaival";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] == 'save_announcement') {
        $announcement_id = $_POST['announcement_id'] ?? '';
        $category = $_POST['category'] ?? 'Community';
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        if (empty($announcement_id)) {
            $stmt = $conn->prepare("INSERT INTO tblannouncement (category, title, content) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $category, $title, $content);
        } else {
            $stmt = $conn->prepare("UPDATE tblannouncement SET category=?, title=?, content=? WHERE announcement_id=?");
            $stmt->bind_param("sssi", $category, $title, $content, $announcement_id);
        }
        $stmt->execute();
        $message = "Announcement saved successfully!";
    }
    
    if ($_POST['action'] == 'delete_announcement') {
        $announcement_id = $_POST['announcement_id'] ?? 0;
        $stmt = $conn->prepare("DELETE FROM tblannouncement WHERE announcement_id=?");
        $stmt->bind_param("i", $announcement_id);
        $stmt->execute();
        $message = "Announcement removed successfully.";
    }
    
    if ($_POST['action'] == 'update_incident') {
        $report_id = $_POST['report_id'] ?? 0;
        $status = $_POST['status'] ?? 'pending';
        $stmt = $conn->prepare("UPDATE tblreports SET status=? WHERE report_id=?");
        $stmt->bind_param("si", $status, $report_id);
        $stmt->execute();
        $message = "Incident status updated successfully.";
    }

    if ($_POST['action'] == 'register_user') {
        $fullname     = $_POST['fullname'] ?? '';
        $mname        = $_POST['mname'] ?? '';
        $gender       = $_POST['gender'] ?? 'Male';
        $account_type = $_POST['account_type'] ?? 'Resident';
        $username     = $_POST['username'] ?? '';
        $password     = password_hash($_POST['password'] ?? '123456', PASSWORD_BCRYPT);
        $position     = ($account_type === 'Admin') ? ($_POST['position'] ?? 'Staff') : 'Resident';

        $stmt = $conn->prepare("INSERT INTO tblusers (fullname, mname, gender, position, username, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $fullname, $mname, $gender, $position, $username, $password);
        if($stmt->execute()) {
            $message = "User registered successfully!";
        } else {
            $message = "Error: Username configuration conflict.";
        }
    }
}

$announcements = $conn->query("SELECT * FROM tblannouncements ORDER BY announcement_id DESC");
$incidents = $conn->query("SELECT * FROM tblreports ORDER BY report_id DESC");

function uppercase_tag($tag) {
    if (strtolower($tag) == 'disaster') return 'Disaster Risk';
    if (strtolower($tag) == 'community') return 'Community Update';
    if (strtolower($tag) == 'weather') return 'Weather News';
    return $tag;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>survAlval | Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <aside>
        <div>
            <div class="logo-area"><i class="fa-solid fa-location-dot"></i> surv<span>Al</span>val</div>
            <div class="profile-card">
                <h4><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin Officer') ?></h4>
                <p><i class="fa-solid fa-user-shield"></i> System Administrator</p>
                <p>Brgy. San Pablo, STC</p>
            </div>
            
            <nav class="nav-menu">
                <div class="nav-section-title">Core Operations</div>
                <div class="nav-item active" onclick="switchTab('announcements-tab', this)">
                    <i class="fa-solid fa-bullhorn"></i> Announcements
                </div>
                <div class="nav-item" onclick="switchTab('incidents-tab', this)">
                    <i class="fa-solid fa-triangle-exclamation"></i> Reported Incidents
                </div>
                
                <div class="nav-section-title">Management</div>
                <div class="nav-item" onclick="switchTab('registration-tab', this)">
                    <i class="fa-solid fa-user-plus"></i> Staff Registration
                </div>
            </nav>
        </div>
        
        <a href="?logout=true" class="nav-item logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
    </aside>

    <main>
        <?php if(!empty($message)): ?>
            <div class="status-alert">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <section id="announcements-tab" class="dashboard-tab active-tab">
            <div class="header-bar">
                <h2>Manage Board Announcements</h2>
                <button class="btn-action" onclick="openAnnouncementModal()"><i class="fa-solid fa-plus"></i> Compose Notice</button>
            </div>
            
            <div class="card-grid">
                <?php if($announcements && $announcements->num_rows > 0): ?>
                    <?php while($row = $announcements->fetch_assoc()): ?>
                        <?php 
                            $aid      = $row['announcement_id'] ?? 0;
                            $cat      = $row['category'] ?? 'Community';
                            $title    = $row['title'] ?? 'Untitled Notification';
                            $content  = $row['content'] ?? '';
                            $raw_date = $row['created_at'] ?? date('Y-m-d H:i:s');
                            
                            $safe_json_row = json_encode([
                                'id' => $aid,
                                'category' => $cat,
                                'title' => $title,
                                'content' => $content
                            ]);
                        ?>
                        <div class="announcement-card">
                            <div class="card-body-content">
                                <span class="card-tag tag-<?= strtolower(htmlspecialchars($cat)) ?>">
                                    <?= uppercase_tag(htmlspecialchars($cat)) ?>
                                </span>
                                <h3 class="card-title"><?= htmlspecialchars($title) ?></h3>
                                <div class="card-date"><i class="fa-regular fa-clock"></i> <?= date('F j, Y | g:i A', strtotime($raw_date)) ?></div>
                                <p class="card-body-text"><?= nl2br(htmlspecialchars($content)) ?></p>
                            </div>
                            <div class="card-controls">
                                <button class="btn-ctrl btn-edit" onclick='editAnnouncement(<?= htmlspecialchars($safe_json_row, ENT_QUOTES, 'UTF-8') ?>)'><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                <form method="POST" style="flex:1;" onsubmit="return confirm('Delete item permanently?');">
                                    <input type="hidden" name="action" value="delete_announcement">
                                    <input type="hidden" name="announcement_id" value="<?= $aid ?>">
                                    <button type="submit" class="btn-ctrl btn-delete"><i class="fa-solid fa-trash"></i> Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty-state-message">No announcements discovered on the board database.</p>
                <?php endif; ?>
            </div>
        </section>

        <section id="incidents-tab" class="dashboard-tab">
            <div class="header-bar">
                <h2>Incoming Critical & Citizen Incident Logs</h2>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Reporter</th>
                        <th>Incident Title / Type</th>
                        <th>Description Details</th>
                        <th>Date Processed</th>
                        <th>Status Control</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($incidents && $incidents->num_rows > 0): ?>
                        <?php while($row = $incidents->fetch_assoc()): ?>
                            <?php 
                                $inc_id      = $row['report_id'] ?? 0;
                                $inc_rep     = $row['reporter_name'] ?? 'Anonymous';
                                $inc_title   = $row['incident_title'] ?? 'No Title';
                                $inc_type    = $row['incident_type'] ?? 'General';
                                $inc_details = $row['description'] ?? 'No context details supplied.';
                                $inc_date    = $row['created_at'] ?? date('Y-m-d H:i:s');
                                $inc_status  = strtolower($row['status'] ?? 'pending');
                            ?>
                            <tr>
                                <td>#<?= $inc_id ?></td>
                                <td><strong><?= htmlspecialchars($inc_rep) ?></strong></td>
                                <td>
                                    <div style="font-weight:600; color:#1e3824;"><?= htmlspecialchars($inc_title) ?></div>
                                    <span class="type-indicator"><?= htmlspecialchars($inc_type) ?></span>
                                </td>
                                <td><p class="table-details-text"><?= htmlspecialchars($inc_details) ?></p></td>
                                <td><?= date('M j, Y g:i A', strtotime($inc_date)) ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_incident">
                                        <input type="hidden" name="report_id" value="<?= $inc_id ?>">
                                        <select name="status" onchange="this.form.submit()" class="status-selector status-<?= $inc_status ?>">
                                            <option value="draft" <?= $inc_status == 'draft' ? 'selected' : '' ?>>Draft</option>
                                            <option value="pending" <?= $inc_status == 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="responding" <?= $inc_status == 'responding' ? 'selected' : '' ?>>Responding</option>
                                            <option value="resolved" <?= $inc_status == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; color:#666; padding: 40px;">No incident entries found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section id="registration-tab" class="dashboard-tab">
            <div class="header-bar">
                <h2>Register Personnel & Accounts</h2>
            </div>
            <div class="admin-form-box">
                <form method="POST">
                    <input type="hidden" name="action" value="register_user">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" required placeholder="First Name Last Name">
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="mname" required placeholder="Middle Name">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Account Type</label>
                        <select name="account_type" id="account_type" onchange="togglePositionField()" required>
                            <option value="Resident">Standard Resident</option>
                            <option value="Admin">System Admin / Staff</option>
                        </select>
                    </div>
                    <div class="form-group" id="position_container" style="display: none;">
                        <label>Assigned Official Position / Governance Role</label>
                        <select name="position" id="position_select">
                            <option value="Barangay Chairman">Barangay Chairman</option>
                            <option value="Sangguniang Barangay Member">Sangguniang Barangay Member</option>
                            <option value="SK Chairman">SK Chairman</option>
                            <option value="BHERT Responder">BHERT Responder</option>
                            <option value="Disaster Action Officer">Disaster Action Officer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>System Account Username</label>
                        <input type="text" name="username" required placeholder="Unique username">
                    </div>
                    <div class="form-group">
                        <label>Secure Core Password</label>
                        <input type="password" name="password" required placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn-action" style="width: 100%; border-radius: 8px; margin-top: 10px;">Register Account Profile</button>
                </form>
            </div>
        </section>
    </main>

    <div id="announcementModal" class="modal">
        <div class="admin-form-box" style="width: 100%; max-width: 500px; border-radius: 16px;">
            <h3 id="modalTitle" style="margin-bottom: 20px; color: #2c4a34;">Compose Board Announcement</h3>
            <form method="POST">
                <input type="hidden" name="action" value="save_announcement">
                <input type="hidden" name="announcement_id" id="form_announcement_id">
                
                <div class="form-group">
                    <label>Categorized Tag</label>
                    <select name="category" id="form_category" required>
                        <option value="Disaster">Disaster Risk</option>
                        <option value="Community">Community Update</option>
                        <option value="Weather">Weather News</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Header Title</label>
                    <input type="text" name="title" id="form_title" required placeholder="Heading text">
                </div>
                <div class="form-group">
                    <label>Context Content Body</label>
                    <textarea name="content" id="form_content" rows="6" required placeholder="Write details here..."></textarea>
                </div>
                
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="button" class="cancel-modal-btn" onclick="closeAnnouncementModal()">Cancel</button>
                    <button type="submit" class="btn-action" style="flex:1; border-radius:6px;">Commit Content</button>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>