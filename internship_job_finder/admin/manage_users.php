<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/database.php';

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $uid = intval($_GET['delete_id']);
    if($uid != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $uid");
    }
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';
    header("Location: manage_users.php?tab=" . urlencode($tab));
    exit();
}

// Handle Approve Deletion Request
if (isset($_GET['approve_delete_id'])) {
    $uid = intval($_GET['approve_delete_id']);
    if($uid != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $uid");
    }
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';
    header("Location: manage_users.php?tab=" . urlencode($tab));
    exit();
}

// Handle Reject Deletion Request
if (isset($_GET['reject_delete_id'])) {
    $uid = intval($_GET['reject_delete_id']);
    if($uid != $_SESSION['user_id']) {
        $conn->query("UPDATE users SET delete_request = 'none' WHERE id = $uid");
    }
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';
    header("Location: manage_users.php?tab=" . urlencode($tab));
    exit();
}

$tab = isset($_GET['tab']) && in_array($_GET['tab'], ['all', 'company', 'student']) ? $_GET['tab'] : 'all';

$sql = "SELECT * FROM users";
if ($tab === 'company') {
    $sql .= " WHERE role = 'company'";
} elseif ($tab === 'student') {
    $sql .= " WHERE role = 'student'";
}
$sql .= " ORDER BY CASE WHEN role = 'admin' THEN 0 ELSE 1 END ASC, id ASC";

$users = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f4f7f6; }
        .sidebar { width: 250px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding-top: 20px; }
        .sidebar a { display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; }
        .sidebar a:hover { background: #34495e; }
        .main-content { margin-left: 250px; padding: 40px; width: 100%; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #34495e; color: white; }
        .role { padding: 4px 8px; border-radius: 4px; font-size: 0.8em; display: inline-block; }
        .role-admin { background: #e74c3c; color: white; }
        .role-company { background: #3498db; color: white; }
        .role-student { background: #2ecc71; color: white; }
        .delete-btn { color: #e74c3c; text-decoration: none; font-weight: bold; }
        
        /* Tabs Styling */
        .tabs-container { display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 2px solid #ddd; padding-bottom: 0; }
        .tab-link { padding: 12px 24px; text-decoration: none; color: #7f8c8d; font-weight: bold; border-radius: 6px 6px 0 0; transition: all 0.2s ease; border: 1px solid transparent; border-bottom: none; margin-bottom: -2px; }
        .tab-link.active { color: #3498db; border-color: #ddd; background: white; border-bottom: 2px solid white; z-index: 1; }
        .tab-link:hover:not(.active) { color: #2c3e50; background: #eaeded; }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_users.php" style="background:#34495e">Manage Users</a>
        <a href="manage_opportunities.php">Review Opportunities</a>
        <a href="manage_applications.php">Manage Applications</a>
        <a href="expired_posts.php">Expired Posts</a>
        <a href="../settings.php">Settings</a>
        <a href="../auth/logout.php" style="color:#e74c3c">Logout</a>
    </div>

    <!-- Dark Mode Support -->
    <script>
        (function() {
            const style = document.createElement('style');
            style.innerHTML = `
                body.dark-mode { background: #1a1a1a !important; color: #f4f6f9 !important; }
                .dark-mode .sidebar { background: #111111 !important; }
                .dark-mode .sidebar a { color: #bbbbbb !important; }
                .dark-mode .sidebar a:hover { background: #2c3e50 !important; color: #ffffff !important; }
                .dark-mode .form-card, 
                .dark-mode .stat-card, 
                .dark-mode .card, 
                .dark-mode .opp-card, 
                .dark-mode table,
                .dark-mode .profile-card,
                .dark-mode .settings-card,
                .dark-mode .delete-box { background: #252525 !important; color: #f4f6f9 !important; border-color: #333333 !important; box-shadow: 0 4px 10px rgba(0,0,0,0.3) !important; }
                .dark-mode td { border-bottom: 1px solid #333333 !important; color: #f4f6f9 !important; }
                .dark-mode th { background: #1f1f1f !important; color: #ffffff !important; }
                .dark-mode h1, .dark-mode h2, .dark-mode h3, .dark-mode h4, .dark-mode label, .dark-mode .p-info strong { color: #ffffff !important; }
                .dark-mode input, .dark-mode select, .dark-mode textarea { background: #2d2d2d !important; color: #ffffff !important; border: 1px solid #444444 !important; }
                .dark-mode .desc, .dark-mode .meta, .dark-mode p { color: #aaaaaa !important; }
                .dark-mode .nav { background: #252525 !important; border-bottom: 1px solid #333333 !important; }
                .dark-mode .nav .logo { color: #ffffff !important; }
                .dark-mode .badge { background: #333333 !important; color: #f4f6f9 !important; }
                .dark-mode .status-pending { background: #3a2e1b !important; color: #f39c12 !important; }
                .dark-mode .status-accepted { background: #14352b !important; color: #1abc9c !important; border-color: #1a5c43 !important; }
                .dark-mode .status-rejected { background: #3c1e1e !important; color: #e74c3c !important; border-color: #5c1a1a !important; }
                .dark-mode .save-btn { border-color: #3498db !important; color: #3498db !important; }
                .dark-mode .save-btn:hover { background: #1e3a5f !important; }
                .dark-mode .tabs-container { border-bottom-color: #333333 !important; }
                .dark-mode .tab-link { color: #aaaaaa !important; }
                .dark-mode .tab-link.active { color: #3498db !important; background: #252525 !important; border-color: #333333 !important; border-bottom-color: #252525 !important; }
                .dark-mode .tab-link:hover:not(.active) { color: #ffffff !important; background: #2d2d2d !important; }
             `;
            document.head.appendChild(style);
            if (localStorage.getItem('theme_' + '<?php echo $_SESSION['user_id']; ?>') === 'dark') {
                document.body.classList.add('dark-mode');
            }
        })();
    </script>

    <div class="main-content">
        <h1>Manage Users</h1>
        
        <div class="tabs-container">
            <a href="manage_users.php?tab=all" class="tab-link <?php echo $tab === 'all' ? 'active' : ''; ?>">All</a>
            <a href="manage_users.php?tab=company" class="tab-link <?php echo $tab === 'company' ? 'active' : ''; ?>">Company</a>
            <a href="manage_users.php?tab=student" class="tab-link <?php echo $tab === 'student' ? 'active' : ''; ?>">Student</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['full_name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><span class="role role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                    <td>
                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                            <?php if(isset($user['delete_request']) && $user['delete_request'] == 'pending'): ?>
                                <div style="margin-bottom: 8px;">
                                    <span class="role" style="background:#e74c3c; color:white; font-weight:bold;">Deletion Requested</span>
                                </div>
                                <div style="display: flex; gap: 5px;">
                                    <a href="manage_users.php?approve_delete_id=<?php echo $user['id']; ?>&tab=<?php echo $tab; ?>" class="role" style="text-decoration:none; background:#2ecc71; color:white; font-weight:bold;" onclick="return confirm('Permanently delete this user account?')">Approve</a>
                                    <a href="manage_users.php?reject_delete_id=<?php echo $user['id']; ?>&tab=<?php echo $tab; ?>" class="role" style="text-decoration:none; background:#95a5a6; color:white; font-weight:bold;" onclick="return confirm('Reset and keep this user account?')">Reject</a>
                                </div>
                            <?php else: ?>
                                <a href="manage_users.php?delete_id=<?php echo $user['id']; ?>&tab=<?php echo $tab; ?>" class="delete-btn" onclick="return confirm('Delete this user?')">Delete</a>
                            <?php endif; ?>
                        <?php else: ?>
                            (Me)
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
