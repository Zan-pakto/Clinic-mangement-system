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

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['patient_id', 'amount', 'payment_method'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields.");
            }
        }

        // Validate amount is a positive number
        if (!is_numeric($_POST['amount']) || $_POST['amount'] <= 0) {
            throw new Exception("Amount must be a positive number.");
        }

        // Prepare the SQL statement
        $stmt = $pdo->prepare("
            INSERT INTO billing (
                patient_id, 
                doctor_id, 
                amount, 
                payment_method, 
                payment_status,
                notes
            ) VALUES (
                :patient_id,
                :doctor_id,
                :amount,
                :payment_method,
                'Pending',
                :notes
            )
        ");

        // Execute the statement
        $stmt->execute([
            'patient_id' => $_POST['patient_id'],
            'doctor_id' => $doctor_id,
            'amount' => $_POST['amount'],
            'payment_method' => $_POST['payment_method'],
            'notes' => $_POST['notes'] ?? null
        ]);

        // Redirect back to billing page with success message
        $_SESSION['success_message'] = "Billing record added successfully.";
        header('Location: billing.php');
        exit;

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: billing.php');
        exit;
    }
} else {
    // If not POST request, redirect to billing page
    header('Location: billing.php');
    exit;
} 