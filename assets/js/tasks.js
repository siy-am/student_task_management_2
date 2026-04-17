/**
 * Core Task Management Logic for TaskFlow
 * 
 * Handles:
 * 1. Drag and Drop status updates
 * 2. AJAX status synchronization with the server
 * 3. Real-time UI updates (badges, stats counters)
 * 4. Auto-scrolling during drag operations
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log('Task Management System Initialized');
    console.log('Pathname: ' + window.location.pathname);

    // ===== STATE MANAGEMENT =====
    var draggedTask = null;
    var dragFeedback = document.getElementById('dragFeedback');

    // ===== AUTO SCROLL CONFIGURATION =====
    var SCROLL_ZONE = 100;
    var SCROLL_SPEED = 10;
    var scrollDirection = 0;

    function autoScroll() {
        if (draggedTask && scrollDirection !== 0) {
            window.scrollBy(0, scrollDirection * SCROLL_SPEED);
            requestAnimationFrame(autoScroll);
        }
    }

    document.addEventListener('dragover', function (e) {
        if (!draggedTask) return;

        var y = e.clientY;
        var h = window.innerHeight;
        var newDirection = 0;

        if (y < SCROLL_ZONE) {
            newDirection = -1;
        } else if (y > h - SCROLL_ZONE) {
            newDirection = 1;
        }

        if (scrollDirection !== newDirection) {
            var wasScrolling = scrollDirection !== 0;
            scrollDirection = newDirection;

            if (!wasScrolling && scrollDirection !== 0) {
                requestAnimationFrame(autoScroll);
            }
        }
    });

    document.addEventListener('dragend', function () {
        scrollDirection = 0;
    });

    /**
     * INITIALIZATION: Draggable Tasks
     */
    function initDraggableTasks() {
        var tasks = document.querySelectorAll('.task-card');
        tasks.forEach(function (task) {
            task.addEventListener('dragstart', function (e) {
                draggedTask = this;
                this.classList.add('dragging');
                e.dataTransfer.setData('text/plain', this.dataset.taskId);
                e.dataTransfer.effectAllowed = 'move';
            });

            task.addEventListener('dragend', function () {
                this.classList.remove('dragging');
                draggedTask = null;
                document.querySelectorAll('.stat-card').forEach(function (card) {
                    card.classList.remove('drop-over');
                });
            });
        });
    }

    /**
     * INITIALIZATION: Status Update Buttons
     */
    function initStatusUpdateButtons() {
        var buttons = document.querySelectorAll('.status-update-btn');
        buttons.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var taskId = this.getAttribute('data-task-id');
                var newStatus = this.getAttribute('data-new-status');
                var taskCard = this.closest('.task-card');

                if (taskCard && taskId && newStatus) {
                    var oldStatus = taskCard.getAttribute('data-task-status');

                    if (newStatus === oldStatus) return;

                    handleStatusUpdate(taskCard, taskId, oldStatus, newStatus);
                }
            });
        });
    }

    /**
     * CORE LOGIC: Unified handler for status updates
     */
    function handleStatusUpdate(taskCard, taskId, oldStatus, newStatus) {
        console.log('handleStatusUpdate: Updating task ' + taskId + ' from ' + oldStatus + ' to ' + newStatus);

        // STEP 1: Update Task UI Card
        var statusBadge = taskCard.querySelector('.task-status');
        if (statusBadge) {
            statusBadge.textContent = newStatus;
            statusBadge.className = 'task-status status-' + newStatus.toLowerCase().replace(/\s+/g, '-');
        }
        taskCard.setAttribute('data-task-status', newStatus);

        // STEP 2: Update Statistics Counters
        updateStatCounts(oldStatus, newStatus);

        // STEP 3: Provide Visual Feedback (Toast)
        showDragFeedback(newStatus);

        // STEP 4: Persist to Database via AJAX
        updateTaskStatusOnServer(taskId, newStatus);
    }

    /**
     * INITIALIZATION: Drop Targets (Stat Cards)
     */
    function initDropTargets() {
        var statCards = document.querySelectorAll('.stat-card.pending, .stat-card.in-progress, .stat-card.completed');

        statCards.forEach(function (card) {
            card.addEventListener('dragover', function (e) {
                e.preventDefault();
                this.classList.add('drop-over');
            });

            card.addEventListener('dragleave', function () {
                this.classList.remove('drop-over');
            });

            card.addEventListener('drop', function (e) {
                e.preventDefault();
                this.classList.remove('drop-over');

                if (draggedTask) {
                    var taskId = draggedTask.getAttribute('data-task-id');
                    var oldStatus = draggedTask.getAttribute('data-task-status');
                    var newStatus = this.getAttribute('data-status');

                    if (newStatus && newStatus !== oldStatus) {
                        handleStatusUpdate(draggedTask, taskId, oldStatus, newStatus);
                    }
                }
            });
        });
    }

    /**
     * UI HELPER: Update stat card counts in real-time
     */
    function updateStatCounts(oldStatus, newStatus) {
        function getStatusId(status) {
            if (!status) return '';
            return status.toLowerCase().replace(/\s+/g, '') + 'Count';
        }

        var oldId = getStatusId(oldStatus);
        var newId = getStatusId(newStatus);

        var oldCountElem = document.getElementById(oldId);
        var newCountElem = document.getElementById(newId);

        if (oldCountElem) {
            var oldCount = parseInt(oldCountElem.textContent) || 0;
            oldCountElem.textContent = Math.max(0, oldCount - 1);
        }

        if (newCountElem) {
            var newCount = parseInt(newCountElem.textContent) || 0;
            newCountElem.textContent = newCount + 1;
        }
    }

    /**
     * UI HELPER: Show temporary success feedback
     */
    function showDragFeedback(newStatus) {
        if (!dragFeedback) return;

        dragFeedback.style.display = 'block';

        var bgColor = '#6c757d';
        if (newStatus === 'Completed') bgColor = '#28a745';
        else if (newStatus === 'In Progress') bgColor = '#ffc107';

        dragFeedback.style.background = bgColor;

        var textElem = dragFeedback.querySelector('#feedbackText');
        if (textElem) textElem.textContent = 'Task marked as "' + newStatus + '"';

        setTimeout(function () {
            dragFeedback.style.display = 'none';
        }, 2000);
    }

    /**
     * AJAX HELPER: Sync task status update to the server
     */
    function updateTaskStatusOnServer(taskId, newStatus) {
        var formData = new FormData();
        formData.append('update_task_status', 'true');
        formData.append('task_id', taskId);
        formData.append('new_status', newStatus);

        fetch('dashboard.php', {
            method: 'POST',
            body: formData
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(function (data) {
                if (!data.success) {
                    console.error('Server error:', data.error);
                }
            })
            .catch(function (error) {
                console.error('Fetch error:', error);
            });
    }

    // Run initializations
    initDraggableTasks();
    initDropTargets();
    initStatusUpdateButtons();
});
