<?php
/**
 * Common Functions for TaskFlow
 */

/**
 * Fetch a list of tasks that are categorized as "Urgent"
 * Urgent tasks include Overdue, Due Today, and Due Tomorrow.
 * 
 * @param mysqli $conn The database connection object
 * @param int $user_id the ID of the logged-in user
 * @return array An array of task associative arrays with an added 'type' field
 */
if (!function_exists('getUrgentTasks')) {
    function getUrgentTasks($conn, $user_id) {
        $urgent = [];
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        // Use prepared statement to prevent SQL injection
        $sql = "SELECT id, title, due_date FROM tasks 
                WHERE user_id = ? 
                AND status != 'Completed' 
                AND due_date IS NOT NULL 
                AND due_date <= ? 
                ORDER BY due_date ASC";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("is", $user_id, $tomorrow);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                // Logic: Assign a relative 'type' based on how close the deadline is
                if ($row['due_date'] < $today) {
                    $row['type'] = 'overdue';
                } elseif ($row['due_date'] == $today) {
                    $row['type'] = 'due-today';
                } else {
                    $row['type'] = 'due-tomorrow';
                }
                $urgent[] = $row;
            }
            $stmt->close();
        }
        
        return $urgent;
    }
}
