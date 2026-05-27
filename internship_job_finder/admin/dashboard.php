<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/database.php';

$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$pending_jobs = $conn->query("SELECT COUNT(*) as count FROM opportunities WHERE status = 'pending'")->fetch_assoc()['count'];
$total_apps = $conn->query("SELECT COUNT(*) as count FROM applications")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f4f7f6; }
        .sidebar { width: 250px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding-top: 20px; }
        .sidebar a { display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; }
        .sidebar a:hover { background: #34495e; }
        .main-content { margin-left: 250px; padding: 40px; width: 100%; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .stat-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .stat-card h3 { margin: 0; color: #7f8c8d; font-size: 0.9em; }
        .stat-card p { font-size: 2em; margin-top: 10px; color: #2c3e50; font-weight: bold; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2 style="text-align:center">Admin Panel</h2>
        <a href="../index.php">Home Site</a>
        <a href="dashboard.php" style="background:#34495e">Dashboard</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_opportunities.php">Review Opportunities (<?php echo $pending_jobs; ?>)</a>
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
            `;
            document.head.appendChild(style);
            if (localStorage.getItem('theme_' + '<?php echo $_SESSION['user_id']; ?>') === 'dark') {
                document.body.classList.add('dark-mode');
            }
        })();
    </script>
    <div class="main-content">
        <h1>Overview</h1>
        <div class="stats">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php echo $total_users; ?></p>
            </div>
            <div class="stat-card">
                <h3>Pending Jobs</h3>
                <p><?php echo $pending_jobs; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Applications</h3>
                <p><?php echo $total_apps; ?></p>
            </div>
        </div>
    </div>
</body>
</html>
