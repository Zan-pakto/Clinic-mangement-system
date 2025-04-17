<?php
require_once 'config/database.php';
require_once 'auth_check.php';

// Check if prescription ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Medical Record ID is required.";
    header('Location: medical_records.php');
    exit;
}

$record_id = $_GET['id'];

// Fetch medical record details with patient and doctor information
$stmt = $pdo->prepare("
    SELECT mr.*, 
           p.first_name as patient_first_name, p.last_name as patient_last_name,
           d.first_name as doctor_first_name, d.last_name as doctor_last_name
    FROM medical_records mr
    JOIN patients p ON mr.patient_id = p.id
    JOIN doctors d ON mr.doctor_id = d.id
    WHERE mr.id = :record_id AND mr.doctor_id = :doctor_id
");
$stmt->execute([
    'record_id' => $record_id,
    'doctor_id' => $_SESSION['doctor_id']
]);
$record = $stmt->fetch();

// If record not found or doesn't belong to the logged-in doctor
if (!$record) {
    $_SESSION['error'] = "Medical record not found.";
    header('Location: medical_records.php');
    exit;
}

// Get doctor information
$doctor_id = $_SESSION['doctor_id'];
$doctor_name = $_SESSION['doctor_name'] ?? 'Doctor';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Record Details - Clinic Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        <div class="sidebar w-64 bg-white shadow-lg fixed h-full">
            <div class="p-4">
                <h1 class="text-2xl font-bold text-blue-600">ClinicMS</h1>
            </div>
            <div class="p-4 border-t border-b">
                <div class="flex items-center space-x-3">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($doctor_name) ?>&background=0D8ABC&color=fff" alt="Profile" class="w-10 h-10 rounded-full">
                    <div>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($doctor_name) ?></p>
                        <p class="text-sm text-gray-500">Doctor</p>
                    </div>
                </div>
            </div>
            <nav class="mt-4">
                <a href="dashboard.php" class="menu-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50">
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
                <a href="medical_records.php" class="menu-item flex items-center px-4 py-3 text-gray-700 bg-blue-50">
                    <i class="fas fa-file-medical w-6"></i>
                    <span>Medical Records</span>
                </a>
                <a href="lab_results.php" class="menu-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50">
                    <i class="fas fa-flask w-6"></i>
                    <span>Lab Results</span>
                </a>
                <a href="billing.php" class="menu-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50">
                    <i class="fas fa-file-invoice-dollar w-6"></i>
                    <span>Billing</span>
                </a>
                <a href="profile.php" class="menu-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50">
                    <i class="fas fa-user-cog w-6"></i>
                    <span>Profile</span>
                </a>
                <a href="logout.php" class="menu-item flex items-center px-4 py-3 text-red-600 hover:bg-red-50">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 ml-64">
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center">
                        <a href="medical_records.php" class="text-gray-600 hover:text-gray-900 mr-4">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h2 class="text-xl font-semibold text-gray-800">Medical Record Details</h2>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button onclick="editRecord(<?php echo $record['id']; ?>)" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-edit mr-2"></i>Edit Record
                        </button>
                        <button onclick="deleteRecord(<?php echo $record['id']; ?>)" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-trash mr-2"></i>Delete Record
                        </button>
                    </div>
                </div>
            </header>

            <main class="p-6">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <!-- Patient Information -->
                    <div class="px-6 py-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Patient Information</h3>
                        <div class="mt-2 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Name</p>
                                <p class="text-base font-medium text-gray-900">
                                    <?php echo htmlspecialchars($record['patient_first_name'] . ' ' . $record['patient_last_name']); ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Recorded By</p>
                                <p class="text-base font-medium text-gray-900">
                                    Dr. <?php echo htmlspecialchars($record['doctor_first_name'] . ' ' . $record['doctor_last_name']); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Details -->
                    <div class="px-6 py-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Medical Details</h3>
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Diagnosis</p>
                                <p class="text-base font-medium text-gray-900"><?php echo htmlspecialchars($record['diagnosis']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Treatment</p>
                                <p class="text-base font-medium text-gray-900"><?php echo htmlspecialchars($record['treatment']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="px-6 py-4">
                        <h3 class="text-lg font-medium text-gray-900">Additional Information</h3>
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Record Date</p>
                                <p class="text-base font-medium text-gray-900">
                                    <?php echo date('F j, Y', strtotime($record['record_date'])); ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Follow-up Date</p>
                                <p class="text-base font-medium text-gray-900">
                                    <?php echo $record['follow_up_date'] ? date('F j, Y', strtotime($record['follow_up_date'])) : 'Not scheduled'; ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php
                                    switch($record['status']) {
                                        case 'Active':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'Completed':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'Archived':
                                            echo 'bg-gray-100 text-gray-800';
                                            break;
                                    }
                                    ?>">
                                    <?php echo htmlspecialchars($record['status']); ?>
                                </span>
                            </div>
                        </div>
                        <?php if (!empty($record['notes'])): ?>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">Notes</p>
                            <p class="mt-1 text-base text-gray-900"><?php echo nl2br(htmlspecialchars($record['notes'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function editRecord(id) {
            window.location.href = 'edit_medical_record.php?id=' + id;
        }

        function deleteRecord(id) {
            if (confirm('Are you sure you want to delete this medical record? This action cannot be undone.')) {
                window.location.href = 'delete_medical_record.php?id=' + id;
            }
        }
    </script>
</body>
</html> 