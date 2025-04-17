<?php
require_once 'config/database.php';
require_once 'config/auth_check.php';

// Check if prescription ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Prescription ID is required.";
    header('Location: prescriptions.php');
    exit;
}

$prescription_id = $_GET['id'];

try {
    // First verify that the prescription belongs to the logged-in doctor
    $stmt = $pdo->prepare("SELECT id FROM prescriptions WHERE id = :prescription_id AND doctor_id = :doctor_id");
    $stmt->execute([
        'prescription_id' => $prescription_id,
        'doctor_id' => $_SESSION['doctor_id']
    ]);
    
    if (!$stmt->fetch()) {
        $_SESSION['error'] = "Prescription not found or you don't have permission to delete it.";
        header('Location: prescriptions.php');
        exit;
    }

    // Delete the prescription
    $stmt = $pdo->prepare("DELETE FROM prescriptions WHERE id = :prescription_id AND doctor_id = :doctor_id");
    $stmt->execute([
        'prescription_id' => $prescription_id,
        'doctor_id' => $_SESSION['doctor_id']
    ]);

    $_SESSION['success'] = "Prescription has been successfully deleted.";
} catch (PDOException $e) {
    $_SESSION['error'] = "An error occurred while deleting the prescription. Please try again.";
    // Log the error for debugging
    error_log("Error deleting prescription: " . $e->getMessage());
}

header('Location: prescriptions.php');
exit; 