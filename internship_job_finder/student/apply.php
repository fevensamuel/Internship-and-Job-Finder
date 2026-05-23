<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../config/database.php';
require_once '../models/Application.php';
require_once '../models/Opportunity.php';

$student_id = $_SESSION['user_id'];
$opportunity_id = $_GET['id'];

$oppModel = new Opportunity($conn);
$opp = $oppModel->getById($opportunity_id);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];
    $cv_file_name = "";

    // Handle File Upload
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] == 0) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_ext = pathinfo($_FILES["cv"]["name"], PATHINFO_EXTENSION);
        $cv_file_name = time() . "_" . $student_id . "." . $file_ext;
        move_uploaded_file($_FILES["cv"]["tmp_name"], $target_dir . $cv_file_name);
    }

    $app = new Application($conn);
    $app->apply($student_id, $opportunity_id, $message, $cv_file_name);
    header("Location: my_applications.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply for <?php echo $opp['title']; ?></title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .form-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        h2 { color: #2c3e50; margin-bottom: 5px; }
        .company { color: #7f8c8d; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; height: 120px; }
        input[type="file"] { width: 100%; padding: 10px; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 6px; cursor: pointer; }
        .btn { width: 100%; padding: 12px; background: #3498db; border: none; color: white; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; margin-top: 10px; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #7f8c8d; text-decoration: none; }
    </style>
</head>
<body>
    <!-- Dark Mode Support -->
    <script>
        (function() {
            const style = document.createElement('style');
            style.id = "dark-mode-styles";
            style.innerHTML = `
                body.dark-mode { background: #1a1a1a !important; color: #f4f6f9 !important; }
                .dark-mode .form-card { background: #252525 !important; color: #f4f6f9 !important; border-color: #333333 !important; box-shadow: 0 4px 10px rgba(0,0,0,0.3) !important; }
                .dark-mode h2, .dark-mode label { color: #ffffff !important; }
                .dark-mode textarea { background: #2d2d2d !important; color: #ffffff !important; border: 1px solid #444444 !important; }
                .dark-mode input[type="file"] { background: #2d2d2d !important; color: #ffffff !important; border: 1px dashed #444444 !important; }
                .dark-mode .company, .dark-mode .back-link { color: #aaaaaa !important; }
                .dark-mode .back-link:hover { color: #ffffff !important; }
            `;
            document.head.appendChild(style);
            if (localStorage.getItem('theme_' + '<?php echo $_SESSION['user_id']; ?>') === 'dark') {
                document.body.classList.add('dark-mode');
            }
        })();
    </script>
    <div class="form-card">
        <h2>Apply for Position</h2>
        <p class="company"><?php echo $opp['title']; ?> at <?php echo $opp['company_name']; ?></p>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Application Message / Cover Letter</label>
                <textarea name="message" placeholder="Explain why you are a good fit for this role..." required></textarea>
            </div>
            <div class="form-group">
                <label>Upload CV (PDF or Word)</label>
                <input type="file" name="cv" accept=".pdf,.doc,.docx" required>
            </div>
            <button type="submit" class="btn">Submit Application</button>
        </form>
        
        <a href="opportunities.php" class="back-link">Cancel and Go Back</a>
    </div>
</body>
</html>
