<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/database.php';

$message = "";
$message_type = "";

// Bulk Delete Expired Posts
if (isset($_POST['action']) && $_POST['action'] === 'delete_all_expired') {
    $stmt = $conn->query("DELETE FROM opportunities WHERE deadline < CURRENT_DATE()");
    if ($stmt) {
        $deleted = $conn->affected_rows;
        $message = "Successfully deleted all expired posts ($deleted posts removed).";
        $message_type = "success";
    } else {
        $message = "Error occurred while deleting expired posts: " . $conn->error;
        $message_type = "error";
    }
}

// Individual Delete Expired Post
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['oid'])) {
    $oid = intval($_GET['oid']);
    $stmt = $conn->query("DELETE FROM opportunities WHERE id = $oid AND deadline < CURRENT_DATE()");
    if ($stmt) {
        if ($conn->affected_rows > 0) {
            $message = "Expired post deleted successfully.";
            $message_type = "success";
        } else {
            $message = "Post not found or is not expired.";
            $message_type = "error";
        }
    } else {
        $message = "Error deleting post: " . $conn->error;
        $message_type = "error";
    }
}

// Fetch all expired posts
$sql = "SELECT o.*, u.full_name as company_name 
        FROM opportunities o 
        JOIN users u ON o.company_id = u.id 
        WHERE o.deadline < CURRENT_DATE() 
        ORDER BY o.deadline DESC";
$expired_jobs = $conn->query($sql);

$total_expired = $expired_jobs->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Expired Posts</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f4f7f6; }
        .sidebar { width: 250px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding-top: 20px; }
        .sidebar a { display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; }
        .sidebar a:hover { background: #34495e; }
        .main-content { margin-left: 250px; padding: 40px; width: 100%; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #34495e; color: white; }
        .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; color: white; font-size: 0.85em; display: inline-block; cursor: pointer; border: none; font-weight: bold; }
        .delete-btn { background: #e74c3c; color: white; }
        .delete-btn:hover { background: #c0392b; }
        .bulk-btn { background: #e74c3c; color: white; padding: 12px 20px; font-size: 14px; border-radius: 6px; display: inline-flex; align-items: center; transition: background 0.2s; }
        .bulk-btn:hover { background: #c0392b; }
        .bulk-container { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; }
        .bulk-info h3 { margin: 0; color: #34495e; }
        .bulk-info p { margin: 5px 0 0; color: #7f8c8d; font-size: 0.9em; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8em; }
        .badge-internship { background: #ebf5fb; color: #2980b9; }
        .badge-job { background: #e8f8f5; color: #16a085; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2 style="text-align:center">Admin Panel</h2>
        <a href="../index.php">Home Site</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_opportunities.php">Review Opportunities</a>
        <a href="manage_applications.php">Manage Applications</a>
        <a href="expired_posts.php" style="background:#34495e">Expired Posts</a>
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
                .dark-mode .bulk-container,
                .dark-mode table { background: #252525 !important; color: #f4f6f9 !important; border-color: #333333 !important; box-shadow: 0 4px 10px rgba(0,0,0,0.3) !important; }
                .dark-mode td { border-bottom: 1px solid #333333 !important; color: #f4f6f9 !important; }
                .dark-mode th { background: #1f1f1f !important; color: #ffffff !important; }
                .dark-mode h1, .dark-mode h2, .dark-mode h3, .dark-mode h4 { color: #ffffff !important; }
                .dark-mode .bulk-info p { color: #aaaaaa !important; }
                .alert-success.dark-mode-alert { background: #1b4d22 !important; color: #c3e6cb !important; border-color: #1b4d22 !important; }
                .alert-error.dark-mode-alert { background: #4d1b1b !important; color: #f5c6cb !important; border-color: #4d1b1b !important; }
                .dark-mode .badge-internship { background: #1f3a52 !important; color: #5dade2 !important; }
                .dark-mode .badge-job { background: #1f4236 !important; color: #48c9b0 !important; }
            `;
            document.head.appendChild(style);
            if (localStorage.getItem('theme_' + '<?php echo $_SESSION['user_id']; ?>') === 'dark') {
                document.body.classList.add('dark-mode');
                // Adjust alert style dark-mode elements at runtime
                window.addEventListener('DOMContentLoaded', () => {
                    const alert = document.querySelector('.alert');
                    if (alert) {
                        alert.classList.add('dark-mode-alert');
                    }
                });
            }
        })();
    </script>

    <div class="main-content">
        <h1>Expired Opportunities</h1>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="bulk-container">
            <div class="bulk-info">
                <h3>Clean up Expired Opportunities Posts</h3>
                <p>There are currently <strong><?php echo $total_expired; ?></strong> expired opportunities in the system database.</p>
            </div>
            <?php if ($total_expired > 0): ?>
                <form method="POST" onsubmit="return confirm('WARNING: This will permanently delete all <?php echo $total_expired; ?> expired posts and their applications. This cannot be undone. Are you sure?');">
                    <input type="hidden" name="action" value="delete_all_expired">
                    <button type="submit" class="bulk-btn">
                        Delete All Expired Posts
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Posted Date</th>
                    <th>Deadline Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $expired_jobs->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $row['type']; ?>">
                            <?php echo ucfirst($row['type']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                    <td style="color:#e74c3c; font-weight:bold;"><?php echo date('M d, Y', strtotime($row['deadline'])); ?> (Expired)</td>
                    <td>
                        <a href="expired_posts.php?action=delete&oid=<?php echo $row['id']; ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this expired opportunity?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($total_expired == 0): ?>
                <tr>
                    <td colspan="6" style="text-align:center; color:#7f8c8d; padding: 40px 15px;">No expired opportunities found in the system! Everything is up to date.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
