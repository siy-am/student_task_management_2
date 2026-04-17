<?php
// register.php - COMPLETE WORKING VERSION
session_start();
include 'includes/db.php';

// Initialize variables
$first_name = $last_name = $username = $email = '';
$error = $success = '';

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Simple validation
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required";
    }
    elseif ($password != $confirm_password) {
        $error = "Passwords don't match";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strpos($email, '@') === false) {
        $error = "Please enter a valid email address containing '@'";
    }
    elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    }
    else {
        // Check if user already exists
        $check = $conn->query("SELECT id FROM users WHERE username = '$username' OR email = '$email'");
        if ($check->num_rows > 0) {
            $error = "Username or email already exists";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into database
            $sql = "INSERT INTO users (first_name, last_name, username, email, password) 
                    VALUES ('$first_name', '$last_name', '$username', '$email', '$hashed_password')";
            
            if ($conn->query($sql) === TRUE) {
                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                // Clear form
                $first_name = $last_name = $username = $email = '';
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TaskFlow</title>
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

        .register-container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .register-header {
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

        .register-form {
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

        /* Two columns for name fields */
        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .btn-register {
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

        .btn-register:hover {
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

        /* Terms checkbox */
        .terms {
            margin: 20px 0;
            font-size: 14px;
            color: #666;
        }

        .terms input {
            margin-right: 8px;
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

        /* Error/Success messages */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin: 20px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
            .register-container {
                max-width: 100%;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <!-- Header -->
        <div class="register-header">
            <div class="logo">TaskFlow</div>
            <div class="tagline">Create your account</div>
        </div>

        <!-- Display Error/Success Messages -->
        <?php if ($error): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form class="register-form" method="POST" action="">
            <!-- Name fields in row -->
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <div class="input-group">
                        <input type="text" id="first_name" name="first_name" class="form-control" 
                               placeholder="Enter first name" required 
                               value="<?php echo htmlspecialchars($first_name); ?>">
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <div class="input-group">
                        <input type="text" id="last_name" name="last_name" class="form-control" 
                               placeholder="Enter last name" required
                               value="<?php echo htmlspecialchars($last_name); ?>">
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-group">
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="Choose a username" required
                           value="<?php echo htmlspecialchars($username); ?>">
                    <i class="fas fa-id-badge input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-group">
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="Enter your email" required
                           value="<?php echo htmlspecialchars($email); ?>">
                    <i class="fas fa-envelope input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password (Min 8 chars)</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Create a password" required style="padding-right: 40px !important;">
                    <i class="fas fa-lock input-icon"></i>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('password', this)"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-group">
                    <input type="password" id="confirm_password" name="confirm_password" 
                           class="form-control" placeholder="Re-enter password" required style="padding-right: 40px !important;">
                    <i class="fas fa-lock input-icon"></i>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password', this)"></i>
                </div>
            </div>

            <!-- Terms checkbox -->
            <div class="terms">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree to the terms and conditions</label>
            </div>

            <button type="submit" class="btn-register">Create Account</button>

            <div class="divider">Already have an account?</div>

            <div class="form-links">
                <a href="login.php">Sign In to Existing Account</a>
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
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>