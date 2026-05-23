<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/database.php';
require_once '../models/Application.php';

$student_id = $_SESSION['user_id'];
$appModel = new Application($conn);

// Handle removal
if (isset($_GET['remove_id'])) {
    $rid = $_GET['remove_id'];
    $conn->query("DELETE FROM saved_opportunities WHERE id = $rid AND student_id = $student_id");
    header("Location: saved_opportunities.php");
    exit();
}

$sql = "SELECT s.id as saved_id, o.*, u.full_name as company_name 
        FROM saved_opportunities s 
        JOIN opportunities o ON s.opportunity_id = o.id 
        JOIN users u ON o.company_id = u.id 
        WHERE s.student_id = $student_id
        ORDER BY s.id DESC";
$saved = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Saved Jobs</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f4f7f6; }
        .sidebar { width: 250px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding-top: 20px; }
        .sidebar a { display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; }
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); box-sizing: border-box; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; margin-top: 30px; }
        .opp-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); position: relative; }
        .opp-card h3 { color: #2c3e50; margin: 0 0 10px; }
        .opp-card .meta { color: #7f8c8d; font-size: 0.9em; margin-bottom: 15px; }
        .opp-card .desc { color: #555; line-height: 1.5; margin-bottom: 20px; height: 80px; overflow: hidden; }
        .badge { display: inline-block; padding: 4px 10px; background: #e8f8f5; color: #1abc9c; border-radius: 20px; font-size: 0.8em; font-weight: bold; margin-bottom: 10px; }
        .actions { margin-top: 20px; display: flex; gap: 10px; }
        .btn { flex: 1; padding: 10px; border-radius: 6px; text-align: center; text-decoration: none; font-weight: bold; font-size: 0.9em; transition: 0.3s; }
        .apply-btn { background: #3498db; color: white; border: none; cursor:pointer; }
        .applied-btn { background: #bdc3c7; color: white; cursor: not-allowed; }
        .remove-btn { background: #fdedec; color: #e74c3c; border: 1px solid #f9d5d5; text-align: center; }
        .remove-btn:hover { background: #f9d5d5; }
        .salary-badge { background: #fef5e7; color: #e67e22; }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="opportunities.php">Find Opportunities</a>
        <a href="my_applications.php">My Applications</a>
        <a href="saved_opportunities.php" style="background:#34495e">Saved Opportunities</a>
        <a href="../settings.php">Settings</a>
        <a href="../auth/logout.php" style="color:#e74c3c">Logout</a>
    </div>

    <!-- Dark Mode Support -->
    <script>
        (function() {
            const style = document.createElement('style');
            style.id = "dark-mode-styles";
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
        <h1>Saved Opportunities</h1>
        <p style="color: #7f8c8d; margin-top: -10px;">Review opportunities you have saved for later.</p>
        
        <div class="grid">
            <?php while($row = $saved->fetch_assoc()): ?>
            <div class="opp-card">
                <span class="badge"><?php echo ucfirst($row['type']); ?></span>
                <?php if($row['type'] == 'internship' && $row['internship_type'] != 'n/a'): ?>
                    <span class="badge" style="background:#f4f6f7; color:#7f8c8d;"><?php echo ucfirst($row['internship_type']); ?></span>
                <?php endif; ?>
                
                <?php if($row['salary_type'] != 'none'): ?>
                    <span class="badge salary-badge">
                        <?php if($row['salary_type'] == 'fixed'): ?>
                            $<?php echo number_format($row['salary_amount']); ?>/mo
                        <?php else: ?>
                            $<?php echo number_format($row['salary_amount']); ?> - $<?php echo number_format($row['salary_max']); ?>/mo
                        <?php endif; ?>
                    </span>
                <?php endif; ?>

                <span class="badge" style="background:#ebf5fb; color:#2980b9;"><?php echo htmlspecialchars($row['category']); ?></span>
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <div class="meta"><?php echo htmlspecialchars($row['company_name']); ?> • <?php echo htmlspecialchars($row['location']); ?></div>
                <div class="meta" style="color:#e67e22; font-weight:bold;">
                    Deadline: <?php echo date('M d, Y', strtotime($row['deadline'])); ?> • 
                    <?php echo $row['applicants_needed']; ?> needed
                </div>
                <div class="desc"><?php echo htmlspecialchars(substr($row['description'], 0, 150)); ?>...</div>
                
                <div class="actions">
                    <?php if($appModel->hasApplied($student_id, $row['id'])): ?>
                        <button class="btn applied-btn" disabled>Applied</button>
                    <?php else: ?>
                        <a href="apply.php?id=<?php echo $row['id']; ?>" class="btn apply-btn">Apply Now</a>
                    <?php endif; ?>
                    <a href="saved_opportunities.php?remove_id=<?php echo $row['saved_id']; ?>" class="btn remove-btn">Remove</a>
                </div>
            </div>
            <?php endwhile; ?>
            <?php if($saved->num_rows == 0): ?>
                <div style="grid-column: 1 / -1; background: white; padding: 40px; text-align: center; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);" class="card">
                    <h3 style="margin-top:0; color:#e74c3c;">No Saved Jobs</h3>
                    <p style="color:#7f8c8d; margin-bottom:0;">Explore opportunities and save them to find them in this view.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
