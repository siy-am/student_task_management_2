<?php
/**
 * Dashboard Controller - TaskFlow
 * 
 * Main dynamic view for the user, providing:
 * 1. Task overview counters (Pending, In Progress, Completed, Overdue)
 * 2. Interactive task grid with drag-and-drop status updates
 * 3. Urgent task notifications (Overdue, Due Today, Due Tomorrow)
 * 4. Task management (Creation, Deletion, Status toggling)
 * 
 * Dependencies:
 * - includes/auth_check.php (Session validation)
 * - includes/db.php (Database connection)
 * - includes/functions.php (Business logic helpers)
 */

// 1. ENVIRONMENT SETUP
include 'includes/auth_check.php';
$current_page = 'dashboard';

include 'includes/db.php';
include 'includes/functions.php';

// 2. NOTIFICATION ENGINE
// Fetch tasks that need immediate attention
$urgent_tasks = getUrgentTasks($conn, $user_id);
$urgent_count = count($urgent_tasks);

// 3. STATISTICAL DATA AGGREGATION
// Initialize counters
$tasks = [];
$total_tasks = $completed_tasks = $inprogress_tasks = $pending_tasks = $overdue_tasks = 0;

// Fetch all active tasks for the current user
$sql = "SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
    
    $total_tasks = count($tasks);
    
    // Group tasks to calculate dashboard metrics
    $today = date('Y-m-d');
    foreach ($tasks as $task) {
        if ($task['status'] == 'Completed') $completed_tasks++;
        if ($task['status'] == 'In Progress') $inprogress_tasks++;
        if ($task['status'] == 'Pending') $pending_tasks++;
        
        // Define overdue: Not completed AND deadline has passed
        if ($task['status'] != 'Completed' && !empty($task['due_date']) && $task['due_date'] < $today) {
            $overdue_tasks++;
        }
    }
    $stmt->close();
}

/**
 * FEATURE: AJAX Status Updates
 * Handles background requests from the drag-and-drop interface
 */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_task_status'])) {
    $task_id = intval($_POST['task_id']);
    $new_status = $_POST['new_status'];
    
    // Validation: Ensure task belongs to user and status is valid
    $valid_statuses = ['Pending', 'In Progress', 'Completed'];
    $sql_check = "SELECT id FROM tasks WHERE id = ? AND user_id = ?";
    
    if ($stmt = $conn->prepare($sql_check)) {
        $stmt->bind_param("ii", $task_id, $user_id);
        $stmt->execute();
        $check_result = $stmt->get_result();
        
        if ($check_result->num_rows > 0 && in_array($new_status, $valid_statuses)) {
            $update_sql = "UPDATE tasks SET status = ? WHERE id = ?";
            if ($update_stmt = $conn->prepare($update_sql)) {
                $update_stmt->bind_param("si", $new_status, $task_id);
                $update_stmt->execute();
                echo json_encode(['success' => true, 'new_status' => $new_status]);
                $update_stmt->close();
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
        }
        $stmt->close();
    }
    exit();
}

/**
 * FEATURE: GET Status Updates (Button Clicks)
 * Handles direct link clicks from the task card buttons
 */
if (isset($_GET['update_status']) && isset($_GET['status'])) {
    $task_id = intval($_GET['update_status']);
    $new_status = $_GET['status'];
    
    // Validation
    $valid_statuses = ['Pending', 'In Progress', 'Completed'];
    
    if (in_array($new_status, $valid_statuses)) {
        $sql_check = "SELECT id FROM tasks WHERE id = ? AND user_id = ?";
        if ($stmt = $conn->prepare($sql_check)) {
            $stmt->bind_param("ii", $task_id, $user_id);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $update_sql = "UPDATE tasks SET status = ? WHERE id = ?";
                if ($update_stmt = $conn->prepare($update_sql)) {
                    $update_stmt->bind_param("si", $new_status, $task_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }
            $stmt->close();
        }
    }
    
    // Redirect to clear URL parameters
    header("Location: dashboard.php");
    exit();
}

/**
 * FEATURE: Task Creation
 * Processes new task submissions from the dashboard modal
 */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_task'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $status = $_POST['status'];
    $due_date = $_POST['due_date'];
    
    if (!empty($title)) {
        $sql_insert = "INSERT INTO tasks (user_id, title, description, category, priority, status, due_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql_insert)) {
            $stmt->bind_param("issssss", $user_id, $title, $description, $category, $priority, $status, $due_date);
            
            if ($stmt->execute()) {
                // Return to dashboard to refresh view and counters
                header("Location: dashboard.php");
                exit();
            }
            $stmt->close();
        }
    }
}

/**
 * FEATURE: Task Deletion
 * Processes requests to remove tasks permanently
 */
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql_del = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
    
    if ($stmt = $conn->prepare($sql_del)) {
        $stmt->bind_param("ii", $delete_id, $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TaskFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="assets/css/task_list_styles.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <!-- DASHBOARD CONTAINER -->
    <div class="dashboard">
        
        <?php include 'includes/header.php'; ?>
        
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- Content Header -->
            <div class="content-header">
                <h1 class="page-title">Task Dashboard</h1>
                <div class="current-date">
                    <i class="far fa-calendar"></i>
                    <span id="currentDate"><?php echo date('l, F j, Y'); ?></span>
                </div>
            </div>
            
            <!-- Stats Cards (DROP TARGETS) -->
            <div class="stats">
                <div class="stat-card pending" data-status="Pending" id="pendingStatCard">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value" id="pendingCount"><?php echo $pending_tasks; ?></div>
                    <div class="stat-label">Pending</div>
                    <div class="drop-hint">Drop tasks here</div>
                </div>
                
                <div class="stat-card in-progress" data-status="In Progress" id="inProgressStatCard">
                    <div class="stat-icon">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div class="stat-value" id="inprogressCount"><?php echo $inprogress_tasks; ?></div>
                    <div class="stat-label">In Progress</div>
                    <div class="drop-hint">Drop tasks here</div>
                </div>
                
                <div class="stat-card completed" data-status="Completed" id="completedStatCard">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value" id="completedCount"><?php echo $completed_tasks; ?></div>
                    <div class="stat-label">Completed</div>
                    <div class="drop-hint">Drop tasks here</div>
                </div>
                
                <div class="stat-card overdue" data-status="Overdue" id="overdueStatCard">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value"><?php echo $overdue_tasks; ?></div>
                    <div class="stat-label">Overdue</div>
                </div>
            </div>
            
            <!-- Instructions -->
            <div style="text-align: center; margin-bottom: 20px; color: #666; font-size: 14px;">
                <i class="fas fa-hand-point-up"></i>
                <strong>Drag & Drop:</strong> Drag tasks onto status cards above to update their status
            </div>
            
            <!-- Tasks Grid -->
            <div class="tasks-grid" id="tasksGrid">
                <?php if (empty($tasks)): ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>No tasks yet</h3>
                        <p>Start by creating your first task to get organized!</p>
                        <button class="add-task-btn add-task-trigger" style="max-width: 200px;">
                            <i class="fas fa-plus"></i> Create First Task
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <?php
                        // Check if task is overdue
                        $is_overdue = false;
                        if (!empty($task['due_date']) && $task['status'] != 'Completed') {
                            $today = date('Y-m-d');
                            if ($task['due_date'] < $today) {
                                $is_overdue = true;
                            }
                        }
                        
                        // Priority class
                        $priority_class = '';
                        if ($task['priority'] == 'High') $priority_class = 'high-priority';
                        elseif ($task['priority'] == 'Medium') $priority_class = 'medium-priority';
                        else $priority_class = 'low-priority';
                        
                        // Status class
                        $status_class = 'status-' . strtolower(str_replace(' ', '-', $task['status']));
                        ?>
                        
                        <div class="task-card <?php echo $priority_class; ?> <?php echo $is_overdue ? 'overdue' : ''; ?>"
                             data-task-id="<?php echo $task['id']; ?>"
                             data-task-status="<?php echo $task['status']; ?>"
                             draggable="true">
                            <div class="task-header">
                                <div>
                                    <h3 class="task-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                                    <span class="task-category"><?php echo htmlspecialchars($task['category']); ?></span>
                                </div>
                                <span class="priority-badge <?php echo strtolower($task['priority']); ?>">
                                    <?php echo $task['priority']; ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($task['description'])): ?>
                                <p class="task-description">
                                    <?php echo htmlspecialchars($task['description']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="task-footer">
                                <div class="task-due <?php echo $is_overdue ? 'overdue' : ''; ?>">
                                    <i class="far fa-clock"></i>
                                    <?php if (!empty($task['due_date'])): ?>
                                        Due: <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                                        <?php if ($is_overdue): ?>
                                            (Overdue)
                                        <?php endif; ?>
                                    <?php else: ?>
                                        No due date
                                    <?php endif; ?>
                                </div>
                                <div class="task-actions">
                                    <!-- Status update buttons -->
                                    <button type="button" class="action-btn status-update-btn" 
                                            data-task-id="<?php echo $task['id']; ?>" 
                                            data-new-status="Pending" 
                                            title="Mark as Pending">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                    <button type="button" class="action-btn status-update-btn" 
                                            data-task-id="<?php echo $task['id']; ?>" 
                                            data-new-status="In Progress" 
                                            title="Mark as In Progress">
                                        <i class="fas fa-spinner"></i>
                                    </button>
                                    <button type="button" class="action-btn status-update-btn" 
                                            data-task-id="<?php echo $task['id']; ?>" 
                                            data-new-status="Completed" 
                                            title="Mark as Completed">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <a href="dashboard.php?delete_id=<?php echo $task['id']; ?>" 
                                       class="action-btn delete" title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this task?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="task-status <?php echo $status_class; ?>">
                                <?php echo $task['status']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
    
    <!-- ADD TASK MODAL -->
    <div id="addTaskModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 class="modal-title">Add New Task</h2>
            <form id="addTaskForm" method="POST" action="">
                <input type="hidden" name="add_task" value="1">
                
                <div class="form-group">
                    <label>Task Title *</label>
                    <input type="text" id="title" name="title" class="form-control" placeholder="Enter task title" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="description" name="description" class="form-control" placeholder="Enter task description" rows="3"></textarea>
                </div>
                
                <!-- Category & Priority Row -->
                <div class="form-group" style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">Category</label>
                        <select id="category" name="category" class="form-control">
                            <option value="School">School</option>
                            <option value="Work">Work</option>
                            <option value="Personal">Personal</option>
                            <option value="General">General</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">Priority</label>
                        <select id="priority" name="priority" class="form-control">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                </div>
                
                <!-- Due Date -->
                <div class="form-group">
                    <label>Due Date</label>
                    <input type="date" id="due_date" name="due_date" class="form-control">
                </div>
                
                <!-- Status -->
                <div class="form-group">
                    <label>Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="Pending" selected>Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                
                <div class="modal-actions" style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-cancel">Cancel</button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Save Task
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Drag Feedback Notification -->
    <div class="drag-feedback" id="dragFeedback">
        <i class="fas fa-check-circle"></i>
        <span id="feedbackText">Task status updated!</span>
    </div>
    
    <script>
        // Pass server-side urgent task count to the notification system
        window.urgentTaskCount = <?php echo $urgent_count; ?>;
    </script>
    
    <script src="assets/js/tasks.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/dashboard.js?v=<?php echo time(); ?>"></script>
    <?php include 'includes/footer_scripts.php'; ?>
</body>
</html>