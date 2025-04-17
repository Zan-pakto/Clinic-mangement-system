<?php
require_once 'config/database.php';
require_once 'config/auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

// Fetch doctor's name
$stmt = $pdo->prepare("SELECT first_name, last_name FROM doctors WHERE id = ?");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch();
$doctor_name = $doctor['first_name'] . ' ' . $doctor['last_name'];

// Get success and error messages from session
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Fetch appointments
try {
    $stmt = $pdo->prepare("
        SELECT a.*, p.first_name as patient_first_name, p.last_name as patient_last_name 
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        WHERE a.doctor_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $stmt->execute([$doctor_id]);
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = 'An error occurred while fetching appointments.';
    $appointments = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Clinic Management System</title>
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
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Appointments</h1>
                <div class="flex items-center space-x-2">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($doctor_name) ?>&background=0D8ABC&color=fff" alt="Profile" class="w-8 h-8 rounded-full">
                    <span class="text-gray-700">Dr. <?= htmlspecialchars($doctor_name) ?></span>
                </div>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <div class="flex items-center">
                        <div class="py-1">
                            <svg class="h-6 w-6 text-green-500 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold">Success</p>
                            <p><?= htmlspecialchars($success_message) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

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

            <?php if (empty($appointments)): ?>
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">No Appointments</h3>
                    <p class="mt-1 text-gray-500">There are no appointments scheduled at this time.</p>
                    <div class="mt-6">
                        <a href="add_appointment.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-plus mr-2"></i> Add New Appointment
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">Patient</th>
                                <th class="py-3 px-6 text-left">Date</th>
                                <th class="py-3 px-6 text-left">Time</th>
                                <th class="py-3 px-6 text-left">Reason</th>
                                <th class="py-3 px-6 text-left">Status</th>
                                <th class="py-3 px-6 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <?php foreach ($appointments as $appointment): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-3 px-6">
                                        <?= htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']) ?>
                                    </td>
                                    <td class="py-3 px-6">
                                        <?= isset($appointment['appointment_date']) ? date('M d, Y', strtotime($appointment['appointment_date'])) : 'N/A' ?>
                                    </td>
                                    <td class="py-3 px-6">
                                        <?= isset($appointment['appointment_time']) ? date('h:i A', strtotime($appointment['appointment_time'])) : 'N/A' ?>
                                    </td>
                                    <td class="py-3 px-6">
                                        <?= htmlspecialchars($appointment['reason'] ?? 'N/A') ?>
                                    </td>
                                    <td class="py-3 px-6">
                                        <span class="px-2 py-1 rounded-full text-xs 
                                            <?php
                                            switch($appointment['status'] ?? '') {
                                                case 'Completed':
                                                    echo 'bg-green-200 text-green-800';
                                                    break;
                                                case 'Scheduled':
                                                    echo 'bg-blue-200 text-blue-800';
                                                    break;
                                                case 'Cancelled':
                                                    echo 'bg-red-200 text-red-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-200 text-gray-800';
                                            }
                                            ?>">
                                            <?= htmlspecialchars($appointment['status'] ?? 'Unknown') ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <a href="edit_appointment.php?id=<?= $appointment['id'] ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_appointment.php?id=<?= $appointment['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this appointment? This action cannot be undone.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <a href="add_appointment.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-plus mr-2"></i> Add New Appointment
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html> 