# 📋 TaskFlow - Student Task Management System

TaskFlow is a robust, responsive web-based application developed by BrightHub groups designed to help students and professionals manage their daily tasks efficiently. With a modern, user-friendly interface, it allows users to organize tasks by categories, track deadlines via a calendar, and visualize their productivity through reports.

---

## ✨ Features

### 🔐 Authentication & Security
*   **Secure Login & Registration:** Password hashing using PHP's `password_hash`.
*   **Session Management:** Secure user sessions with timeout protection.
*   **Profile Management:** Update personal details and change passwords securely.

### 📊 Dashboard & Overview
*   **Dynamic Dashboard:** At-a-glance view of pending, completed, and urgent tasks.
*   **Urgent & Upcoming Deadlines:** Visual indicators for tasks due today or overdue.

### 📝 Task Management
*   **CRUD Operations:** Create, Read, Update, and Delete tasks easily.
*   **Priority Levels:** Categorize tasks as **High**, **Medium**, or **Low** priority.
*   **Categories:** Dedicated views for **School**, **Work**, **Personal**, and **General** tasks.
*   **Status Tracking:** Mark tasks as *Pending*, *In Progress*, or *Completed*.

### 📅 Smart Views
*   **Today's View:** A focused list of tasks due specifically today.
*   **Calendar View:** Monthly interactive calendar to visualize upcoming deadlines.
*   **List View:** Comprehensive sorted table of all tasks with filtering options.

### 📈 Analytics
*   **Productivity Reports:** Visual breakdown of task completion status.
*   **Priority Distribution:** Understand where your focus lies (e.g., how many High priority tasks).

### 🎨 UI/UX
*   **Modern Design:** Clean layout with a fixed header 
*   **Mobile Responsive:** Fully functional on mobile devices with a collapsible sidebar.
*   **Notification System:** Alerts for urgent tasks and approaching deadlines.

---

## 🛠️ Tech Stack

*   **Frontend:** HTML5, CSS3 (Custom Grid/Flexbox), JavaScript (Vanilla).
*   **Backend:** PHP (Native).
*   **Database:** MySQL / MariaDB.
*   **Font Icons:** Font Awesome 6.
*   **Server:** Apache (via XAMPP)
).

---

## 🚀 Installation Guide

Follow these steps to set up the project locally using **XAMPP**.

### Prerequisites
*   [XAMPP](https://www.apachefriends.org/index.html) installed on your machine.

### Steps

1.  **Clone/Download the Project:**
    *   Download the project files.
    *   Move the `student_task_management` folder into your XAMPP `htdocs` directory:
        `C:\xampp\htdocs\student_task_management`

2.  **Database Setup:**
    *   Open XAMPP Control Panel and start **Apache** and **MySQL**.
    *   Go to `http://localhost/phpmyadmin` in your browser.
    *   Create a new database named `student_task_db` (or check `includes/db.php` for the configured name).

    *   Create the users table. Using MySQL shell or phpMyAdmin, run:
      CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 );

  *  Create the tasks table. Using MySQL shell or phpMyAdmin, run:
    CREATE TABLE tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        category ENUM('General', 'School', 'Work', 'Personal', 'Other') DEFAULT 'General',
        priority ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
        status ENUM('Pending', 'In Progress', 'Completed') DEFAULT 'Pending',
        due_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );

3.  **Configuration:**
    *   Open `includes/db.php` and ensure the database credentials match your local setup:
        ```php
        $servername = "localhost";
        $username = "root";
        $password = ""; // Default XAMPP password is empty
        $dbname = "student_task_db";
        ```

4.  **Run the Application:**
    *   Open your browser and navigate to:
        `http://localhost/student_task_management/index.php`

---

## 📂 Project Structure

```
student_task_management/
├── assets/                     # Frontend assets
│   ├── css/                    # Component-specific stylesheets
│   │   ├── calendar.css        # Styles for calendar view
│   │   ├── dashboard.css       # Main dashboard layout styles
│   │   ├── notifications.css   # Toast and dropdown notifications
│   │   ├── report.css          # Analytics and chart styles
│   │   └── task_list_styles.css # Comprehensive table styles
│   └── js/                     # Interactive logic
│       ├── dashboard.js        # Search and modal handlers
│       ├── notifications.js    # Push and UI alerts
│       ├── sidebar.js          # Mobile toggle and active states
│       └── tasks.js            # AJAX status and drag & drop logic
├── includes/                   # Reusable backend modules
│   ├── auth_check.php          # Session security layer
│   ├── db.php                  # MySQL database connection
│   ├── footer.php              # Shared page footer
│   ├── footer_scripts.php      # Global script initializers
│   ├── functions.php           # Global business logic & SQL helpers
│   ├── header.php              # Dynamic top navigation
│   └── sidebar.php             # Categorized navigation menu
├── calendar.php                # Monthly deadline visuals
├── dashboard.php               # User landing page & stats
├── general.php                 # General category view
├── index.php                   # Landing/Redirect handler
├── login.php                   # Authentication portal
├── logout.php                  # Session termination
├── personal.php                # Personal category view
├── profile.php                 # User account management
├── register.php                # New user enrollment
├── report.php                  # Productivity analytics
├── school.php                  # School category view
├── tasks_list.php              # Master task management table
├── today.php                   # Daily focus view
└── work.php                    # Work category view
```

---

## 💡 Usage

1.  **Register:** Create a new account on the registration page.
2.  **Create Tasks:** Use the **+ New Task** button in the sidebar or in the dashboard at the right bottom to add tasks .
3.  **Manage:** Click the **Edit** (pen) icon to update status/details or **Delete** (trash) icon to remove tasks.
4.  **Track:** Check the **Calendar** and **Today** views to stay on top of deadlines.

---

## 📜 License

we package the project as a .zip for direct download.