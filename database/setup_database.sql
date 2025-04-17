-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS clinic_management;
USE clinic_management;

-- Create doctors table
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    specialization VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create patients table
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('M', 'F', 'Other'),
    address TEXT,
    medical_history TEXT,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create appointments table
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    type VARCHAR(100),
    status ENUM('Scheduled', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Create prescriptions table
CREATE TABLE IF NOT EXISTS prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    prescription_date DATE NOT NULL,
    medication TEXT NOT NULL,
    dosage VARCHAR(100),
    frequency VARCHAR(100),
    duration VARCHAR(100),
    instructions TEXT,
    status ENUM('Active', 'Completed', 'Cancelled') DEFAULT 'Active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Create lab_results table
CREATE TABLE IF NOT EXISTS lab_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    test_name VARCHAR(100) NOT NULL,
    test_date DATE NOT NULL,
    results TEXT,
    notes TEXT,
    status ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Create billing table
CREATE TABLE IF NOT EXISTS billing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status ENUM('Pending', 'Paid', 'Cancelled') DEFAULT 'Pending',
    payment_date DATE,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL
);

-- Insert default doctor account (password: admin123)
INSERT INTO doctors (first_name, last_name, email, password, phone, specialization) 
VALUES ('John', 'Smith', 'admin@clinic.com', '$2y$10$8KzQ8IzAF1QDMV0oVXqQeOQZqXqXqXqXqXqXqXqXqXqXqXqXqXqX', '123-456-7890', 'General Medicine');

-- Insert sample patients
INSERT INTO patients (first_name, last_name, email, phone, date_of_birth, gender, address, status) 
VALUES 
('Jane', 'Doe', 'jane.doe@example.com', '987-654-3210', '1985-05-15', 'F', '123 Main St, City', 'Active'),
('Robert', 'Johnson', 'robert.johnson@example.com', '555-123-4567', '1978-10-22', 'M', '456 Oak Ave, Town', 'Active'),
('Emily', 'Williams', 'emily.williams@example.com', '777-888-9999', '1992-03-30', 'F', '789 Pine Rd, Village', 'Active');

-- Insert sample appointments
INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, type, status) 
VALUES 
(1, 1, CURDATE(), '09:00:00', 'Regular Checkup', 'Confirmed'),
(2, 1, CURDATE(), '10:30:00', 'Follow-up', 'Scheduled'),
(3, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', 'Consultation', 'Scheduled');

-- Insert sample prescriptions
INSERT INTO prescriptions (patient_id, doctor_id, prescription_date, medication, dosage, frequency, duration, instructions) 
VALUES 
(1, 1, CURDATE(), 'Amoxicillin', '500mg', 'Twice daily', '7 days', 'Take with food'),
(2, 1, CURDATE(), 'Lisinopril', '10mg', 'Once daily', '30 days', 'Take in the morning');

-- Insert sample lab results
INSERT INTO lab_results (patient_id, doctor_id, test_name, test_date, results, status) 
VALUES 
(1, 1, 'Blood Test', CURDATE(), 'Normal blood count, slightly elevated cholesterol', 'Completed'),
(2, 1, 'X-Ray', CURDATE(), 'No abnormalities detected', 'Completed'),
(3, 1, 'MRI', DATE_ADD(CURDATE(), INTERVAL 2 DAY), NULL, 'Pending');

-- Insert sample billing records
INSERT INTO billing (patient_id, doctor_id, appointment_id, amount, payment_method, payment_status) 
VALUES 
(1, 1, 1, 150.00, 'Credit Card', 'Paid'),
(2, 1, 2, 200.00, 'Insurance', 'Pending'),
(3, 1, 3, 175.00, 'Cash', 'Pending'); 