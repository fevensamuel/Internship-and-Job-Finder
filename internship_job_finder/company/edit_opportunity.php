<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/database.php';

$id = $_GET['id'];
$company_id = $_SESSION['user_id'];

$res = $conn->query("SELECT * FROM opportunities WHERE id = $id AND company_id = $company_id");
if($res->num_rows == 0) die("Access denied");
$opp = $res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $deadline = $_POST['deadline'];
    $type = $_POST['type'];
    $internship_type = isset($_POST['internship_type']) ? $_POST['internship_type'] : 'n/a';
    $salary_type = isset($_POST['salary_type']) ? $_POST['salary_type'] : 'none';
    $salary_amount = isset($_POST['salary_amount']) ? floatval($_POST['salary_amount']) : 0.00;
    $salary_max = isset($_POST['salary_max']) ? floatval($_POST['salary_max']) : 0.00;
    $applicants_needed = isset($_POST['applicants_needed']) ? intval($_POST['applicants_needed']) : 1;

    $sql = "UPDATE opportunities SET 
            title='$title', 
            category='$category', 
            description='$description', 
            location='$location', 
            deadline='$deadline', 
            type='$type', 
            internship_type='$internship_type',
            salary_type='$salary_type',
            salary_amount=$salary_amount,
            salary_max=$salary_max,
            applicants_needed=$applicants_needed,
            status='pending' 
            WHERE id=$id";
    if ($conn->query($sql)) {
        header("Location: my_opportunities.php");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Opportunity</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f4f7f6; }
        .sidebar { width: 250px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding-top: 20px; }
        .sidebar a { display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; }
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); }
        .form-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 600px; margin-bottom: 50px; }
        .form-group { margin-bottom: 20px; }
        .hidden { display: none; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        button { background: #3498db; color: white; border: none; padding: 12px 30px; border-radius: 6px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="add_opportunity.php">Post Opportunity</a>
        <a href="my_opportunities.php">My Opportunities</a>
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
                .dark-mode input[type="date"] { color-scheme: dark !important; }
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
        <h1>Edit Opportunity</h1>
        <div class="form-card">
            <form method="POST">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="<?php echo $opp['title']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" value="<?php echo $opp['category']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?php echo $opp['location']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" id="typeSelect" onchange="toggleFields()">
                        <option value="internship" <?php if($opp['type']=='internship') echo 'selected'; ?>>Internship</option>
                        <option value="job" <?php if($opp['type']=='job') echo 'selected'; ?>>Job</option>
                    </select>
                </div>

                <!-- Internship Specific -->
                <div id="internshipFields" class="form-group">
                    <label>Internship Payment</label>
                    <select name="internship_type">
                        <option value="unpaid" <?php if($opp['internship_type']=='unpaid') echo 'selected'; ?>>Unpaid</option>
                        <option value="paid" <?php if($opp['internship_type']=='paid') echo 'selected'; ?>>Paid</option>
                    </select>
                </div>

                <!-- Job Specific -->
                <div id="jobFields" class="hidden">
                    <div class="form-group">
                        <label>Salary Type</label>
                        <select name="salary_type" id="salaryTypeSelect" onchange="toggleSalaryInputs()">
                            <option value="none" <?php if($opp['salary_type']=='none') echo 'selected'; ?>>Not Disclosed</option>
                            <option value="fixed" <?php if($opp['salary_type']=='fixed') echo 'selected'; ?>>Fixed Monthly</option>
                            <option value="range" <?php if($opp['salary_type']=='range') echo 'selected'; ?>>Salary Range</option>
                        </select>
                    </div>
                    <div id="salaryFixed" class="form-group hidden">
                        <label>Monthly Salary (ETB)</label>
                        <input type="number" name="salary_amount" value="<?php echo $opp['salary_amount']; ?>" placeholder="e.g. 5000">
                    </div>
                    <div id="salaryRange" class="hidden">
                        <div class="form-group">
                            <label>Min Salary (ETB)</label>
                            <input type="number" name="salary_amount" value="<?php echo $opp['salary_amount']; ?>" placeholder="Min">
                        </div>
                        <div class="form-group">
                            <label>Max Salary (ETB)</label>
                            <input type="number" name="salary_max" value="<?php echo $opp['salary_max']; ?>" placeholder="Max">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Number of Applicants Needed</label>
                    <input type="number" name="applicants_needed" value="<?php echo $opp['applicants_needed']; ?>" min="1" required>
                </div>

                <div class="form-group">
                    <label>Deadline</label>
                    <input type="date" name="deadline" value="<?php echo $opp['deadline']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" required><?php echo $opp['description']; ?></textarea>
                </div>
                <button type="submit">Update & Submit for Review</button>
            </form>
        </div>
    </div>

    <script>
        function toggleFields() {
            const type = document.getElementById('typeSelect').value;
            const internshipFields = document.getElementById('internshipFields');
            const jobFields = document.getElementById('jobFields');

            if (type === 'internship') {
                internshipFields.classList.remove('hidden');
                jobFields.classList.add('hidden');
            } else {
                internshipFields.classList.add('hidden');
                jobFields.classList.remove('hidden');
            }
        }

        function toggleSalaryInputs() {
            const salaryType = document.getElementById('salaryTypeSelect').value;
            document.getElementById('salaryFixed').classList.add('hidden');
            document.getElementById('salaryRange').classList.add('hidden');

            if (salaryType === 'fixed') {
                document.getElementById('salaryFixed').classList.remove('hidden');
            } else if (salaryType === 'range') {
                document.getElementById('salaryRange').classList.remove('hidden');
            }
        }
        
        // Initial setup
        toggleFields();
        toggleSalaryInputs();
    </script>
</body>
</html>
