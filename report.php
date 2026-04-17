<?php
/**
 * Reports Controller - TaskFlow
 * 
 * Generates visual and tabular performance metrics, including:
 * 1. Completion rate visualization (Circular progress)
 * 2. Task distribution by status (Summary metrics)
 * 3. Productivity by category (Bar charts)
 * 4. Recent accomplishments (Completed tasks table)
 * 
 * Dependencies:
 * - includes/auth_check.php
 * - includes/db.php
 * - includes/functions.php
 */

// 1. ENVIRONMENT SETUP
include 'includes/auth_check.php';
$current_page = 'report';

include 'includes/db.php';
include 'includes/functions.php';

// 2. NOTIFICATION ENGINE
$urgent_tasks = getUrgentTasks($conn, $user_id);
$urgent_count = count($urgent_tasks);

// 3. STATISTICAL DATA AGGREGATION
$stats = [
    'total' => 0,
    'completed' => 0,
    'in_progress' => 0,
    'pending' => 0,
    'overdue' => 0,
    'categories' => []
];

// Fetch all tasks for analytics processing
$sql = "SELECT status, category, due_date FROM tasks WHERE user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $today = date('Y-m-d');
    while($row = $result->fetch_assoc()) {
        $stats['total']++;
        
        // Status metrics
        if ($row['status'] == 'Completed') $stats['completed']++;
        if ($row['status'] == 'In Progress') $stats['in_progress']++;
        if ($row['status'] == 'Pending') $stats['pending']++;
        
        // Overdue metrics
        if ($row['status'] != 'Completed' && !empty($row['due_date']) && $row['due_date'] < $today) {
            $stats['overdue']++;
        }
        
        // Category-based productivity distribution
        $cat = $row['category'] ?: 'Uncategorized';
        if (!isset($stats['categories'][$cat])) {
            $stats['categories'][$cat] = 0;
        }
        $stats['categories'][$cat]++;
    }
    $stmt->close();
}

// Formula: Percentage of tasks marked as 'Completed'
$completion_rate = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0;

// 4. HISTORICAL DATA FETCHING
// Retrieve the 5 most recently completed tasks for the "Recent Accomplishments" section
$completed_tasks = [];
$sql_completed = "SELECT title, category, due_date FROM tasks WHERE user_id = ? AND status = 'Completed' ORDER BY due_date DESC LIMIT 5";
if ($stmt = $conn->prepare($sql_completed)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_completed = $stmt->get_result();
    while($row = $result_completed->fetch_assoc()) {
        $completed_tasks[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - TaskFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="assets/css/task_list_styles.css">
    <link rel="stylesheet" href="assets/css/report.css">
</head>
<body>
    <div class="dashboard">
        
        <?php include 'includes/header.php'; ?>
        
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- MAIN CONTENT -->
        <main class="main-content">
            <div class="content-header">
                <h1 class="page-title">Performance Report</h1>
                <div style="color: #666;">
                    <i class="fas fa-chart-line"></i> Task Analytics
                </div>
            </div>
            
            <div class="report-grid">
                <!-- Progress Card (Left) -->
                <div class="report-card">
                    <h3><i class="fas fa-check-circle"></i> Completion Rate</h3>
                    <div class="progress-container">
                        <div class="circular-progress" style="background: conic-gradient(#725AB7 <?php echo $completion_rate * 3.6; ?>deg, #f0f0f0 0deg);">
                            <div class="progress-value"><?php echo $completion_rate; ?>%</div>
                        </div>
                        <div class="completion-text">
                            Overall Task Completion Efficiency
                        </div>
                    </div>
                </div>

                <!-- Summary Card (Right) -->
                <div class="report-card">
                    <h3><i class="fas fa-chart-pie"></i> Task Distribution</h3>
                    <div class="stats-summary">
                        <div class="summary-item">
                            <div class="summary-value"><?php echo $stats['total']; ?></div>
                            <div class="summary-label">Total Tasks</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-value" style="color: #28a745;"><?php echo $stats['completed']; ?></div>
                            <div class="summary-label">Completed</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-value" style="color: #ffc107;"><?php echo $stats['in_progress']; ?></div>
                            <div class="summary-label">In Progress</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-value" style="color: #dc3545;"><?php echo $stats['overdue']; ?></div>
                            <div class="summary-label">Overdue</div>
                        </div>
                    </div>
                </div>
                
                <!-- Categories Card -->
                <div class="report-card">
                    <h3><i class="fas fa-tags"></i> Tasks by Category</h3>
                    
                    <?php if (empty($stats['categories'])): ?>
                        <p style="text-align: center; color: #999;">No category data yet.</p>
                    <?php else: ?>
                        <?php foreach($stats['categories'] as $cat => $count): ?>
                            <?php $percent = ($count / $stats['total']) * 100; ?>
                            <div class="category-bar">
                                <div class="bar-label">
                                    <span><?php echo htmlspecialchars($cat); ?></span>
                                    <span><?php echo $count; ?></span>
                                </div>
                                <div class="bar-bg">
                                    <div class="bar-fill" style="width: <?php echo $percent; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Activity Card -->
                <div class="report-card">
                    <h3><i class="fas fa-history"></i> Recently Completed</h3>
                    
                    <?php if (empty($completed_tasks)): ?>
                        <p style="text-align: center; color: #999;">No completed tasks yet.</p>
                    <?php else: ?>
                        <table class="tasks-table">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($completed_tasks as $task): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                                        <td>
                                            <?php 
                                            echo $task['due_date'] ? date('M j', strtotime($task['due_date'])) : '-'; 
                                            ?>
                                        </td>
                                        <td><span class="status-badge">Done</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
    <?php include 'includes/footer_scripts.php'; ?>
</body>
</html>
