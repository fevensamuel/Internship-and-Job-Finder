<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship & Job Finder</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; background-color: #f9f9f9; }
        header { background: #2c3e50; color: white; padding: 60px 20px; text-align: center; }
        header h1 { margin: 0; font-size: 3em; }
        header p { font-size: 1.2em; opacity: 0.9; }
        .nav { background: white; padding: 15px 10%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); sticky; top: 0; z-index: 100; }
        .nav .logo { font-size: 1.5em; font-weight: bold; color: #2c3e50; text-decoration: none; }
        .nav-links a { text-decoration: none; margin-left: 20px; padding: 8px 20px; border-radius: 5px; font-weight: 500; }
        .login-btn { color: #3498db; border: 1px solid #3498db; }
        .register-btn { background: #3498db; color: white; }
        .dashboard-btn { background: #2ecc71; color: white; }
        .container { max-width: 1200px; margin: 50px auto; padding: 0 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; transition: transform 0.3s; }
        .card:hover { transform: translateY(-5px); }
        .card i { font-size: 3em; color: #3498db; margin-bottom: 20px; display: block; }
        .card h3 { color: #333; }
        .card p { color: #666; line-height: 1.6; }
        footer { background: #2c3e50; color: white; text-align: center; padding: 30px 20px; margin-top: 50px; }
    </style>
</head>
<body>

<div class="nav">
    <a href="index.php" class="logo">CareerPortal</a>
    <div class="nav-links">
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php 
                $dash_link = "student/dashboard.php";
                if($_SESSION['role'] == 'admin') $dash_link = "admin/dashboard.php";
                if($_SESSION['role'] == 'company') $dash_link = "company/dashboard.php";
            ?>
            <a href="<?php echo $dash_link; ?>" class="dashboard-btn">My Dashboard</a>
            <a href="auth/logout.php" class="login-btn">Logout</a>
        <?php else: ?>
            <a href="auth/login.php" class="login-btn">Login</a>
            <a href="auth/register.php" class="register-btn">Register</a>
        <?php endif; ?>
    </div>
</div>

<header>
    <h1>Find Your Dream Career</h1>
    <p>Connecting the best students with the most innovative companies.</p>
</header>

<div class="container">
    <div class="card">
        <h3>For Students</h3>
        <p>Browse thousands of internships and full-time positions. Apply with one click and track your progress.</p>
    </div>
    <div class="card">
        <h3>For Companies</h3>
        <p>Post jobs and reach talented students. Manage applications and hire the best fit for your team.</p>
    </div>
    <div class="card">
        <h3>Moderated Content</h3>
        <p>Every job posting is reviewed by our admins to ensure the highest quality and safety for our users.</p>
    </div>
</div>

<footer>
    <p>&copy; 2026 Internship and Job Finder System. All Rights Reserved.</p>
</footer>

</body>
</html>
