# Clinic Management System

A comprehensive web-based clinic management system built with PHP and MySQL, designed to help medical practitioners manage their practice efficiently.

## Features

- **User Authentication**
  - Doctor registration and login
  - Secure password hashing
  - Session management

- **Patient Management**
  - Add, edit, and view patient records
  - Store patient medical history
  - Track patient status (Active/Inactive)

- **Appointment Management**
  - Schedule appointments
  - Track appointment status (Scheduled, Completed, Cancelled)
  - View appointment history

- **Medical Records**
  - Create and maintain patient medical records
  - Track diagnoses and treatments
  - Store follow-up dates

- **Lab Results**
  - Record and manage lab test results
  - Track test status (Pending, Completed, Cancelled)
  - Add notes and observations

- **Prescriptions**
  - Create and manage prescriptions
  - Track medication details
  - Monitor prescription status

- **Billing**
  - Track payments and pending amounts
  - Generate financial reports
  - Monitor daily collections

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

## Installation

1. Clone the repository to your web server directory:
   ```bash
   git clone [repository-url]
   ```

2. Create a MySQL database named 'clinic'

3. Import the database structure:
   ```bash
   mysql -u root -p clinic < setup.php
   ```

4. Configure the database connection:
   - Open `config/database.php`
   - Update the following variables:
     ```php
     $host = 'localhost';
     $dbname = 'clinic';
     $username = 'your_username';
     $password = 'your_password';
     ```

5. Set up your web server:
   - Point your web server to the project directory
   - Ensure PHP has write permissions for session handling

## Database Structure

### Tables

1. **doctors**
   - id (Primary Key)
   - first_name
   - last_name
   - email (Unique)
   - password
   - phone
   - specialization
   - status
   - created_at
   - updated_at

2. **patients**
   - id (Primary Key)
   - first_name
   - last_name
   - email
   - phone
   - date_of_birth
   - gender (ENUM: 'M', 'F', 'Other')
   - address
   - medical_history
   - status (ENUM: 'Active', 'Inactive')
   - created_at
   - updated_at

3. **appointments**
   - id (Primary Key)
   - patient_id (Foreign Key)
   - doctor_id (Foreign Key)
   - appointment_date
   - appointment_time
   - status (ENUM: 'Scheduled', 'Completed', 'Cancelled')
   - reason
   - notes
   - created_at
   - updated_at

4. **prescriptions**
   - id (Primary Key)
   - patient_id (Foreign Key)
   - doctor_id (Foreign Key)
   - prescription_date
   - medication
   - dosage
   - frequency
   - duration
   - instructions
   - status (ENUM: 'Active', 'Completed', 'Cancelled')
   - created_at
   - updated_at

5. **lab_results**
   - id (Primary Key)
   - patient_id (Foreign Key)
   - doctor_id (Foreign Key)
   - test_name
   - test_date
   - results
   - notes
   - status (ENUM: 'Pending', 'Completed', 'Cancelled')
   - created_at
   - updated_at

6. **billing**
   - id (Primary Key)
   - patient_id (Foreign Key)
   - doctor_id (Foreign Key)
   - appointment_id (Foreign Key)
   - amount
   - payment_method
   - payment_status (ENUM: 'Pending', 'Paid', 'Cancelled')
   - payment_date
   - notes
   - created_at

## Security Features

- Password hashing using PHP's password_hash()
- PDO prepared statements to prevent SQL injection
- Session management for authentication
- Input validation and sanitization
- CSRF protection
- Secure password reset functionality

## Usage

1. **Initial Setup**
   - Run `setup.php` to create the database and tables
   - Register a new doctor account
   - Log in to access the dashboard

2. **Managing Patients**
   - Add new patients through the patients section
   - Update patient information as needed
   - View patient history and records

3. **Appointments**
   - Schedule new appointments
   - Update appointment status
   - View appointment calendar

4. **Medical Records**
   - Create and maintain patient records
   - Add diagnoses and treatments
   - Track follow-up dates

5. **Lab Results**
   - Record lab test results
   - Update test status
   - Add notes and observations

6. **Billing**
   - Track payments
   - Generate financial reports
   - Monitor daily collections

## Maintenance

- Regular database backups recommended
- Keep PHP and MySQL updated
- Monitor error logs
- Clean up old records periodically

## Support

For support and queries, please contact [your-email@example.com]

## License

This project is licensed under the MIT License - see the LICENSE file for details. 