/**
 * Notification System for TaskFlow
 * 
 * Handles:
 * 1. Browser push notifications for urgent tasks
 * 2. Real-time UI notification dropdown
 * 3. Session-based persistence for notification state
 */

document.addEventListener('DOMContentLoaded', function () {

    /**
     * INITIALIZATION: Request Browser Notification Permissions
     * We ask the user for permission early to ensure we can show 
     * desktop alerts for overdue tasks.
     */
    if ("Notification" in window) {
        if (Notification.permission !== "granted" && Notification.permission !== "denied") {
            Notification.requestPermission();
        }
    }

    /**
     * COMPONENT: Notification Dropdown Toggle Logic
     * Handles the display and interaction of the top header bell notification menu.
     */
    const bell = document.getElementById('notifBell');
    const dropdown = document.getElementById('notifDropdown');

    if (bell && dropdown) {
        // Sync UI: Hide badge if notifications were already acknowledged in this session
        if (sessionStorage.getItem('urgentViewed')) {
            const badge = bell.querySelector('.notif-badge');
            if (badge) badge.classList.add('hidden');
        }

        // Toggle UI: Open/Close notification menu
        bell.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdown.classList.toggle('active');

            // Business Logic: Mark notifications as seen once the user opens the menu
            sessionStorage.setItem('urgentViewed', 'true');
            const badge = bell.querySelector('.notif-badge');
            if (badge) badge.classList.add('hidden');
        });

        // Toggle UI: Close dropdown when clicking anywhere else on the document
        document.addEventListener('click', function (e) {
            if (!dropdown.contains(e.target) && e.target !== bell) {
                dropdown.classList.remove('active');
            }
        });

        // UX: Clear notification badge when individual alert items are clicked
        dropdown.querySelectorAll('.notif-item').forEach(item => {
            item.addEventListener('click', function () {
                sessionStorage.setItem('urgentViewed', 'true');
                const badge = bell.querySelector('.notif-badge');
                if (badge) badge.classList.add('hidden');
            });
        });
    }

    /**
     * CORE LOGIC: Browser Push Alerts
     * Triggers a native system notification if urgent tasks exist.
     * Restricted to once per session to avoid spamming the user.
     * 
     * NEW BEHAVIOR: 
     * 1. If we haven't checked for notifications this session (!notifiedOnce)
     * 2. If tasks exist, NOTIFY and set flag.
     * 3. If tasks don't exist, just SET FLAG so new tasks added during this 
     *    session won't trigger immediate alerts.
     */
    console.log('Notification Check - Urgent Count: ' + window.urgentTaskCount);
    console.log('Already Notified this session: ' + sessionStorage.getItem('notifiedOnce'));

    if (!sessionStorage.getItem('notifiedOnce')) {
        if (window.urgentTaskCount > 0 && "Notification" in window && Notification.permission === "granted") {
            const notif = new Notification("TaskFlow Alert", {
                body: `You have ${window.urgentTaskCount} urgent tasks needing attention!`,
                icon: "https://cdn-icons-png.flaticon.com/512/3119/3119338.png"
            });

            // Navigation: Take user directly to the task list when they click the alert
            notif.onclick = function () {
                sessionStorage.setItem('urgentViewed', 'true');
                window.location.href = "tasks_list.php?highlight_urgent=1";
                window.focus();
            };
        }

        // Mark as "notified/checked" for this session regardless of whether a popup was shown
        // This ensures tasks added later in the session don't trigger immediate popups
        sessionStorage.setItem('notifiedOnce', 'true');
    }
});
