<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/database.php';

$success = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_id = $_SESSION['user_id'];
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

    $sql = "INSERT INTO opportunities (company_id, title, category, description, location, deadline, type, internship_type, salary_type, salary_amount, salary_max, applicants_needed, status) 
            VALUES ($company_id, '$title', '$category', '$description', '$location', '$deadline', '$type', '$internship_type', '$salary_type', $salary_amount, $salary_max, $applicants_needed, 'pending')";
    
    if ($conn->query($sql)) {
        $success = "Opportunity posted successfully and is pending admin approval!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Opportunity</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f4f7f6; }
        .sidebar { width: 250px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding-top: 20px; }
        .sidebar a { display: block; padding: 15px 25px; color: #ecf0f1; text-decoration: none; }
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); }
        .form-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 600px; margin-bottom: 50px;}
        .form-group { margin-bottom: 20px; }
        .hidden { display: none; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #2c3e50; }
        input, textarea, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        textarea { height: 150px; }
        button { background: #3498db; color: white; border: none; padding: 12px 30px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .success { background: #dff0d8; color: #3c763d; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="add_opportunity.php" style="background:#34495e">Post Opportunity</a>
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
        <h1>Post New Opportunity</h1>
        <?php if($success): ?> <div class="success"><?php echo $success; ?></div> <?php endif; ?>
        
        <div class="form-card">
            <form method="POST">
                <div class="form-group">
                    <label>Job Title</label>
                    <input type="text" name="title" required placeholder="e.g. Web Development Intern">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" required placeholder="e.g. Software Engineering">
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" required placeholder="e.g. New York (or Remote)">
                </div>
                <div class="form-group">
                    <label>Opportunity Type</label>
                    <select name="type" id="typeSelect" required onchange="toggleFields()">
                        <option value="internship">Internship</option>
                        <option value="job">Job</option>
                    </select>
                </div>

                <!-- Internship Specific -->
                <div id="internshipFields" class="form-group">
                    <label>Internship Payment</label>
                    <select name="internship_type">
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>

                <!-- Job Specific -->
                <div id="jobFields" class="hidden">
                    <div class="form-group">
                        <label>Salary Type</label>
                        <select name="salary_type" id="salaryTypeSelect" onchange="toggleSalaryInputs()">
                            <option value="none">Not Disclosed</option>
                            <option value="fixed">Fixed Monthly</option>
                            <option value="range">Salary Range</option>
                        </select>
                    </div>
                    <div id="salaryFixed" class="form-group hidden">
                        <label>Monthly Salary ($)</label>
                        <input type="number" name="salary_amount" placeholder="e.g. 5000">
                    </div>
                    <div id="salaryRange" class="hidden">
                        <div class="form-group">
                            <label>Min Salary ($)</label>
                            <input type="number" name="salary_amount" placeholder="Min">
                        </div>
                        <div class="form-group">
                            <label>Max Salary ($)</label>
                            <input type="number" name="salary_max" placeholder="Max">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Number of Applicants Needed</label>
                    <input type="number" name="applicants_needed" value="1" min="1" required>
                </div>

                <div class="form-group">
                    <label>Deadline</label>
                    <input type="date" name="deadline" required>
                </div>
                <div class="form-group">
                    <label>Job Description</label>
                    <textarea name="description" required placeholder="Describe the role, requirements, and responsibilities..."></textarea>
                </div>
                <button type="submit">Post Opportunity</button>
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
    </script>
</body>
</html>
