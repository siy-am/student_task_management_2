<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!-- index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Welcome</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .logo {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .logo i {
            font-size: 50px;
        }

        .tagline {
            font-size: 18px;
            opacity: 0.9;
        }

        .content {
            padding: 50px;
            text-align: center;
        }

        .welcome-text {
            font-size: 28px;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .sub-text {
            font-size: 18px;
            color: #666;
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }

        .btn {
            padding: 16px 40px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.6);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #f8f9ff;
            transform: translateY(-3px);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 60px;
            text-align: left;
        }

        .feature {
            background: #f8f9ff;
            padding: 25px;
            border-radius: 15px;
            border-left: 5px solid #667eea;
        }

        .feature i {
            font-size: 30px;
            color: #667eea;
            margin-bottom: 15px;
        }

        .feature h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .feature p {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .content {
                padding: 30px 20px;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Footer Styles from Dashboard */
        .dashboard-footer {
            padding: 20px 30px;
            text-align: center;
            color: #666;
            font-size: 14px;
            border-top: 1px solid #eee;
            background: white;
            width: 100%;
        }

        .dashboard-footer .brighthub {
            color: #6875DC;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .dashboard-footer .brighthub i {
            color: #FFD700;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header - CHANGED: TaskFlow Pro → TaskFlow -->
        <div class="header">
            <div class="logo">
                <i class="fas fa-clipboard-check"></i>
                TaskFlow
            </div>
            <div class="tagline">Student Task Management System</div>
        </div>

        <!-- Main Content -->
        <div class="content">
            <h1 class="welcome-text">
                Welcome!<br>
                Manage your daily tasks, assignments, and deadlines in one place.
            </h1>
            
            <p class="sub-text">
                Login or Register to begin organizing your academic life efficiently.
                Track progress, set priorities, and never miss a deadline again.
            </p>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a>
                <a href="register.php" class="btn btn-secondary">
                    <i class="fas fa-user-plus"></i>
                    Register
                </a>
            </div>

            <!-- Features -->
            <div class="features">
                <div class="feature">
                    <i class="fas fa-tasks"></i>
                    <h3>Task Organization</h3>
                    <p>Categorize tasks by subject, priority, and due date for better management.</p>
                </div>
                <div class="feature">
                    <i class="fas fa-chart-pie"></i>
                    <h3>Progress Tracking</h3>
                    <p>Visual dashboard showing completed, pending, and overdue tasks.</p>
                </div>
                <div class="feature">
                    <i class="fas fa-bell"></i>
                    <h3>Deadline Alerts</h3>
                    <p>Get notifications for upcoming deadlines and important tasks.</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>