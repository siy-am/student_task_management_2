/**
 * Dashboard-Specific Interactivity for TaskFlow
 * 
 * Handles:
 * 1. Real-time task searching/filtering
 * 2. Add Task modal form handlers (open/close)
 * 3. Add Task modal form validation
 * 4. General UI helpers specific to the dashboard view
 */

document.addEventListener('DOMContentLoaded', function () {

    /**
     * FEATURE: Real-time Task Search
     * Filters task cards as the user types in the search bar.
     */
    const searchInput = document.getElementById('searchTasks');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const taskCards = document.querySelectorAll('.task-card');

            taskCards.forEach(card => {
                const title = card.querySelector('.task-title').textContent.toLowerCase();
                const description = card.querySelector('.task-description')?.textContent.toLowerCase() || '';

                // Logic: Match search term against title OR description
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    /**
     * COMPONENT: Add Task Modal Handlers
     * Manages opening and closing the "Create Task" modal.
     */
    const modal = document.getElementById('addTaskModal');
    const triggers = document.querySelectorAll('.add-task-trigger');
    const closeBtn = document.querySelector('.close-modal');
    const cancelBtn = document.querySelector('.btn-cancel');

    // Open Modal: Attach listener to all trigger buttons
    triggers.forEach(trigger => {
        trigger.addEventListener('click', function () {
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden'; // Lock background scroll
            }
        });
    });

    // Close Modal: UI helper
    function closeTaskModal() {
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore background scroll
        }
    }

    if (closeBtn) closeBtn.onclick = closeTaskModal;
    if (cancelBtn) cancelBtn.onclick = closeTaskModal;

    // Close Modal: Click outside sensitivity
    window.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeTaskModal();
        }
    });

    /**
     * COMPONENT: Add Task Modal Validation
     * Ensures users are aware if they haven't selected a due date.
     */
    const addTaskForm = document.getElementById('addTaskForm');
    if (addTaskForm) {
        addTaskForm.addEventListener('submit', function (e) {
            const dateField = document.getElementById('due_date');

            // UX Rule: If no date is set, ask user if they want to default to TODAY
            if (!dateField.value) {
                e.preventDefault(); // Pause submission

                const today = new Date().toISOString().split('T')[0];
                if (confirm("You haven't selected a due date. \n\nClick OK to automatically set it to TODAY, or Cancel to go back and choose a date manually.")) {
                    dateField.value = today;
                    this.submit(); // Resume submission with defaulted date
                }
            }
        });
    }
});
