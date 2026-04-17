<!-- includes/header.php -->
<header class="dashboard-header">
    <div class="header-left">
        <div class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
        <div class="logo">
            <i class="fas fa-clipboard-check"></i>
            TaskFlow
        </div>
    </div>
    
    <div class="user-info">
        <!-- Notifications Bell -->
        <div class="notifications-container">
            <div class="notif-bell" id="notifBell">
                <i class="fas fa-bell"></i>
                <?php if (isset($urgent_count) && $urgent_count > 0): ?>
                    <span class="notif-badge"><?php echo $urgent_count; ?></span>
                <?php endif; ?>
            </div>
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                    Notifications
                    <?php if (isset($urgent_count) && $urgent_count > 0): ?>
                        <span style="font-size: 11px; padding: 2px 8px; background: #fee2e2; color: #ef4444; border-radius: 10px;">Urgent</span>
                    <?php endif; ?>
                </div>
                <div class="notif-list">
                    <?php if (!isset($urgent_count) || $urgent_count === 0): ?>
                        <div class="notif-empty">
                            <i class="fas fa-bell-slash"></i>
                            <p>No urgent tasks at the moment!</p>
                        </div>
                    <?php else: ?>
                        <?php if (isset($urgent_tasks)): ?>
                            <?php foreach ($urgent_tasks as $ut): ?>
                                <a href="tasks_list.php?highlight=<?php echo $ut['id']; ?>" class="notif-item">
                                    <div class="notif-icon <?php echo $ut['type']; ?>">
                                        <i class="fas <?php echo ($ut['type'] === 'overdue') ? 'fa-exclamation-circle' : 'fa-clock'; ?>"></i>
                                    </div>
                                    <div class="notif-info">
                                        <div class="notif-title"><?php echo htmlspecialchars($ut['title']); ?></div>
                                        <div class="notif-desc">
                                            <?php 
                                                if ($ut['type'] === 'overdue') echo '<b>Overdue:</b> ';
                                                elseif ($ut['type'] === 'due-today') echo '<b>Due Today:</b> ';
                                                else echo '<b>One Day Left:</b> ';
                                            ?>
                                            <?php echo date('M d, Y', strtotime($ut['due_date'])); ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <a href="profile.php" style="text-decoration: none; display: flex; align-items: center; gap: 15px;">
            <div class="user-avatar"><?php echo strtoupper(substr($first_name, 0, 1)); ?></div>
            <div class="user-details">
                <div class="user-name" style="color: #333; font-weight: 600;"><?php echo htmlspecialchars($first_name); ?></div>
                <div class="user-role" style="color: #666; font-size: 12px;">Student</div>
            </div>
        </a>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</header>
