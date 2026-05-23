<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/database.php';
require_once '../models/Opportunity.php';
require_once '../models/Application.php';

$student_id = $_SESSION['user_id'];
$oppModel = new Opportunity($conn);
$appModel = new Application($conn);

// Handle Saving/Unsaving
if (isset($_GET['save_id'])) {
    $sid = $_GET['save_id'];
    $check = $conn->query("SELECT id FROM saved_opportunities WHERE student_id = $student_id AND opportunity_id = $sid");
    if($check->num_rows == 0) {
        $conn->query("INSERT INTO saved_opportunities (student_id, opportunity_id) VALUES ($student_id, $sid)");
    } else {
        $conn->query("DELETE FROM saved_opportunities WHERE student_id = $student_id AND opportunity_id = $sid");
    }
    
    // Build redirect with search/filter parameters preserved
    $redirect_url = "opportunities.php";
    $params = [];
    if (isset($_GET['search'])) $params['search'] = $_GET['search'];
    if (isset($_GET['category'])) $params['category'] = $_GET['category'];
    if (isset($_GET['type'])) $params['type'] = $_GET['type'];
    if (!empty($params)) {
        $redirect_url .= "?" . http_build_query($params);
    }
    header("Location: " . $redirect_url);
    exit();
}

// Get saved IDs for this student
$saved_res = $conn->query("SELECT opportunity_id FROM saved_opportunities WHERE student_id = $student_id");
$saved_ids = [];
while($s_row = $saved_res->fetch_assoc()) {
    $saved_ids[] = $s_row['opportunity_id'];
}

// Fetch distinct categories for the filter select dropdown list
$categories_res = $conn->query("SELECT DISTINCT category FROM opportunities WHERE status = 'approved' AND category != '' ORDER BY category ASC");
$categories = [];
if ($categories_res) {
    while($cat_row = $categories_res->fetch_assoc()) {
        $categories[] = $cat_row['category'];
    }
}

// Check search and filter conditions
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, trim($_GET['category'])) : '';
$type = isset($_GET['type']) ? mysqli_real_escape_string($conn, trim($_GET['type'])) : '';

$sql = "SELECT o.*, u.full_name as company_name 
        FROM opportunities o 
        JOIN users u ON o.company_id = u.id 
        WHERE o.status = 'approved'";

if ($search !== '') {
    $sql .= " AND (o.title LIKE '%$search%' OR o.description LIKE '%$search%')";
}
if ($category !== '') {
    $sql .= " AND o.category = '$category'";
}
if ($type !== '') {
    $sql .= " AND o.type = '$type'";
}

$sql .= " ORDER BY o.created_at DESC";
$opportunities = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find Opportunities</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f4f7f6; }
        .sidebar { width: 250px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding-top: 20px; }
        .sidebar a { display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; }
        .main-content { margin-left: 250px; padding: 40px; width: 100%; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; margin-top: 30px; }
        .opp-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); position: relative; }
        .opp-card h3 { color: #2c3e50; margin: 0 0 10px; }
        .opp-card .meta { color: #7f8c8d; font-size: 0.9em; margin-bottom: 15px; }
        .opp-card .desc { color: #555; line-height: 1.5; margin-bottom: 20px; height: 80px; overflow: hidden; }
        .badge { display: inline-block; padding: 4px 10px; background: #e8f8f5; color: #1abc9c; border-radius: 20px; font-size: 0.8em; font-weight: bold; margin-bottom: 10px; }
        .actions { display: flex; gap: 10px; }
        .btn { flex: 1; padding: 10px; border-radius: 6px; text-align: center; text-decoration: none; font-weight: bold; font-size: 0.9em; transition: 0.3s; }
        .apply-btn { background: #3498db; color: white; border: none; cursor:pointer; }
        .applied-btn { background: #bdc3c7; color: white; cursor: not-allowed; }
        .save-btn { border: 1px solid #3498db; color: #3498db; }
        .unsave-btn { background: #3498db; color: white; border: 1px solid #3498db; }
        .save-btn:hover { background: #f0f7ff; }
        .salary-badge { background: #fef5e7; color: #e67e22; }

        /* Search and Filter Styles */
        .search-container { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 25px; }
        .search-form { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .search-group { flex: 1; min-width: 200px; }
        .search-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #34495e; font-size: 0.9em; }
        .search-input, .search-select { width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 0.95em; }
        .search-btn, .clear-btn { padding: 11px 20px; border-radius: 6px; font-weight: bold; font-size: 0.9em; cursor: pointer; text-decoration: none; text-align: center; border: none; display: inline-block; }
        .search-btn { background: #3498db; color: white; }
        .search-btn:hover { background: #2980b9; }
        .clear-btn { background: #95a5a6; color: white; }
        .clear-btn:hover { background: #7f8c8d; }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="opportunities.php" style="background:#34495e">Find Opportunities</a>
        <a href="my_applications.php">My Applications</a>
        <a href="saved_opportunities.php">Saved Opportunities</a>
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
                .dark-mode .search-container,
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
        <h1>Latest Opportunities</h1>

        <!-- Search and Filter Panel -->
        <div class="search-container">
            <form method="GET" action="opportunities.php" class="search-form">
                <div class="search-group">
                    <label for="search">Keyword Search</label>
                    <input type="text" name="search" id="search" class="search-input" placeholder="Title, keywords, description..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="search-group">
                    <label for="category">Category</label>
                    <select name="category" id="category" class="search-select">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php if($category === $cat) echo 'selected'; ?>><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="search-group">
                    <label for="type">Opportunity Type</label>
                    <select name="type" id="type" class="search-select">
                        <option value="">All Types</option>
                        <option value="internship" <?php if($type === 'internship') echo 'selected'; ?>>Internship</option>
                        <option value="job" <?php if($type === 'job') echo 'selected'; ?>>Job</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="search-btn">Search</button>
                    <?php if(!empty($search) || !empty($category) || !empty($type)): ?>
                        <a href="opportunities.php" class="clear-btn">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="grid">
            <?php if($opportunities->num_rows == 0): ?>
                <div style="grid-column: 1 / -1; background: white; padding: 40px; text-align: center; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);" class="card">
                    <h3 style="margin-top:0; color:#e74c3c;">No Opportunities Found</h3>
                    <p style="color:#7f8c8d; margin-bottom:0;">Try adjusting your keyword search, category selector, or opportunity type filter.</p>
                </div>
            <?php endif; ?>
            <?php while($row = $opportunities->fetch_assoc()): ?>
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

                <span class="badge" style="background:#ebf5fb; color:#2980b9;"><?php echo $row['category']; ?></span>
                <h3><?php echo $row['title']; ?></h3>
                <div class="meta"><?php echo $row['company_name']; ?> • <?php echo $row['location']; ?></div>
                <div class="meta" style="color:#e67e22; font-weight:bold;">
                    Deadline: <?php echo date('M d, Y', strtotime($row['deadline'])); ?> • 
                    <?php echo $row['applicants_needed']; ?> needed
                </div>
                <div class="desc"><?php echo substr($row['description'], 0, 150); ?>...</div>
                
                <div class="actions">
                    <?php if($appModel->hasApplied($student_id, $row['id'])): ?>
                        <button class="btn applied-btn" disabled>Applied</button>
                    <?php else: ?>
                        <a href="apply.php?id=<?php echo $row['id']; ?>" class="btn apply-btn">Apply Now</a>
                    <?php endif; ?>
                    
                    <?php 
                    $query_string = "";
                    $params = [];
                    if (!empty($search)) $params['search'] = $search;
                    if (!empty($category)) $params['category'] = $category;
                    if (!empty($type)) $params['type'] = $type;
                    if (!empty($params)) {
                        $query_string = "&" . http_build_query($params);
                    }
                    ?>
                    <?php if(in_array($row['id'], $saved_ids)): ?>
                        <a href="opportunities.php?save_id=<?php echo $row['id']; ?><?php echo $query_string; ?>" class="btn unsave-btn">Saved ✓</a>
                    <?php else: ?>
                        <a href="opportunities.php?save_id=<?php echo $row['id']; ?><?php echo $query_string; ?>" class="btn save-btn">Save</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
