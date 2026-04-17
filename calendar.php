<?php
/**
 * Calendar Controller - TaskFlow
 * 
 * Manages the interactive monthly calendar viewport, including:
 * 1. Pagination between months and years
 * 2. Task distribution by due date
 * 3. Color-coded priority indicators for quick analysis
 * 
 * Dependencies:
 * - includes/auth_check.php
 * - includes/db.php
 * - includes/functions.php
 */

// 1. ENVIRONMENT SETUP
include 'includes/auth_check.php';
$current_page = 'calendar';

include 'includes/db.php';
include 'includes/functions.php';

// 2. NOTIFICATION ENGINE
$urgent_tasks = getUrgentTasks($conn, $user_id);
$urgent_count = count($urgent_tasks);

// 3. PAGINATION & DATE LOGIC
// Sanitize and validate month/year inputs
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Logic: Calculate previous month/year for navigation
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

// Logic: Calculate next month/year for navigation
$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

// Logic: Calendar Grid calculation using built-in PHP date functions
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$first_day_timestamp = mktime(0, 0, 0, $month, 1, $year);
$start_day_of_week = date('w', $first_day_timestamp); // 0 (Sun) to 6 (Sat)
$month_name = date('F', $first_day_timestamp);

// 4. DATA FETCHING
// Fetch all user tasks scheduled for the currently viewed month
$tasks_by_date = [];
$start_date = "$year-$month-01";
$end_date = "$year-$month-$days_in_month";

$sql = "SELECT id, title, priority, status, due_date FROM tasks 
        WHERE user_id = ? 
        AND due_date BETWEEN ? AND ? 
        ORDER BY due_date ASC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iss", $user_id, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $day = intval(date('j', strtotime($row['due_date'])));
        if (!isset($tasks_by_date[$day])) {
            $tasks_by_date[$day] = [];
        }
        $tasks_by_date[$day][] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar View - TaskFlow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="assets/css/task_list_styles.css">
    <link rel="stylesheet" href="assets/css/calendar.css">
</head>
<body>
    <div class="dashboard">
        
        <!-- HEADER -->
        <?php include 'includes/header.php'; ?>
        
        <!-- SIDEBAR -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- MAIN CONTENT -->
        <main class="main-content">
            <div class="content-header">
                <h1 class="page-title">Calendar</h1>
                <div>
                     <a href="tasks_list.php" style="color: #666; text-decoration: none;">
                         <i class="fas fa-list"></i> List View
                     </a>
                </div>
            </div>
            
            <div class="calendar-container">
                <div class="calendar-header">
                    <div class="month-display">
                        <?php echo $month_name . ' ' . $year; ?>
                    </div>
                    <div class="calendar-nav">
                        <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="nav-btn">
                            <i class="fas fa-chevron-left"></i> Prev
                        </a>
                        <a href="calendar.php" class="nav-btn">Today</a>
                        <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="nav-btn">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="weekdays">
                    <div class="weekday">Sun</div>
                    <div class="weekday">Mon</div>
                    <div class="weekday">Tue</div>
                    <div class="weekday">Wed</div>
                    <div class="weekday">Thu</div>
                    <div class="weekday">Fri</div>
                    <div class="weekday">Sat</div>
                </div>
                
                <div class="calendar-grid">
                    <?php
                    // Empty cells for days before start of month
                    for ($i = 0; $i < $start_day_of_week; $i++) {
                        echo '<div class="calendar-day empty-day"></div>';
                    }
                    
                    // Days of the month
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $is_today = ($day == date('j') && $month == date('m') && $year == date('Y'));
                        $current_date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                        
                        echo '<div class="calendar-day ' . ($is_today ? 'day-today' : '') . '">';
                        echo '<span class="day-number">' . $day . '</span>';
                        
                        // Tasks for this day
                        if (isset($tasks_by_date[$day])) {
                            foreach ($tasks_by_date[$day] as $task) {
                                $priority_class = 'task-' . strtolower($task['priority']);
                                if ($task['status'] == 'Completed') {
                                    $priority_class .= ' task-completed';
                                }
                                echo '<a href="dashboard.php" class="task-indicator ' . $priority_class . '" title="' . htmlspecialchars($task['title']) . '">';
                                echo htmlspecialchars($task['title']);
                                echo '</a>';
                            }
                        }
                        
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
    <?php include 'includes/footer_scripts.php'; ?>
</body>
</html>
