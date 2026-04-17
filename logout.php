<?php
// logout.php - logic to destroy session
session_start();
// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();
?>
<!-- logout.php - Simple logout page -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - TaskFlow</title>
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

        .logout-container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: center;
        }

        .logout-header {
            background: #725AB7;
            color: white;
            padding: 40px;
        }

        .logo {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .logout-icon {
            font-size: 60px;
            color: #6875DC;
            margin: 20px 0;
        }

        .logout-content {
            padding: 40px;
        }

        .logout-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }

        .logout-message {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .btn-login {
            background: #6875DC;
            color: white;
        }

        .btn-login:hover {
            background: #5a65c9;
        }

        .btn-home {
            background: #f0f0f0;
            color: #333;
        }

        .btn-home:hover {
            background: #e0e0e0;
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

        @media (max-width: 768px) {
            .logout-container {
                max-width: 100%;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <!-- Header -->
        <div class="logout-header">
            <div class="logo">
                <i class="fas fa-clipboard-check"></i>
                TaskFlow
            </div>
        </div>

        <!-- Logout Content -->
        <div class="logout-content">
            <div class="logout-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            
            <h1 class="logout-title">You have been logged out</h1>
            
            <p class="logout-message">
                Thank you for using TaskFlow. Your session has been successfully ended.
                <br><br>
                For security reasons, please close your browser if you're on a shared computer.
            </p>
            
            <div class="action-buttons">
                <a href="login.php" class="btn btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login Again
                </a>
                <a href="index.php" class="btn btn-home">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>

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

    <!-- Auto-redirect after 5 seconds (optional) -->
    <script>
        // Clear notification flags
        sessionStorage.removeItem('notifiedOnce');
        sessionStorage.removeItem('urgentViewed');
        
        // Optional: Auto redirect to login after 5 seconds
        setTimeout(function() {
            window.location.href = "login.php";
        }, 5000); // 5000 milliseconds = 5 seconds
    </script>
</body>
</html>