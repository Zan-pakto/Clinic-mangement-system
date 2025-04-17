<?php
require_once 'config/database.php';
require_once 'config/auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['doctor_id'])) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit;
}

// Check if patient ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Patient ID is required.";
    header('Location: patients.php');
    exit;
}

$patient_id = $_GET['id'];

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Delete related prescriptions
    $stmt = $pdo->prepare("DELETE FROM prescriptions WHERE patient_id = :patient_id");
    $stmt->execute(['patient_id' => $patient_id]);
    
    // Delete related lab results
    $stmt = $pdo->prepare("DELETE FROM lab_results WHERE patient_id = :patient_id");
    $stmt->execute(['patient_id' => $patient_id]);
    
    // Delete related appointments
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE patient_id = :patient_id");
    $stmt->execute(['patient_id' => $patient_id]);
    
    // Delete related billing records
    $stmt = $pdo->prepare("DELETE FROM billing WHERE patient_id = :patient_id");
    $stmt->execute(['patient_id' => $patient_id]);
    
    // Finally, delete the patient
    $stmt = $pdo->prepare("DELETE FROM patients WHERE id = :patient_id");
    $stmt->execute(['patient_id' => $patient_id]);
    
    // Commit transaction
    $pdo->commit();
    
    $_SESSION['success'] = "Patient and all related records have been successfully deleted.";
} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = "Error deleting patient: " . $e->getMessage();
}

// Redirect back to patients page
header('Location: patients.php');
exit; 