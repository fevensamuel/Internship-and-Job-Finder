<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/database.php';

$company_id = $_SESSION['user_id'];

// Handle status updates
if (isset($_GET['action'])) {
    $aid = $_GET['aid'];
    $status = $_GET['action'] == 'accept' ? 'accepted' : 'rejected';
    $conn->query("UPDATE applications SET status = '$status' WHERE id = $aid");
    header("Location: applicants.php");
}

$sql = "SELECT a.*, o.title as job_title, u.full_name as student_name, u.email as student_email 
        FROM applications a 
        JOIN opportunities o ON a.opportunity_id = o.id 
        JOIN users u ON a.student_id = u.id 
        WHERE o.company_id = $company_id ORDER BY a.applied_at DESC";
$applicants = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Applicants</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f4f7f6; }
        .sidebar { width: 250px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding-top: 20px; }
        .sidebar a { display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; }
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #34495e; color: white; }
        .status { padding: 5px 10px; border-radius: 20px; font-size: 0.85em; font-weight: bold; }
        .status-pending { background: #fef9e7; color: #f39c12; }
        .status-accepted { background: #e8f8f5; color: #1abc9c; }
        .status-rejected { background: #fdedec; color: #e74c3c; }
        .action-links { display: flex; gap: 8px; }
        .action-btn { text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85em; display: inline-block; }
        .accept-btn { background: #2ecc71; color: white; }
        .reject-btn { background: #e74c3c; color: white; }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="add_opportunity.php">Post Opportunity</a>
        <a href="my_opportunities.php">My Opportunities</a>
        <a href="applicants.php" style="background:#34495e">View Applicants</a>
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
        <h1>Applicants</h1>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Opportunity</th>
                    <th>Message</th>
                    <th>CV</th>
                    <th>Applied Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $applicants->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['student_name']; ?></td>
                    <td><?php echo $row['student_email']; ?></td>
                    <td><?php echo $row['job_title']; ?></td>
                    <td><?php echo nl2br($row['message']); ?></td>
                    <td>
                        <?php if($row['cv_file']): ?>
                            <a href="../uploads/<?php echo $row['cv_file']; ?>" target="_blank" style="color:#3498db; font-weight:bold;">View CV</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('Y-m-d', strtotime($row['applied_at'])); ?></td>
                    <td><span class="status status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                    <td>
                        <?php if($row['status'] == 'pending'): ?>
                            <div class="action-links">
                                <a href="applicants.php?action=accept&aid=<?php echo $row['id']; ?>" class="action-btn accept-btn">Accept</a>
                                <a href="applicants.php?action=reject&aid=<?php echo $row['id']; ?>" class="action-btn reject-btn">Reject</a>
                            </div>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($applicants->num_rows == 0): ?>
                <tr><td colspan="8" style="text-align:center">No applications received yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
