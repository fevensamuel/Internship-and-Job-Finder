<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/database.php';

$sql = "SELECT a.*, o.title as job_title, s.full_name as student_name, c.full_name as company_name 
        FROM applications a 
        JOIN opportunities o ON a.opportunity_id = o.id 
        JOIN users s ON a.student_id = s.id 
        JOIN users c ON o.company_id = c.id 
        ORDER BY a.applied_at DESC";
$apps = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Applications</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f4f7f6; }
        .sidebar { width: 250px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding-top: 20px; }
        .sidebar a { display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; }
        .main-content { margin-left: 250px; padding: 40px; width: 100%; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #34495e; color: white; }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_opportunities.php">Review Opportunities</a>
        <a href="manage_applications.php" style="background:#34495e">Manage Applications</a>
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
                .dark-mode .status-accepted { background: #14352b !important; color: #1abc9c !important; }
                .dark-mode .status-rejected { background: #3c1e1e !important; color: #e74c3c !important; }
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
        <h1>All System Applications</h1>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Company</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Applied At</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $apps->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['student_name']; ?></td>
                    <td><?php echo $row['company_name']; ?></td>
                    <td><?php echo $row['job_title']; ?></td>
                    <td><?php echo ucfirst($row['status']); ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($row['applied_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
