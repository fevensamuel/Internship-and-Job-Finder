<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/database.php';
require_once '../models/Opportunity.php';

$oppModel = new Opportunity($conn);
$opportunities = $oppModel->getByCompany($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Opportunities</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f4f7f6; }
        .sidebar { width: 250px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding-top: 20px; }
        .sidebar a { display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; }
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #34495e; color: white; }
        .status { padding: 5px 10px; border-radius: 20px; font-size: 0.85em; font-weight: bold; }
        .status-pending { background: #fef9e7; color: #f39c12; }
        .status-approved { background: #e8f8f5; color: #1abc9c; }
        .status-rejected { background: #fdedec; color: #e74c3c; }
        .actions a { text-decoration: none; margin-right: 15px; font-size: 0.9em; }
        .edit-btn { color: #3498db; }
        .delete-btn { color: #e74c3c; }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="add_opportunity.php">Post Opportunity</a>
        <a href="my_opportunities.php" style="background:#34495e">My Opportunities</a>
        <a href="applicants.php">View Applicants</a>
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
        <h1>My Opportunities</h1>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Date Posted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $opportunities->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $row['type']; ?></td>
                    <td><?php echo $row['location']; ?></td>
                    <td><span class="status status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                    <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                    <td class="actions">
                        <a href="edit_opportunity.php?id=<?php echo $row['id']; ?>" class="edit-btn">Edit</a>
                        <a href="delete_opportunity.php?id=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($opportunities->num_rows == 0): ?>
                <tr><td colspan="6" style="text-align:center">No opportunities posted yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
