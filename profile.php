<?php
require_once 'config/database.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['doctor_id'])) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

// Fetch doctor information
$stmt = $pdo->prepare("
    SELECT 
        d.id,
        d.first_name,
        d.last_name,
        d.email,
        d.phone,
        d.specialization,
        d.created_at
    FROM doctors d
    WHERE d.id = :doctor_id
");
$stmt->execute(['doctor_id' => $doctor_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

// If doctor not found, redirect to login
if (!$doctor) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Fetch recent appointments
$stmt = $pdo->prepare("
    SELECT 
        a.id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        p.first_name as patient_first_name,
        p.last_name as patient_last_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    WHERE a.doctor_id = :doctor_id
    ORDER BY a.appointment_date DESC
    LIMIT 5
");
$stmt->execute(['doctor_id' => $doctor_id]);
$recent_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent prescriptions for the doctor
$stmt = $pdo->prepare("
    SELECT pr.*, p.first_name as patient_first_name, p.last_name as patient_last_name
    FROM prescriptions pr
    JOIN patients p ON pr.patient_id = p.id
    WHERE pr.doctor_id = :doctor_id
    ORDER BY pr.created_at DESC
    LIMIT 5
");
$stmt->execute(['doctor_id' => $doctor_id]);
$recentPrescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch doctor statistics
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count
    FROM appointments
    WHERE doctor_id = :doctor_id
");
$stmt->execute(['doctor_id' => $doctor_id]);
$appointmentCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT patient_id) as count
    FROM appointments
    WHERE doctor_id = :doctor_id
");
$stmt->execute(['doctor_id' => $doctor_id]);
$patientCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->prepare("
    SELECT COUNT(*) as count
    FROM prescriptions
    WHERE doctor_id = :doctor_id
");
$stmt->execute(['doctor_id' => $doctor_id]);
$prescriptionCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Clinic Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <style>
        .sidebar {
            transition: all 0.3s ease;
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
        <div class="sidebar w-64 bg-white shadow-lg">
            <div class="p-4">
                <h1 class="text-2xl font-bold text-blue-600">ClinicMS</h1>
            </div>
            <nav class="mt-4">
                <a href="index.php" class="menu-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50">
                    <i class="fas fa-home w-6"></i>
                    <span>Dashboard</span>
                </a>
                <a href="appointments.php" class="menu-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50">
                    <i class="fas fa-calendar-check w-6"></i>
                    <span>Appointments</span>
                </a>
                <a href="patients.php" class="menu-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50">
                    <i class="fas fa-user-injured w-6"></i>
                    <span>Patients</span>
                </a>
                <a href="prescriptions.php" class="menu-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50">
                    <i class="fas fa-prescription w-6"></i>
                    <span>Prescriptions</span>
                </a>
                <a href="lab_results.php" class="menu-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50">
                    <i class="fas fa-flask w-6"></i>
                    <span>Lab Results</span>
                </a>
                <a href="billing.php" class="menu-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50">
                    <i class="fas fa-file-invoice-dollar w-6"></i>
                    <span>Billing</span>
                </a>
                <a href="profile.php" class="menu-item flex items-center px-4 py-3 text-gray-700 bg-blue-50">
                    <i class="fas fa-user w-6"></i>
                    <span>Profile</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Profile</h2>
                    <div class="flex items-center space-x-2">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($doctor['first_name'] . '+' . $doctor['last_name']) ?>&background=0D8ABC&color=fff" alt="Profile" class="w-8 h-8 rounded-full">
                        <span class="text-gray-700">Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?></span>
                    </div>
                </div>
            </header>

            <main class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Profile Information -->
                    <div class="md:col-span-1">
                        <div class="bg-white rounded-lg shadow-sm p-6" data-aos="fade-up">
                            <div class="flex flex-col items-center">
                                <div class="w-32 h-32 rounded-full overflow-hidden mb-4">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($doctor['first_name'] . '+' . $doctor['last_name']) ?>&background=0D8ABC&color=fff&size=128" alt="Profile" class="w-full h-full object-cover">
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800">Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?></h3>
                                <p class="text-gray-600"><?= htmlspecialchars($doctor['specialization']) ?></p>
                                <div class="mt-4 flex space-x-2">
                                    <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" onclick="openEditProfileModal()">
                                        <i class="fas fa-edit mr-2"></i>Edit Profile
                                    </button>
                                </div>
                            </div>
                            <div class="mt-6 border-t pt-4">
                                <h4 class="text-lg font-medium text-gray-800 mb-4">Contact Information</h4>
                                <div class="space-y-3">
                                    <div class="flex items-start">
                                        <i class="fas fa-envelope text-gray-500 mt-1 mr-3"></i>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Email</p>
                                            <p class="text-gray-800"><?= htmlspecialchars($doctor['email']) ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-phone text-gray-500 mt-1 mr-3"></i>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Phone</p>
                                            <p class="text-gray-800"><?= htmlspecialchars($doctor['phone']) ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <i class="fas fa-calendar-alt text-gray-500 mt-1 mr-3"></i>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Member Since</p>
                                            <p class="text-gray-800"><?= date('F d, Y', strtotime($doctor['created_at'])) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity and Stats -->
                    <div class="md:col-span-2">
                        <!-- Stats Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-white rounded-lg shadow-sm p-4" data-aos="fade-up">
                                <div class="flex items-center">
                                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                        <i class="fas fa-calendar-check text-xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-gray-500 text-sm">Appointments</p>
                                        <p class="text-xl font-semibold text-gray-800"><?= $appointmentCount ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white rounded-lg shadow-sm p-4" data-aos="fade-up" data-aos-delay="100">
                                <div class="flex items-center">
                                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                                        <i class="fas fa-user-injured text-xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-gray-500 text-sm">Patients</p>
                                        <p class="text-xl font-semibold text-gray-800"><?= $patientCount ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white rounded-lg shadow-sm p-4" data-aos="fade-up" data-aos-delay="200">
                                <div class="flex items-center">
                                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                        <i class="fas fa-prescription text-xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-gray-500 text-sm">Prescriptions</p>
                                        <p class="text-xl font-semibold text-gray-800"><?= $prescriptionCount ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="bg-white rounded-lg shadow-sm p-6 mb-6" data-aos="fade-up">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Appointments</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($recent_appointments as $appointment): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name=<?= urlencode($appointment['patient_first_name'] . '+' . $appointment['patient_last_name']) ?>&background=0D8ABC&color=fff" alt="">
                                                    </div>
                                                    <div class="ml-3">
                                                        <p class="text-sm font-medium text-gray-900">
                                                            <?= htmlspecialchars($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']) ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <p class="text-sm text-gray-900"><?= date('M d, Y', strtotime($appointment['appointment_date'])) ?></p>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <p class="text-sm text-gray-900"><?= date('h:i A', strtotime($appointment['appointment_time'])) ?></p>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php
                                                    switch($appointment['status']) {
                                                        case 'Scheduled':
                                                            echo 'bg-yellow-100 text-yellow-800';
                                                            break;
                                                        case 'Confirmed':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'Completed':
                                                            echo 'bg-blue-100 text-blue-800';
                                                            break;
                                                        case 'Cancelled':
                                                            echo 'bg-red-100 text-red-800';
                                                            break;
                                                    }
                                                    ?>">
                                                    <?= htmlspecialchars($appointment['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 text-right">
                                <a href="appointments.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View All <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Recent Prescriptions -->
                        <div class="bg-white rounded-lg shadow-sm p-6" data-aos="fade-up">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Prescriptions</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($recentPrescriptions as $prescription): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name=<?= urlencode($prescription['patient_first_name'] . '+' . $prescription['patient_last_name']) ?>&background=0D8ABC&color=fff" alt="">
                                                    </div>
                                                    <div class="ml-3">
                                                        <p class="text-sm font-medium text-gray-900">
                                                            <?= htmlspecialchars($prescription['patient_first_name'] . ' ' . $prescription['patient_last_name']) ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <p class="text-sm text-gray-900"><?= date('M d, Y', strtotime($prescription['prescription_date'])) ?></p>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php
                                                    switch($prescription['status']) {
                                                        case 'Active':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'Completed':
                                                            echo 'bg-blue-100 text-blue-800';
                                                            break;
                                                        case 'Cancelled':
                                                            echo 'bg-red-100 text-red-800';
                                                            break;
                                                    }
                                                    ?>">
                                                    <?= htmlspecialchars($prescription['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 text-right">
                                <a href="prescriptions.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View All <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Profile</h3>
                <form id="editProfileForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" value="<?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" value="<?= htmlspecialchars($doctor['email']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="tel" value="<?= htmlspecialchars($doctor['phone']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Specialization</label>
                        <input type="text" value="<?= htmlspecialchars($doctor['specialization']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300" onclick="closeEditProfileModal()">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        AOS.init({
            duration: 800,
            once: true
        });

        function openEditProfileModal() {
            document.getElementById('editProfileModal').classList.remove('hidden');
        }

        function closeEditProfileModal() {
            document.getElementById('editProfileModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editProfileModal');
            if (event.target == modal) {
                closeEditProfileModal();
            }
        }
    </script>
</body>
</html> 