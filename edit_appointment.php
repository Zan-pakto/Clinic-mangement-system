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
$appointment = null;

// Check if appointment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'Invalid appointment ID.';
    header('Location: appointments.php');
    exit;
}

$appointment_id = $_GET['id'];

// Fetch appointment details
try {
    $stmt = $pdo->prepare("
        SELECT a.*, p.first_name as patient_first_name, p.last_name as patient_last_name 
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        WHERE a.id = ? AND a.doctor_id = ?
    ");
    $stmt->execute([$appointment_id, $doctor_id]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        $_SESSION['error_message'] = 'Appointment not found or you do not have permission to edit it.';
        header('Location: appointments.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'An error occurred while fetching the appointment.';
    header('Location: appointments.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $status = $_POST['status'] ?? '';

    // Validate input
    if (empty($appointment_date) || empty($appointment_time) || empty($reason)) {
        $error_message = 'All fields are required.';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE appointments 
                SET appointment_date = ?, appointment_time = ?, reason = ?, status = ?
                WHERE id = ? AND doctor_id = ?
            ");
            $stmt->execute([$appointment_date, $appointment_time, $reason, $status, $appointment_id, $doctor_id]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['success_message'] = 'Appointment updated successfully.';
                header('Location: appointments.php');
                exit;
            } else {
                $error_message = 'No changes were made to the appointment.';
            }
        } catch (PDOException $e) {
            $error_message = 'An error occurred while updating the appointment.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment - Clinic Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 16rem;
            height: 100vh;
            background-color: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            z-index: 10;
        }
        .main-content {
            margin-left: 16rem;
            min-height: 100vh;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="flex items-center justify-center h-16 px-4 border-b">
                <h1 class="text-xl font-bold text-blue-600">ClinicMS</h1>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="dashboard.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-home w-5 h-5 mr-3"></i>
                    Dashboard
                </a>
                <a href="appointments.php" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg">
                    <i class="fas fa-calendar-check w-5 h-5 mr-3"></i>
                    Appointments
                </a>
                <a href="patients.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-user-injured w-5 h-5 mr-3"></i>
                    Patients
                </a>
                <a href="prescriptions.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-prescription w-5 h-5 mr-3"></i>
                    Prescriptions
                </a>
                <a href="lab_results.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-flask w-5 h-5 mr-3"></i>
                    Lab Results
                </a>
                <a href="billing.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-file-invoice-dollar w-5 h-5 mr-3"></i>
                    Billing
                </a>
            </nav>
            
            <!-- Profile -->
            <div class="p-4 border-t">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <img class="w-10 h-10 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($doctor_name); ?>&background=0D8ABC&color=fff" alt="Profile">
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($doctor_name); ?></p>
                        <a href="profile.php" class="text-xs text-blue-600 hover:text-blue-800">View Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content p-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Edit Appointment</h1>
                    <a href="appointments.php" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Appointments
                    </a>
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <div class="flex items-center">
                            <div class="py-1">
                                <svg class="h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold">Error</p>
                                <p><?= htmlspecialchars($error_message) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Patient</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <?= htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']) ?>
                        </p>
                    </div>

                    <div>
                        <label for="appointment_date" class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="appointment_date" id="appointment_date" 
                               value="<?= htmlspecialchars($appointment['appointment_date']) ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="appointment_time" class="block text-sm font-medium text-gray-700">Time</label>
                        <input type="time" name="appointment_time" id="appointment_time" 
                               value="<?= htmlspecialchars($appointment['appointment_time']) ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
                        <textarea name="reason" id="reason" rows="3" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($appointment['reason']) ?></textarea>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="Scheduled" <?= $appointment['status'] === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                            <option value="In Progress" <?= $appointment['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="Completed" <?= $appointment['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="Cancelled" <?= $appointment['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="appointments.php" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html> 