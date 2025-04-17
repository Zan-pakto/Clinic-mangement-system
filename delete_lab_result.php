<?php
require_once 'config/database.php';
require_once 'config/auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

// Check if lab result ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Lab Result ID is required.";
    header('Location: lab_results.php');
    exit;
}

$lab_result_id = $_GET['id'];

try {
    // Check if lab result exists and belongs to the logged-in doctor
    $stmt = $pdo->prepare("
        SELECT id FROM lab_results 
        WHERE id = :lab_result_id AND doctor_id = :doctor_id
    ");
    $stmt->execute([
        'lab_result_id' => $lab_result_id,
        'doctor_id' => $doctor_id
    ]);
    
    if (!$stmt->fetch()) {
        throw new Exception("Lab result not found or you don't have permission to delete it.");
    }

    // Delete the lab result
    $stmt = $pdo->prepare("
        DELETE FROM lab_results 
        WHERE id = :lab_result_id AND doctor_id = :doctor_id
    ");
    $stmt->execute([
        'lab_result_id' => $lab_result_id,
        'doctor_id' => $doctor_id
    ]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = "Lab result deleted successfully.";
    } else {
        throw new Exception("Failed to delete lab result.");
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: lab_results.php');
exit; 