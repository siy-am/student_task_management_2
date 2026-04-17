<!-- includes/sidebar.php -->
<aside class="sidebar" id="sidebar">
    <?php if ($current_page === 'dashboard'): ?>
    <button class="add-task-btn add-task-trigger" id="addTaskBtn">
        <i class="fas fa-plus"></i> New Task
    </button>
    <?php else: ?>
    <a href="dashboard.php" class="add-task-btn">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    <?php endif; ?>
    
    <div class="sidebar-section">
        <div class="section-title">Dashboard</div>
        <ul class="sidebar-nav">
            <li><a href="dashboard.php" <?php echo $current_page === 'dashboard' ? 'class="active"' : ''; ?>><i class="fas fa-tachometer-alt"></i> Overview</a></li>
            <li><a href="calendar.php" <?php echo $current_page === 'calendar' ? 'class="active"' : ''; ?>><i class="fas fa-calendar"></i> Calendar</a></li>
            <li><a href="report.php" <?php echo $current_page === 'report' ? 'class="active"' : ''; ?>><i class="fas fa-chart-bar"></i> Reports</a></li>
        </ul>
    </div>
    
    <div class="sidebar-section">
        <div class="section-title">Tasks</div>
        <ul class="sidebar-nav">
            <li><a href="tasks_list.php" <?php echo $current_page === 'tasks_list' ? 'class="active"' : ''; ?>><i class="fas fa-list"></i> All Tasks</a></li>
            <li><a href="today.php" <?php echo $current_page === 'today' ? 'class="active"' : ''; ?>><i class="fas fa-clock"></i> Today</a></li>
        </ul>
    </div>

    <div class="sidebar-section">
        <div class="section-title">Categories</div>
        <ul class="sidebar-nav">
            <li><a href="general.php" <?php echo $current_page === 'general' ? 'class="active"' : ''; ?>><i class="fas fa-layer-group"></i> General</a></li>
            <li><a href="school.php" <?php echo $current_page === 'school' ? 'class="active"' : ''; ?>><i class="fas fa-graduation-cap"></i> School</a></li>
            <li><a href="work.php" <?php echo $current_page === 'work' ? 'class="active"' : ''; ?>><i class="fas fa-briefcase"></i> Work</a></li>
            <li><a href="personal.php" <?php echo $current_page === 'personal' ? 'class="active"' : ''; ?>><i class="fas fa-user"></i> Personal</a></li>
        </ul>
    </div>
</aside>
