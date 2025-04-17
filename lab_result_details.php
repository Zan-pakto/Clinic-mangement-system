<?php
require_once 'config/database.php';
require_once 'config/auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit;
}

$doctor_id = $_SESSION['doctor_id'];
$doctor_name = $_SESSION['doctor_name'] ?? 'Doctor';

// Check if lab result ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Lab Result ID is required.";
    header('Location: lab_results.php');
    exit;
}

$lab_result_id = $_GET['id'];

// Fetch lab result details with patient information
$stmt = $pdo->prepare("
    SELECT lr.*, p.first_name as patient_first_name, p.last_name as patient_last_name, 
           p.date_of_birth, p.gender, p.medical_history
    FROM lab_results lr
    JOIN patients p ON lr.patient_id = p.id
    WHERE lr.id = :lab_result_id AND lr.doctor_id = :doctor_id
");
$stmt->execute([
    'lab_result_id' => $lab_result_id,
    'doctor_id' => $doctor_id
]);
$lab_result = $stmt->fetch();

// If lab result not found or doesn't belong to the logged-in doctor
if (!$lab_result) {
    $_SESSION['error'] = "Lab result not found or you don't have permission to view it.";
    header('Location: lab_results.php');
    exit;
}

// Get success/error messages
$success_message = $_SESSION['success'] ?? '';
$error_message = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Result Details - Clinic Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
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
        <div class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg">
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
                    <a href="lab_results.php" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg">
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
                    <div class="flex items-center">
                        <a href="lab_results.php" class="text-gray-500 hover:text-gray-700 mr-4">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h2 class="text-xl font-semibold text-gray-800">Lab Result Details</h2>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="edit_lab_result.php?id=<?= $lab_result_id ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </a>
                        <a href="delete_lab_result.php?id=<?= $lab_result_id ?>" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors" onclick="return confirm('Are you sure you want to delete this lab result?')">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </a>
                    </div>
                </div>
            </header>

            <main class="p-6">
                <?php if ($success_message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p><?= htmlspecialchars($success_message) ?></p>
                </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p><?= htmlspecialchars($error_message) ?></p>
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Patient Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Patient Information</h3>
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0 h-16 w-16">
                                    <img class="h-16 w-16 rounded-full" src="https://ui-avatars.com/api/?name=<?= urlencode($lab_result['patient_first_name'] . '+' . $lab_result['patient_last_name']) ?>&background=0D8ABC&color=fff" alt="">
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-xl font-semibold text-gray-900">
                                        <?= htmlspecialchars($lab_result['patient_first_name'] . ' ' . $lab_result['patient_last_name']) ?>
                                    </h4>
                                    <p class="text-gray-500">
                                        <?= $lab_result['gender'] === 'male' ? 'Male' : ($lab_result['gender'] === 'female' ? 'Female' : 'Other') ?>
                                        <?= $lab_result['date_of_birth'] ? ' • ' . date('F j, Y', strtotime($lab_result['date_of_birth'])) : '' ?>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if ($lab_result['medical_history']): ?>
                            <div class="mt-4">
                                <h5 class="text-sm font-medium text-gray-700">Medical History</h5>
                                <p class="mt-1 text-gray-600"><?= nl2br(htmlspecialchars($lab_result['medical_history'])) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Lab Test Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Lab Test Information</h3>
                            <dl class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Test Name</dt>
                                    <dd class="mt-1 text-lg text-gray-900"><?= htmlspecialchars($lab_result['test_name']) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Test Date</dt>
                                    <dd class="mt-1 text-lg text-gray-900"><?= date('F j, Y', strtotime($lab_result['test_date'])) ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php
                                            switch($lab_result['status']) {
                                                case 'Completed':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'Pending':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'Cancelled':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                            }
                                            ?>">
                                            <?= htmlspecialchars($lab_result['status']) ?>
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
                
                <!-- Test Results -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Test Results</h3>
                    <?php if ($lab_result['results']): ?>
                        <div class="bg-gray-50 p-4 rounded-md">
                            <pre class="text-gray-700 whitespace-pre-wrap"><?= htmlspecialchars($lab_result['results']) ?></pre>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 italic">No results available yet.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Notes -->
                <?php if ($lab_result['notes']): ?>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <p class="text-gray-700"><?= nl2br(htmlspecialchars($lab_result['notes'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>
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