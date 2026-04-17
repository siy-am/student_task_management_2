<?php
/**
 * Today Controller - TaskFlow
 * 
 * Provides a focused daily view of tasks scheduled for the current date.
 * Features:
 * 1. Filtering: Automatic detection of today's date
 * 2. Sorting: Priority-first organization
 * 3. Management: Inline edit and delete capabilities
 * 
 * Dependencies:
 * - includes/auth_check.php
 * - includes/db.php
 * - includes/functions.php
 */

// 1. ENVIRONMENT SETUP
include 'includes/auth_check.php';
$current_page = 'today';

include 'includes/db.php';
include 'includes/functions.php';

// 2. NOTIFICATION ENGINE
$urgent_tasks = getUrgentTasks($conn, $user_id);
$urgent_count = count($urgent_tasks);

/** 
 * ACTION: Handle Task Deletion
 */
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Logic: Ensure ownership before deletion
    $sql_del = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql_del)) {
        $stmt->bind_param("ii", $delete_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: today.php");
    exit();
}

/** 
 * ACTION: Handle Task Editing
 */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_task'])) {
    $task_id = intval($_POST['task_id']);
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $status = $_POST['status'];
    $due_date = $_POST['due_date'];
    
    if (!empty($title)) {
        $sql_upd = "UPDATE tasks SET title=?, description=?, category=?, priority=?, status=?, due_date=? WHERE id=? AND user_id=?";
        if ($stmt = $conn->prepare($sql_upd)) {
            $stmt->bind_param("ssssssii", $title, $description, $category, $priority, $status, $due_date, $task_id, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    header("Location: today.php");
    exit();
}

/** 
 * LOGIC: Sorting & Filtering
 */
// Default: High-priority tasks appear at the top for maximum focus
$sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'priority';
$order_by = "FIELD(priority, 'High', 'Medium', 'Low'), created_at DESC";

if ($sort_option == 'created') {
    $order_by = "created_at DESC";
}

// 3. DATA FETCHING
$tasks = [];
$today_date = date('Y-m-d');
$sql_fetch = "SELECT * FROM tasks WHERE user_id = ? AND due_date = ? ORDER BY $order_by";

if ($stmt = $conn->prepare($sql_fetch)) {
    $stmt->bind_param("is", $user_id, $today_date);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Tasks - TaskFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="assets/css/task_list_styles.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/header.php'; ?>
        
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- MAIN CONTENT -->
        <main class="main-content">
            <div class="content-header">
                <h1 class="page-title">Today's Tasks <span style="font-size: 0.6em; color: #777; font-weight: 400;"><?php echo date('M d, Y'); ?></span></h1>
                
                <div class="controls">
                    <label for="sort" style="font-size: 14px; font-weight: 600; color: #555;">Sort By:</label>
                    <form action="" method="GET">
                        <select name="sort" id="sort" class="sort-select" onchange="this.form.submit()">
                            <option value="priority" <?php echo $sort_option == 'priority' ? 'selected' : ''; ?>>Priority (High to Low)</option>
                            <option value="created" <?php echo $sort_option == 'created' ? 'selected' : ''; ?>>Recently Created</option>
                        </select>
                    </form>
                </div>
            </div>
            
            <div class="task-list-container">
                <?php if (empty($tasks)): ?>
                    <div style="padding: 50px; text-align: center; color: #999;">
                        <i class="fas fa-calendar-check" style="font-size: 48px; margin-bottom: 20px; opacity: 0.3;"></i>
                        <p>No tasks due today. You're all clear!</p>
                    </div>
                <?php else: ?>
                    <table class="task-list">
                        <thead>
                            <tr>
                                <th width="35%">Task</th>
                                <th width="15%">Category</th>
                                <th width="15%">Priority</th>
                                <th width="15%">Status</th>
                                <th width="10%">Due Date</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td>
                                        <div class="cell-title"><?php echo htmlspecialchars($task['title']); ?></div>
                                        <?php if (!empty($task['description'])): ?>
                                            <div class="cell-desc"><?php echo htmlspecialchars($task['description']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="color: #666; font-size: 14px;">
                                            <i class="fas fa-tag" style="margin-right: 5px; opacity: 0.5;"></i>
                                            <?php echo htmlspecialchars($task['category']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge priority-<?php echo strtolower($task['priority']); ?>">
                                            <?php echo htmlspecialchars($task['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php $status_slug = strtolower(str_replace(' ', '-', $task['status'])); ?>
                                        <span class="badge status-<?php echo $status_slug; ?>">
                                            <?php echo htmlspecialchars($task['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="color: #555; font-size: 14px;">
                                            <?php echo $task['due_date'] ? date('M d, Y', strtotime($task['due_date'])) : '-'; ?>
                                        </div>
                                    </td>
                                    <td class="actions">
                                        <!-- Edit Trigger -->
                                        <button class="action-btn" 
                                            onclick='openEditModal(<?php echo json_encode($task); ?>)' 
                                            title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        
                                        <!-- Local Delete Link -->
                                        <a href="today.php?delete_id=<?php echo $task['id']; ?>" 
                                           class="action-btn delete" 
                                           title="Delete" 
                                           onclick="return confirm('Delete this task?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>

    <!-- EDIT TASK MODAL -->
    <div id="editTaskModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeEditModal()">&times;</span>
            <h2 class="modal-title">Edit Task</h2>
            <form action="today.php" method="POST">
                <input type="hidden" name="update_task" value="true">
                <input type="hidden" id="edit_task_id" name="task_id">
                
                <!-- Title -->
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" id="edit_title" name="title" class="form-control" required>
                </div>
                
                <!-- Category & Priority Row -->
                <div class="form-group" style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">Category</label>
                        <select id="edit_category" name="category" class="form-control">
                            <option value="General">General</option>
                            <option value="School">School</option>
                            <option value="Work">Work</option>
                            <option value="Personal">Personal</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">Priority</label>
                        <select id="edit_priority" name="priority" class="form-control">
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="edit_description" name="description" class="form-control"></textarea>
                </div>
                
                <!-- Due Date -->
                <div class="form-group">
                    <label>Due Date</label>
                    <input type="date" id="edit_due_date" name="due_date" class="form-control">
                </div>
                
                <!-- Status -->
                <div class="form-group">
                    <label>Status</label>
                    <select id="edit_status" name="status" class="form-control">
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                
                <!-- Actions -->
                <div class="modal-actions" style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Edit Modal Functions
        function openEditModal(task) {
            document.getElementById('edit_task_id').value = task.id;
            document.getElementById('edit_title').value = task.title;
            document.getElementById('edit_description').value = task.description || '';
            document.getElementById('edit_category').value = task.category;
            document.getElementById('edit_priority').value = task.priority;
            document.getElementById('edit_status').value = task.status;
            document.getElementById('edit_due_date').value = task.due_date;
            
            document.getElementById('editTaskModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editTaskModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editTaskModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
        
    </script>
    <?php include 'includes/footer_scripts.php'; ?>
</body>
</html>
