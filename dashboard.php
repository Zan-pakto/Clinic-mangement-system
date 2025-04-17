<?php
require_once 'config/database.php';
require_once 'config/auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['doctor_id'])) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

// Fetch doctor name
$stmt = $pdo->prepare("SELECT first_name, last_name FROM doctors WHERE id = :doctor_id");
$stmt->execute(['doctor_id' => $doctor_id]);
$doctor = $stmt->fetch();

// If doctor not found, destroy session and redirect to login
if (!$doctor) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$doctor_name = $doctor['first_name'] . ' ' . $doctor['last_name'];

// Get today's appointments count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count 
    FROM appointments 
    WHERE doctor_id = ? AND appointment_date = CURDATE()
");
$stmt->execute([$doctor_id]);
$todayAppointments = $stmt->fetch()['count'];

// Get total patients count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count 
    FROM patients
");
$stmt->execute();
$totalPatients = $stmt->fetch()['count'];

// Get pending prescriptions count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count 
    FROM prescriptions 
    WHERE doctor_id = ? AND status = 'pending'
");
$stmt->execute([$doctor_id]);
$pendingPrescriptions = $stmt->fetch()['count'];

// Get lab results count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count 
    FROM lab_results 
    WHERE doctor_id = ?
");
$stmt->execute([$doctor_id]);
$labResults = $stmt->fetch()['count'];

// Get recent appointments
$stmt = $pdo->prepare("
    SELECT a.*, p.first_name, p.last_name, p.id as patient_id,
           COALESCE(a.reason, 'Regular Checkup') as reason
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    WHERE a.doctor_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT 5
");
$stmt->execute([$doctor_id]);
$recentAppointments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Clinic Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .sidebar {
            transition: all 0.2s ease;
        }
        .menu-item {
            transition: all 0.2s ease;
        }
        .menu-item:hover {
            transform: translateX(5px);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-center h-16 px-4 border-b">
                    <h1 class="text-xl font-bold text-blue-600">ClinicMS</h1>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg">
                        <i class="fas fa-home w-5 h-5 mr-3"></i>
                        Dashboard
                    </a>
                    <a href="appointments.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
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
        <div class="ml-64 flex-1 overflow-auto">
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
                    <div class="flex items-center space-x-2">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($doctor_name) ?>&background=0D8ABC&color=fff" alt="Profile" class="w-8 h-8 rounded-full">
                        <span class="text-gray-700">Dr. <?= htmlspecialchars($doctor_name) ?></span>
                        <a href="logout.php" class="text-gray-600 hover:text-gray-800 ml-2" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </header>

            <main class="p-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-6" data-aos="fade-up">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-calendar-check text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Today's Appointments</h3>
                                <p class="text-2xl font-semibold text-gray-800"><?php echo $todayAppointments; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-user-injured text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Total Patients</h3>
                                <p class="text-2xl font-semibold text-gray-800"><?php echo $totalPatients; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <i class="fas fa-prescription text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Pending Prescriptions</h3>
                                <p class="text-2xl font-semibold text-gray-800"><?php echo $pendingPrescriptions; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 text-red-600">
                                <i class="fas fa-flask text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Lab Results</h3>
                                <p class="text-2xl font-semibold text-gray-800"><?php echo $labResults; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Appointments -->
                <div class="bg-white rounded-lg shadow-sm p-6" data-aos="fade-up">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Appointments</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recentAppointments as $appointment): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($appointment['first_name'] . ' ' . $appointment['last_name']); ?>&background=0D8ABC&color=fff" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo $appointment['first_name'] . ' ' . $appointment['last_name']; ?></div>
                                                <div class="text-sm text-gray-500">ID: #<?php echo $appointment['patient_id']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo date('l, F j', strtotime($appointment['appointment_date'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <?php echo $appointment['reason']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <?php echo $appointment['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
</body>
</html> 