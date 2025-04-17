<?php
// Database configuration
$host = 'localhost';
$dbname = 'clinic';
$username = 'root';
$password = '';

try {
    // Create connection without database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop existing tables if they exist (in correct order)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS prescriptions");
    $pdo->exec("DROP TABLE IF EXISTS lab_results");
    $pdo->exec("DROP TABLE IF EXISTS billing");
    $pdo->exec("DROP TABLE IF EXISTS appointments");
    $pdo->exec("DROP TABLE IF EXISTS patients");
    $pdo->exec("DROP TABLE IF EXISTS doctors");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Create doctors table
    $pdo->exec("CREATE TABLE doctors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        clinic_name VARCHAR(255),
        clinic_type ENUM('general', 'specialist', 'dental', 'pediatric', 'other'),
        phone VARCHAR(20),
        specialization VARCHAR(100),
        remember_token VARCHAR(100) NULL,
        token_expires DATETIME NULL,
        last_login DATETIME NULL,
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Create patients table
    $pdo->exec("CREATE TABLE patients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        phone VARCHAR(20),
        date_of_birth DATE,
        gender ENUM('male', 'female', 'other'),
        address TEXT,
        medical_history TEXT,
        allergies TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Create appointments table
    $pdo->exec("CREATE TABLE appointments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        doctor_id INT NOT NULL,
        patient_id INT NOT NULL,
        appointment_date DATE NOT NULL,
        appointment_time TIME NOT NULL,
        status ENUM('scheduled', 'completed', 'cancelled', 'no-show') DEFAULT 'scheduled',
        reason TEXT,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
        FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
    )");
    
    // Create prescriptions table
    $pdo->exec("CREATE TABLE prescriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        doctor_id INT NOT NULL,
        patient_id INT NOT NULL,
        prescription_date DATE NOT NULL,
        medication TEXT NOT NULL,
        dosage VARCHAR(100),
        frequency VARCHAR(100),
        duration VARCHAR(100),
        instructions TEXT,
        status ENUM('Active', 'Completed', 'Cancelled') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
        FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
    )");

    // Create lab_results table
    $pdo->exec("CREATE TABLE lab_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        doctor_id INT NOT NULL,
        patient_id INT NOT NULL,
        test_name VARCHAR(100) NOT NULL,
        test_date DATE NOT NULL,
        results TEXT,
        notes TEXT,
        status ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
        FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
    )");

    // Insert sample doctor (password: admin123)
    $pdo->exec("INSERT INTO doctors (first_name, last_name, email, password, specialization) VALUES 
        ('John', 'Smith', 'admin@clinic.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'General Medicine')");

    // Insert sample patients
    $pdo->exec("INSERT INTO patients (first_name, last_name, email, phone, date_of_birth, gender, address) VALUES 
        ('Jane', 'Doe', 'jane.doe@example.com', '123-456-7890', '1990-01-15', 'female', '123 Main St'),
        ('Bob', 'Johnson', 'bob.johnson@example.com', '234-567-8901', '1985-03-20', 'male', '456 Oak Ave'),
        ('Alice', 'Smith', 'alice.smith@example.com', '345-678-9012', '1995-07-10', 'female', '789 Pine Rd')");
    
    // Create indexes
    $pdo->exec("CREATE INDEX idx_doctors_email ON doctors(email)");
    $pdo->exec("CREATE INDEX idx_doctors_clinic_name ON doctors(clinic_name)");
    $pdo->exec("CREATE INDEX idx_doctors_status ON doctors(status)");
    $pdo->exec("CREATE INDEX idx_patients_email ON patients(email)");
    $pdo->exec("CREATE INDEX idx_appointments_date ON appointments(appointment_date)");
    $pdo->exec("CREATE INDEX idx_appointments_status ON appointments(status)");
    
    echo "Database setup completed successfully!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 