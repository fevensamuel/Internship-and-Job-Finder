<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$view = isset($_GET['view']) ? $_GET['view'] : 'main';

// Fetch latest user details
$user_stmt = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user_data = $user_stmt->fetch_assoc();

$success_msg = "";
$error_msg = "";

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_p = mysqli_real_escape_string($conn, $_POST['old_password']);
    $new_p = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_p = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    if ($old_p !== $user_data['password']) {
        $error_msg = "Current password is incorrect!";
    } elseif ($new_p !== $confirm_p) {
        $error_msg = "New passwords do not match!";
    } elseif (empty($new_p)) {
        $error_msg = "New password cannot be empty!";
    } else {
        $update_p = $conn->query("UPDATE users SET password = '$new_p' WHERE id = $user_id");
        if ($update_p) {
            $success_msg = "Password updated successfully!";
            $user_data['password'] = $new_p; // Update in memory
        } else {
            $error_msg = "Failed to update password. Please try again.";
        }
    }
}

// Handle Request Account Deletion (Student & Company only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_delete']) && $role !== 'admin') {
    $update_del = $conn->query("UPDATE users SET delete_request = 'pending' WHERE id = $user_id");
    if ($update_del) {
        $success_msg = "Deletion request submitted. Your account will be removed once approved by an administrator.";
        $user_data['delete_request'] = 'pending'; // Update in memory
    } else {
        $error_msg = "Failed to submit request.";
    }
}

// Handle Undo Account Deletion (Student & Company only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['undo_delete']) && $role !== 'admin') {
    $update_del = $conn->query("UPDATE users SET delete_request = 'none' WHERE id = $user_id");
    if ($update_del) {
        $success_msg = "Deletion request cancelled! Your account is active.";
        $user_data['delete_request'] = 'none'; // Update in memory
    } else {
        $error_msg = "Failed to cancel request.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Settings</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f4f7f6; }
        .sidebar { width: 250px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding-top: 20px; }
        .sidebar h2 { text-align: center; margin-bottom: 30px; font-size: 1.25em; }
        .sidebar a { display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; transition: 0.3s; }
        .sidebar a:hover { background: #34495e; padding-left: 35px; }
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); box-sizing: border-box; }
        
        .settings-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; margin-bottom: 30px; }
        .settings-card h2 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-bottom: 20px; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #34495e; }
        input[type="password"], input[type="text"], select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        
        .btn { padding: 12px 24px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; display: inline-block; font-size: 14px; text-decoration: none; }
        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-danger:hover { background: #c0392b; }
        .btn-secondary { background: #95a5a6; color: white; }
        .btn-secondary:hover { background: #7f8c8d; }
        
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #e8f8f5; color: #1abc9c; border: 1px solid #a3e4d7; }
        .alert-error { background: #fdedec; color: #e74c3c; border: 1px solid #f9d5d5; }
        
        /*  Dark mode toggle switch */
        .toggle-container { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .switch { position: relative; display: inline-block; width: 60px; height: 34px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 26px; width: 26px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #3498db; }
        input:checked + .slider:before { transform: translateX(26px); }
        
        .p-info { font-size: 1.1em; margin-bottom: 12px; }
        .p-info strong { color: #34495e; }
        
        .delete-box { border: 1px dashed #e74c3c; padding: 20px; border-radius: 8px; background: #fffbfb; }
        .delete-box-title { color: #e74c3c; font-weight: bold; margin-bottom: 10px; font-size: 1.1em; }
        .delete-confirm-box { display: none; margin-top: 15px; padding: 15px; border-radius: 6px; background: #fdedec; border: 1px solid #f9d5d5; }
        
        /* Password wrapper and toggle styles */
        .password-wrapper { position: relative; }
        .password-wrapper input { padding-right: 40px !important; }
        .toggle-password {
            position: absolute !important;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none !important;
            border: none !important;
            color: #7f8c8d !important;
            cursor: pointer !important;
            font-size: 1.1em !important;
            padding: 0 !important;
            margin: 0 !important;
            width: auto !important;
            height: auto !important;
            box-shadow: none !important;
            display: inline-block !important;
            font-family: inherit;
        }
        .toggle-password:hover {
            color: #333 !important;
            background: none !important;
        }
    </style>
</head>
<body>
    <!-- Sidebar dynamically loading relative routes -->
    <div class="sidebar">
        <?php if ($role === 'student'): ?>
            <h2 style="text-align:center">Student Panel</h2>
            <a href="student/dashboard.php">Dashboard</a>
            <a href="student/opportunities.php">Find Opportunities</a>
            <a href="student/my_applications.php">My Applications</a>
            <a href="student/saved_opportunities.php">Saved Opportunities</a>
            <a href="settings.php" style="background:#34495e">Settings</a>
            <a href="auth/logout.php" style="color:#e74c3c">Logout</a>
        <?php elseif ($role === 'company'): ?>
            <h2 style="text-align:center">Company Panel</h2>
            <a href="company/dashboard.php">Dashboard</a>
            <a href="company/add_opportunity.php">Post Opportunity</a>
            <a href="company/my_opportunities.php">My Opportunities</a>
            <a href="company/applicants.php">View Applicants</a>
            <a href="settings.php" style="background:#34495e">Settings</a>
            <a href="auth/logout.php" style="color:#e74c3c">Logout</a>
        <?php else: ?>
            <h2 style="text-align:center">Admin Panel</h2>
            <a href="admin/dashboard.php">Dashboard</a>
            <a href="admin/manage_users.php">Manage Users</a>
            <a href="admin/manage_opportunities.php">Review Opportunities</a>
            <a href="admin/manage_applications.php">Manage Applications</a>
            <a href="settings.php" style="background:#34495e">Settings</a>
            <a href="auth/logout.php" style="color:#e74c3c">Logout</a>
        <?php endif; ?>
    </div>

    <div class="main-content">
        <h1>Settings & Preferences</h1>
        <p>Manage your login details, display theme, and account status.</p>

        <?php if($success_msg): ?> <div class="alert alert-success"><?php echo $success_msg; ?></div> <?php endif; ?>
        <?php if($error_msg): ?> <div class="alert alert-error"><?php echo $error_msg; ?></div> <?php endif; ?>

        <!-- Theme Preference Section (All users) -->
        <?php if ($view === 'main'): ?>
            <div class="settings-card">
                <h2>Display Theme</h2>
                <div class="toggle-container">
                    <div>
                        <h3 style="margin:0; font-size:1.1em;">Dark Mode</h3>
                        <p style="margin:5px 0 0; font-size:0.9em; color:#7f8c8d;">Turn interface dark for low-light conditions.</p>
                    </div>
                    <div>
                        <label class="switch">
                            <input type="checkbox" id="themeToggleBtn" onchange="toggleThemePreference()">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <?php if ($role === 'admin'): ?>
                <!-- Admin Profile View Only -->
                <div class="settings-card">
                    <h2>Admin Profile</h2>
                    <div class="p-info"><strong>Role:</strong> System Administrator</div>
                    <div class="p-info"><strong>Full Name:</strong> <?php echo htmlspecialchars($user_data['full_name']); ?></div>
                    <div class="p-info"><strong>Email Address:</strong> <?php echo htmlspecialchars($user_data['email']); ?></div>
                    <div class="p-info"><strong>Account Status:</strong> Active <span style="color:#2ecc71; font-weight:bold;">●</span></div>
                </div>
            <?php else: ?>
                <!-- Non-Admins: Redirect Buttons -->
                <div class="settings-card">
                    <h2>Account Settings</h2>
                    <p style="color:#7f8c8d; margin-bottom: 25px; font-size: 0.95em;">Manage your security preferences and profile status.</p>
                    <div style="display: flex; gap: 15px;">
                        <a href="settings.php?view=change_password" class="btn btn-primary" style="text-align: center; text-decoration: none;">Change Password</a>
                        <a href="settings.php?view=delete_account" class="btn btn-danger" style="text-align: center; text-decoration: none;">Delete Account</a>
                    </div>
                </div>
            <?php endif; ?>

        <?php elseif ($view === 'change_password' && $role !== 'admin'): ?>
            <div style="margin-bottom: 25px;">
                <a href="settings.php" class="btn btn-secondary" style="text-decoration: none;">← Back to Settings</a>
            </div>

            <div class="settings-card">
                <h2>Security Settings - Change Password</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="old_password" name="old_password" placeholder="Enter old password" required>
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('old_password')">👁️</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="new_password" name="new_password" placeholder="Create new password" required>
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('new_password')">👁️</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirm_password')">👁️</button>
                        </div>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>
                </form>
            </div>

        <?php elseif ($view === 'delete_account' && $role !== 'admin'): ?>
            <div style="margin-bottom: 25px;">
                <a href="settings.php" class="btn btn-secondary" style="text-decoration: none;">← Back to Settings</a>
            </div>

            <!-- Danger Zone: Account Deletion -->
            <div class="settings-card">
                <h2>Danger Zone</h2>
                <div class="delete-box">
                    <div class="delete-box-title">Account Deletion</div>
                    <?php if ($user_data['delete_request'] === 'pending'): ?>
                        <div class="alert alert-error" style="margin-bottom: 15px;">
                            <strong>Requested Action Pending:</strong> Your account is scheduled for deletion awaiting administrator review.
                        </div>
                        <form method="POST">
                            <button type="submit" name="undo_delete" class="btn btn-primary">Undo Deletion Request</button>
                        </form>
                    <?php else: ?>
                        <p style="margin:0 0 15px; font-size:0.95em; color:#666;">This request will mark your account for deletion. All your applications and job postings will be permanently deleted once approved by an administrator.</p>
                        
                        <button type="button" class="btn btn-danger" id="initDeleteBtn" onclick="showDeleteConfirmation()">Delete Account</button>
                        
                        <div class="delete-confirm-box" id="deleteConfirmBox">
                            <p style="margin-top:0; font-weight:bold; color:#721c24;">Are you absolutely sure you want to delete your account?</p>
                            <form method="POST" style="display:inline-block;">
                                <button type="submit" name="request_delete" class="btn btn-danger">Yes, Delete My Account</button>
                            </form>
                            <button type="button" class="btn btn-secondary" onclick="hideDeleteConfirmation()" style="margin-left:10px;">No, Cancel</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <script>window.location.href = "settings.php";</script>
        <?php endif; ?>
    </div>

    <!-- Inject Dark Mode controller system into the webpage dynamic scope -->
    <script id="dark-mode-detector">
        (function() {
            const style = document.createElement('style');
            style.id = "dark-mode-styles";
            style.innerHTML = `
                body.dark-mode { background: #1a1a1a !important; color: #f4f6f9 !important; }
                .dark-mode .sidebar { background: #111111 !important; }
                .dark-mode .sidebar a { color: #bbbbbb !important; }
                .dark-mode .sidebar a:hover { background: #2c3e50 !important; color: #ffffff !important; }
                .dark-mode .settings-card, 
                .dark-mode .stat-card, 
                .dark-mode .card, 
                .dark-mode .opp-card, 
                .dark-mode table,
                .dark-mode .profile-card,
                .dark-mode .delete-box { background: #252525 !important; color: #f4f6f9 !important; border-color: #333333 !important; box-shadow: 0 4px 10px rgba(0,0,0,0.3) !important; }
                .dark-mode td { border-bottom: 1px solid #333333 !important; color: #f4f6f9 !important; }
                .dark-mode th { background: #1f1f1f !important; color: #ffffff !important; }
                .dark-mode h1, .dark-mode h2, .dark-mode h3, .dark-mode h4, .dark-mode label, .dark-mode .p-info strong { color: #ffffff !important; }
                .dark-mode input, .dark-mode select, .dark-mode textarea { background: #2d2d2d !important; color: #ffffff !important; border: 1px solid #444444 !important; }
                .dark-mode .desc, .dark-mode .meta, .dark-mode p { color: #aaaaaa !important; }
                .dark-mode .nav { background: #252525 !important; border-bottom: 1px solid #333333 !important; }
                .dark-mode .nav .logo { color: #ffffff !important; }
                .dark-mode .badge { background: #333333 !important; color: #f4f6f9 !important; }
                .dark-mode .alert-success { background: #14352b !important; color: #1abc9c !important; border-color: #1a5c43 !important; }
                .dark-mode .alert-error { background: #3c1e1e !important; color: #e74c3c !important; border-color: #5c1a1a !important; }
                .dark-mode .toggle-password { color: #aaaaaa !important; }
                .dark-mode .toggle-password:hover { color: #ffffff !important; }
            `;
            document.head.appendChild(style);
            
            if (localStorage.getItem('theme_' + '<?php echo $_SESSION['user_id']; ?>') === 'dark') {
                document.body.classList.add('dark-mode');
                const checkbox = document.getElementById('themeToggleBtn');
                if (checkbox) checkbox.checked = true;
            }
        })();

        function toggleThemePreference() {
            const hasDark = document.body.classList.toggle('dark-mode');
            const key = 'theme_' + '<?php echo $_SESSION['user_id']; ?>';
            if (hasDark) {
                localStorage.setItem(key, 'dark');
            } else {
                localStorage.setItem(key, 'light');
            }
        }

        function showDeleteConfirmation() {
            const box = document.getElementById('deleteConfirmBox');
            const btn = document.getElementById('initDeleteBtn');
            if (box) box.style.display = 'block';
            if (btn) btn.style.display = 'none';
        }

        function hideDeleteConfirmation() {
            const box = document.getElementById('deleteConfirmBox');
            const btn = document.getElementById('initDeleteBtn');
            if (box) box.style.display = 'none';
            if (btn) btn.style.display = 'inline-block';
        }

        function togglePasswordVisibility(id) {
            const el = document.getElementById(id);
            const btn = el.nextElementSibling;
            if (el.type === 'password') {
                el.type = 'text';
                btn.textContent = '🙈';
            } else {
                el.type = 'password';
                btn.textContent = '👁️';
            }
        }
    </script>
</body>
</html>
