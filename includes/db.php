<?php
// ============================================================
// FILE: includes/db.php
// PURPOSE: Database connection using MySQLi
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change to your MySQL username
define('DB_PASS', '');           // Change to your MySQL password
define('DB_NAME', 'threadhaven_db');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("<div style='font-family:monospace;color:red;padding:20px;'>
        <strong>Database Connection Failed:</strong><br>
        " . mysqli_connect_error() . "<br><br>
        <em>Ensure XAMPP is running and the database exists.</em>
    </div>");
}

mysqli_set_charset($conn, 'utf8mb4');
