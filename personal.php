<?php
// personal.php - Modularized version
include 'includes/auth_check.php';
$current_page = 'personal';

include 'includes/db.php';
include 'includes/functions.php';

// Fetch urgent tasks for notifications
$urgent_tasks = getUrgentTasks($conn, $user_id);
$urgent_count = count($urgent_tasks);


if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM tasks WHERE id = $delete_id AND user_id = $user_id");
    header("Location: personal.php"); exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_task'])) {
    $stmt = $conn->prepare("UPDATE tasks SET title=?, description=?, category=?, priority=?, status=?, due_date=? WHERE id=? AND user_id=?");
    $stmt->bind_param("ssssssii", $_POST['title'], $_POST['description'], $_POST['category'], $_POST['priority'], $_POST['status'], $_POST['due_date'], $_POST['task_id'], $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: personal.php"); exit();
}

$sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'created';
$order_by = "created_at DESC";
switch ($sort_option) {
    case 'date_asc': $order_by = "due_date ASC"; break;
    case 'date_desc': $order_by = "due_date DESC"; break;
    case 'priority': $order_by = "FIELD(priority, 'High', 'Medium', 'Low')"; break;
    case 'created': $order_by = "created_at DESC"; break;
}

$tasks = [];
$sql = "SELECT * FROM tasks WHERE user_id = $user_id AND category = 'Personal' ORDER BY $order_by";
$result = $conn->query($sql);
if ($result->num_rows > 0) { while($row = $result->fetch_assoc()) { $tasks[] = $row; } }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Tasks - TaskFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="assets/css/task_list_styles.css">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/header.php'; ?>
        
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1 class="page-title">Personal Tasks</h1>
                <div class="controls">
                    <label for="sort" style="font-size: 14px; font-weight: 600; color: #555;">Sort By:</label>
                    <form action="" method="GET">
                        <select name="sort" id="sort" class="sort-select" onchange="this.form.submit()">
                            <option value="created" <?php echo $sort_option == 'created' ? 'selected' : ''; ?>>Recently Created</option>
                            <option value="date_asc" <?php echo $sort_option == 'date_asc' ? 'selected' : ''; ?>>Due Date (Earliest)</option>
                            <option value="date_desc" <?php echo $sort_option == 'date_desc' ? 'selected' : ''; ?>>Due Date (Latest)</option>
                            <option value="priority" <?php echo $sort_option == 'priority' ? 'selected' : ''; ?>>Priority (High to Low)</option>
                        </select>
                    </form>
                </div>
            </div>
            
            
            <div class="task-list-container">
                <?php if (empty($tasks)): ?>
                    <div style="padding: 50px; text-align: center; color: #999;">
                        <i class="fas fa-user" style="font-size: 48px; margin-bottom: 20px; opacity: 0.3;"></i>
                        <p>No personal tasks found.</p>
                    </div>
                <?php else: ?>
                    <table class="task-list">
                        <thead><tr><th>Task</th><th>Category</th><th>Priority</th><th>Status</th><th>Due Date</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td><div class="cell-title"><?php echo htmlspecialchars($task['title']); ?></div></td>
                                    <td><?php echo htmlspecialchars($task['category']); ?></td>
                                    <td><span class="badge priority-<?php echo strtolower($task['priority']); ?>"><?php echo htmlspecialchars($task['priority']); ?></span></td>
                                    <td><span class="badge status-<?php echo strtolower(str_replace(' ','-',$task['status'])); ?>"><?php echo htmlspecialchars($task['status']); ?></span></td>
                                    <td><?php echo $task['due_date']; ?></td>
                                    <td class="actions">
                                        <button class="action-btn" onclick='openEditModal(<?php echo json_encode($task); ?>)'><i class="fas fa-pen"></i></button>
                                        <a href="personal.php?delete_id=<?php echo $task['id']; ?>" class="action-btn delete" onclick="return confirm('Delete?');"><i class="fas fa-trash"></i></a>
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
    
    <div id="editTaskModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeEditModal()">&times;</span>
            <h2 class="modal-title">Edit Task</h2>
            <form action="personal.php" method="POST">
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
                
                <div class="modal-actions" style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    <script>
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
        function closeEditModal() { document.getElementById('editTaskModal').style.display = 'none'; }
    </script>
    <?php include 'includes/footer_scripts.php'; ?>
</body>
</html>
