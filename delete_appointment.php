<?php
require_once 'config/database.php';
require_once 'config/auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit;
}

$doctor_id = $_SESSION['doctor_id'];
$success_message = '';
$error_message = '';

// Check if appointment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'Invalid appointment ID.';
    header('Location: appointments.php');
    exit;
}

$appointment_id = $_GET['id'];

try {
    // First verify that the appointment belongs to this doctor
    $stmt = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND doctor_id = ?");
    $stmt->execute([$appointment_id, $doctor_id]);
    
    if (!$stmt->fetch()) {
        $_SESSION['error_message'] = 'Appointment not found or you do not have permission to delete it.';
        header('Location: appointments.php');
        exit;
    }

    // Delete the appointment
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ? AND doctor_id = ?");
    $stmt->execute([$appointment_id, $doctor_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = 'Appointment deleted successfully.';
    } else {
        $_SESSION['error_message'] = 'Failed to delete appointment.';
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'An error occurred while deleting the appointment.';
}

header('Location: appointments.php');
exit; 