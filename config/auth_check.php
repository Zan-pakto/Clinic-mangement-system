<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is not logged in
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit;
} 