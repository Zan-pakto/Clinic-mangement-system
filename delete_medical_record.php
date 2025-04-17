<?php
require_once 'config/database.php';
require_once 'auth_check.php';

// Check if record ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Medical Record ID is required.";
    header('Location: medical_records.php');
    exit;
}

$record_id = $_GET['id'];

try {
    // First verify that the record belongs to the logged-in doctor
    $stmt = $pdo->prepare("SELECT id FROM medical_records WHERE id = :record_id AND doctor_id = :doctor_id");
    $stmt->execute([
        'record_id' => $record_id,
        'doctor_id' => $_SESSION['doctor_id']
    ]);
    
    if (!$stmt->fetch()) {
        $_SESSION['error'] = "Medical record not found or you don't have permission to delete it.";
        header('Location: medical_records.php');
        exit;
    }

    // Delete the medical record
    $stmt = $pdo->prepare("DELETE FROM medical_records WHERE id = :record_id AND doctor_id = :doctor_id");
    $stmt->execute([
        'record_id' => $record_id,
        'doctor_id' => $_SESSION['doctor_id']
    ]);

    $_SESSION['success'] = "Medical record has been successfully deleted.";
} catch (PDOException $e) {
    $_SESSION['error'] = "An error occurred while deleting the medical record. Please try again.";
    // Log the error for debugging
    error_log("Error deleting medical record: " . $e->getMessage());
}

header('Location: medical_records.php');
exit; 