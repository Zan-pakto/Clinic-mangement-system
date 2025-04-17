<?php
require_once '../config/database.php';
session_start();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']) ? true : false;
    
    // Validate input
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no errors, proceed with login
    if (empty($errors)) {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, password FROM doctors WHERE email = ?");
            $stmt->execute([$email]);
            $doctor = $stmt->fetch();
            
            if ($doctor && password_verify($password, $doctor['password'])) {
                // Set session variables
                $_SESSION['doctor_id'] = $doctor['id'];
                $_SESSION['doctor_name'] = $doctor['first_name'] . ' ' . $doctor['last_name'];
                
                // Set remember me cookie if requested
                if ($remember_me) {
                    // Generate a secure token
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Store token in database
                    $stmt = $pdo->prepare("UPDATE doctors SET remember_token = ?, token_expires = ? WHERE id = ?");
                    $stmt->execute([$token, date('Y-m-d H:i:s', $expires), $doctor['id']]);
                    
                    // Set cookie
                    setcookie('remember_token', $token, $expires, '/', '', true, true);
                }
                
                // Redirect to dashboard
                header('Location: ../dashboard.php');
                exit;
            } else {
                $errors[] = "Invalid email or password";
            }
        } catch (PDOException $e) {
            $errors[] = "Login failed: " . $e->getMessage();
        }
    }
    
    // If there were errors, redirect back to login with errors
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        $_SESSION['login_email'] = $email; // Preserve email
        header('Location: ../login.php');
        exit;
    }
} else {
    // If not a POST request, redirect to login page
    header('Location: ../login.php');
    exit;
} 