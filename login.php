<?php
// login.php - COMPLETE WORKING VERSION
session_start();
include 'includes/db.php';

// Initialize variables
$username = '';
$error = '';

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Simple validation
    if (empty($username) || empty($password)) {
        $error = "Username and password are required";
    } else {
        // Check if user exists
        $sql = "SELECT id, username, password, first_name FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['loggedin'] = true;
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }
        $stmt->close();
    }
}

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TaskFlow</title>
    <!-- FontAwesome for icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .login-header {
            background: #725AB7;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .logo {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .tagline {
            font-size: 16px;
            opacity: 0.9;
        }

        .login-form {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            background: #f8f9ff;
        }

        .form-control:focus {
            outline: none;
            border-color: #725AB7;
            background: white;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: #6875DC;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #5a65c9;
        }

        .form-links {
            text-align: center;
            margin-top: 20px;
        }

        .form-links a {
            color: #725AB7;
            text-decoration: none;
            font-size: 14px;
        }

        .form-links a:hover {
            text-decoration: underline;
        }

        .divider {
            text-align: center;
            margin: 25px 0;
            color: #777;
            font-size: 14px;
        }

        .back-home {
            text-align: center;
            margin-top: 20px;
        }

        .back-home a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }

        .back-home a:hover {
            color: #725AB7;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            border-top: 1px solid #eee;
            font-size: 13px;
        }
        
        .brighthub {
            color: #6875DC;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .brighthub i {
            color: #FFD700;
        }

        /* Error message */
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin: 20px;
            border-radius: 8px;
            border: 1px solid #f5c6cb;
        }

        /* Input Group & Icons */
        .input-group {
            position: relative;
        }
        
        .form-control {
            padding-left: 40px !important; /* Space for icon */
        }
        
        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            transition: color 0.3s;
            pointer-events: none;
        }
        
        .form-control:focus ~ .input-icon {
            color: #725AB7;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            cursor: pointer;
            transition: color 0.3s;
            z-index: 10;
        }
        
        .password-toggle:hover {
            color: #725AB7;
        }

        @media (max-width: 768px) {
            .login-container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <div class="logo">TaskFlow</div>
            <div class="tagline">Welcome to your task manager</div>
        </div>

        <!-- Display Error Message -->
        <?php if ($error): ?>
            <div class="alert-error">
                <strong>Error:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form class="login-form" method="POST" action="">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <div class="input-group">
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="Enter your username or email" required 
                           value="<?php echo htmlspecialchars($username); ?>">
                    <i class="fas fa-user input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Enter your password" required style="padding-right: 40px !important;">
                    <i class="fas fa-lock input-icon"></i>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                </div>
            </div>

            <button type="submit" class="btn-login">Login</button>

            <div class="divider">Don't have an account?</div>

            <div class="form-links">
                <a href="register.php">Create New Account</a>
            </div>

            <div class="back-home">
                <a href="index.php">← Back to Home</a>
            </div>
        </form>

        <!-- Footer -->
        <div class="footer">
            <p>© 2025 TaskFlow | By 
                <span class="brighthub">
                    <i class="fas fa-sun"></i>Siyam
                </span> 
                Husen
            </p>
        </div>
    </div>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Clear notification flags on login page load to ensure fresh alerts for new session
        document.addEventListener('DOMContentLoaded', function() {
            sessionStorage.removeItem('notifiedOnce');
            sessionStorage.removeItem('urgentViewed');
        });
    </script>
</body>
</html>