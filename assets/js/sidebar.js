/**
 * Sidebar Navigation Controller
 * 
 * Manages the mobile sidebar menu:
 * 1. Toggled by the hamburger menu icon
 * 2. Click-outside to close functionality
 * 3. Body scroll locking when menu is active
 */

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.querySelector('.menu-toggle');

    /**
     * GLOBAL UTILITY: Toggle Sidebar
     * This function is exposed to the window for use in inline HTML event handlers.
     */
    window.toggleSidebar = function () {
        if (sidebar) {
            sidebar.classList.toggle('active');

            // UX: Lock/Unlock body scroll to prevent background scrolling when menu is open
            if (sidebar.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
    };

    /**
     * EVENT LISTENER: Click Outside Closer
     * Automatically closes the sidebar when the user clicks anywhere outside of it.
     */
    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 992 &&
            sidebar &&
            sidebar.classList.contains('active')) {

            // Validation: Only close if the click was NOT on the sidebar itself 
            // and NOT on the toggle button that opens it.
            if (!sidebar.contains(e.target) &&
                (!menuToggle || !menuToggle.contains(e.target))) {
                toggleSidebar();
            }
        }
    });

    /**
     * RESIZE HANDLER: Responsive Reset
     * Ensures the sidebar state is cleaned up if the user resizes from mobile to desktop.
     */
    window.addEventListener('resize', function () {
        if (window.innerWidth > 992) {
            if (sidebar) sidebar.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});
