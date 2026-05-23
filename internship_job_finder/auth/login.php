<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($password == $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] == 'admin') header("Location: ../admin/dashboard.php");
            elseif ($user['role'] == 'company') header("Location: ../company/dashboard.php");
            else header("Location: ../student/dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Internship and Job Finder</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #2c3e50; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .auth-wrapper { display: flex; flex-direction: column; align-items: center; width: 100%; max-width: 400px; padding: 20px; box-sizing: border-box; }
        .auth-container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; }
        h2 { text-align: center; color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #666; font-weight: 500; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 16px; }
        button { width: 100%; padding: 12px; background: #007bff; border: none; color: white; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; transition: background 0.3s; }
        button:hover { background: #0056b3; }
        .error { color: #d9534f; background: #f2dede; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; }
        .links { margin-top: 20px; text-align: center; color: #666; }
        .links a { color: #007bff; text-decoration: none; }
        .home-btn { display: inline-block; margin-top: 20px; text-decoration: none; color: #333; font-weight: bold; background: #e2e8f0; padding: 10px 20px; border-radius: 6px; transition: background 0.2s, color 0.2s; }
        .home-btn:hover { background: #cbd5e1; color: #000; }
        .password-wrapper { position: relative; }
        .password-wrapper input { padding-right: 40px; }
        .toggle-password {
            position: absolute !important;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none !important;
            border: none !important;
            color: #7f8c8d !important;
            cursor: pointer !important;
            font-size: 1.1em !important;
            padding: 0 !important;
            margin: 0 !important;
            width: auto !important;
            height: auto !important;
            box-shadow: none !important;
            display: inline-block !important;
            font-family: inherit;
        }
        .toggle-password:hover {
            color: #333 !important;
            background: none !important;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <h2>Login to Your Account</h2>
            <?php if($error): ?> <div class="error"><?php echo $error; ?></div> <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" required placeholder="Enter your password">
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password')">👁️</button>
                    </div>
                </div>
                <button type="submit">Login Now</button>
            </form>
            <div class="links">
                Don't have an account? <a href="register.php">Register</a>
            </div>
        </div>
        <a href="../index.php" class="home-btn">Home</a>
    </div>

    <script>
        function togglePasswordVisibility(id) {
            const el = document.getElementById(id);
            const btn = el.nextElementSibling;
            if (el.type === 'password') {
                el.type = 'text';
                btn.textContent = '🙈';
            } else {
                el.type = 'password';
                btn.textContent = '👁️';
            }
        }
    </script>
</body>
</html>
