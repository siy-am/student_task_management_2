<?php
// profile.php - Modularized version
include 'includes/auth_check.php';
$current_page = 'profile';

include 'includes/db.php';
include 'includes/functions.php';

// Fetch urgent tasks for notifications
$urgent_tasks = getUrgentTasks($conn, $user_id);
$urgent_count = count($urgent_tasks);

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    if (!empty($first_name) && !empty($username) && !empty($email)) {
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("sssi", $first_name, $username, $email, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['first_name'] = $first_name;
            $_SESSION['username'] = $username;
            $message = "Profile updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating profile: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    } else {
        $message = "All fields are required.";
        $message_type = "error";
    }
}

// Handle Password Change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password === $confirm_password) {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($hashed_password);
            $stmt->fetch();
            
            if (password_verify($current_password, $hashed_password)) {
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_hashed_password, $user_id);
                
                if ($update_stmt->execute()) {
                    $message = "Password changed successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error updating password.";
                    $message_type = "error";
                }
                $update_stmt->close();
            } else {
                $message = "Incorrect current password.";
                $message_type = "error";
            }
            $stmt->close();
        } else {
            $message = "New passwords do not match.";
            $message_type = "error";
        }
    } else {
        $message = "All password fields are required.";
        $message_type = "error";
    }
}

// Fetch current user data
$stmt = $conn->prepare("SELECT first_name, username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - TaskFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="assets/css/task_list_styles.css">

</head>
<body>
    <div class="dashboard">
        <!-- HEADER -->
        <?php include 'includes/header.php'; ?>

        <?php include 'includes/sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <h1 class="page-title">My Profile</h1>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php if($message_type == 'success') echo '<i class="fas fa-check-circle"></i>'; else echo '<i class="fas fa-exclamation-circle"></i>'; ?>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="profile-container">
                <!-- Profile Details Card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-user-circle" style="color: #725AB7;"></i> Personal Information</h2>
                    </div>
                    <form method="POST" action="">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <button type="submit" class="btn-save">Update Profile</button>
                    </form>
                </div>

                <!-- Security Card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-lock" style="color: #725AB7;"></i> Security Settings</h2>
                    </div>
                    <form method="POST" action="">
                        <input type="hidden" name="change_password" value="1">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn-save">Change Password</button>
                    </form>
                </div>
            </div>
        </main>
        
        <?php include 'includes/footer.php'; ?>
    </div>
    <?php include 'includes/footer_scripts.php'; ?>
</body>
</html>
