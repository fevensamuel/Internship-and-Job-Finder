<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = $_POST['role'];

    $check_email = "SELECT id FROM users WHERE email = '$email'";
    $res = $conn->query($check_email);

    if ($res->num_rows > 0) {
        $error = "Email already exists!";
    } else {
        $sql = "INSERT INTO users (full_name, email, password, role) VALUES ('$full_name', '$email', '$password', '$role')";
        if ($conn->query($sql)) {
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['role'] = $role;

            // Redirect based on role immediately
            if ($role == 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($role == 'company') {
                header("Location: ../company/dashboard.php");
            } else {
                header("Location: ../student/dashboard.php");
            }
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

function mysqli_real_escape_with_string($conn, $str) {
    return mysqli_real_escape_string($conn, $str);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Internship and Job Finder</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #2c3e50; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .auth-wrapper { display: flex; flex-direction: column; align-items: center; width: 100%; max-width: 400px; padding: 20px; box-sizing: border-box; }
        .auth-container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; }
        h2 { text-align: center; color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #666; font-weight: 500; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 16px; }
        button { width: 100%; padding: 12px; background: #007bff; border: none; color: white; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; transition: background 0.3s; }
        button:hover { background: #0056b3; }
        .error { color: #d9534f; background: #f2dede; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; }
        .success { color: #3c763d; background: #dff0d8; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; }
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
            <h2>Create Account</h2>
            <?php if($error): ?> <div class="error"><?php echo $error; ?></div> <?php endif; ?>
            <?php if($success): ?> <div class="success"><?php echo $success; ?></div> <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required placeholder="Enter your name">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="Enter email">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" required placeholder="Create password">
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password')">👁️</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Register As</label>
                    <select name="role" required>
                        <option value="student">Student</option>
                        <option value="company">Company</option>
                    </select>
                </div>
                <button type="submit">Register Now</button>
            </form>
            <div class="links">
                Already have an account? <a href="login.php">Login</a>
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
