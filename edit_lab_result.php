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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['test_name', 'test_date', 'status'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("$field is required.");
            }
        }

        // Update lab result
        $stmt = $pdo->prepare("
            UPDATE lab_results 
            SET test_name = :test_name,
                test_date = :test_date,
                results = :results,
                notes = :notes,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND doctor_id = :doctor_id
        ");

        $stmt->execute([
            'test_name' => $_POST['test_name'],
            'test_date' => $_POST['test_date'],
            'results' => $_POST['results'] ?? null,
            'notes' => $_POST['notes'] ?? null,
            'status' => $_POST['status'],
            'id' => $lab_result_id,
            'doctor_id' => $doctor_id
        ]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "Lab result updated successfully.";
            header("Location: lab_result_details.php?id=$lab_result_id");
            exit;
        } else {
            throw new Exception("No changes were made or you don't have permission to edit this lab result.");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Fetch lab result details
$stmt = $pdo->prepare("
    SELECT lr.*, p.first_name as patient_first_name, p.last_name as patient_last_name
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
    $_SESSION['error'] = "Lab result not found or you don't have permission to edit it.";
    header('Location: lab_results.php');
    exit;
}

// Get error message
$error_message = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lab Result - Clinic Management System</title>
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
                        <a href="lab_result_details.php?id=<?= $lab_result_id ?>" class="text-gray-500 hover:text-gray-700 mr-4">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h2 class="text-xl font-semibold text-gray-800">Edit Lab Result</h2>
                    </div>
                </div>
            </header>

            <main class="p-6">
                <?php if ($error_message): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p><?= htmlspecialchars($error_message) ?></p>
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <form method="POST" class="space-y-6">
                        <!-- Patient Information (Read-only) -->
                        <div class="border-b pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Patient Information</h3>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-16 w-16">
                                    <img class="h-16 w-16 rounded-full" src="https://ui-avatars.com/api/?name=<?= urlencode($lab_result['patient_first_name'] . '+' . $lab_result['patient_last_name']) ?>&background=0D8ABC&color=fff" alt="">
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-xl font-semibold text-gray-900">
                                        <?= htmlspecialchars($lab_result['patient_first_name'] . ' ' . $lab_result['patient_last_name']) ?>
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <!-- Lab Test Information -->
                        <div class="space-y-6">
                            <div>
                                <label for="test_name" class="block text-sm font-medium text-gray-700">Test Name</label>
                                <input type="text" name="test_name" id="test_name" required
                                    value="<?= htmlspecialchars($lab_result['test_name']) ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="test_date" class="block text-sm font-medium text-gray-700">Test Date</label>
                                <input type="date" name="test_date" id="test_date" required
                                    value="<?= htmlspecialchars($lab_result['test_date']) ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="Pending" <?= $lab_result['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Completed" <?= $lab_result['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="Cancelled" <?= $lab_result['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>

                            <div>
                                <label for="results" class="block text-sm font-medium text-gray-700">Results</label>
                                <textarea name="results" id="results" rows="5"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($lab_result['results'] ?? '') ?></textarea>
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($lab_result['notes'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="lab_result_details.php?id=<?= $lab_result_id ?>" 
                               class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Save Changes
                            </button>
                        </div>
                    </form>
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