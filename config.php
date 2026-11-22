<?php
// Database connection (XAMPP defaults)
$conn = new mysqli("localhost", "root", "", "hospital_db");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
