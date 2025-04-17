<?php
session_start();
require_once 'config/database.php';
require_once 'config/auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

// Check if billing ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Billing ID is required";
    header('Location: billing.php');
    exit;
}

$billing_id = $_GET['id'];

try {
    // First check if the billing record exists and belongs to the doctor
    $stmt = $pdo->prepare("SELECT id FROM billing WHERE id = :id AND doctor_id = :doctor_id");
    $stmt->execute(['id' => $billing_id, 'doctor_id' => $doctor_id]);
    
    if (!$stmt->fetch()) {
        $_SESSION['error_message'] = "Billing record not found or you don't have permission to delete it";
        header('Location: billing.php');
        exit;
    }

    // Delete the billing record
    $stmt = $pdo->prepare("DELETE FROM billing WHERE id = :id AND doctor_id = :doctor_id");
    $stmt->execute(['id' => $billing_id, 'doctor_id' => $doctor_id]);

    $_SESSION['success_message'] = "Billing record deleted successfully";
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error deleting billing record: " . $e->getMessage();
}

header('Location: billing.php');
exit; 