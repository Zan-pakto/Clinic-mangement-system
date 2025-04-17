<?php
require_once 'config/database.php';
require_once 'auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit;
}

// Get doctor information
$doctor_id = $_SESSION['doctor_id'];
$doctor_name = $_SESSION['doctor_name'] ?? 'Doctor';

// Check if record ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Medical Record ID is required.";
    header('Location: medical_records.php');
    exit;
}

$record_id = $_GET['id'];

// Fetch medical record details
$stmt = $pdo->prepare("
    SELECT mr.*, p.first_name as patient_first_name, p.last_name as patient_last_name
    FROM medical_records mr
    JOIN patients p ON mr.patient_id = p.id
    WHERE mr.id = :record_id AND mr.doctor_id = :doctor_id
");
$stmt->execute([
    'record_id' => $record_id,
    'doctor_id' => $doctor_id
]);
$record = $stmt->fetch();

// If record not found or doesn't belong to the logged-in doctor
if (!$record) {
    $_SESSION['error'] = "Medical record not found.";
    header('Location: medical_records.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $diagnosis = $_POST['diagnosis'];
        $treatment = $_POST['treatment'];
        $notes = $_POST['notes'] ?? '';
        $record_date = $_POST['record_date'];
        $follow_up_date = $_POST['follow_up_date'] ?? null;
        $status = $_POST['status'];
        
        $stmt = $pdo->prepare("
            UPDATE medical_records 
            SET diagnosis = :diagnosis,
                treatment = :treatment,
                notes = :notes,
                record_date = :record_date,
                follow_up_date = :follow_up_date,
                status = :status
            WHERE id = :record_id AND doctor_id = :doctor_id
        ");
        
        $stmt->execute([
            'diagnosis' => $diagnosis,
            'treatment' => $treatment,
            'notes' => $notes,
            'record_date' => $record_date,
            'follow_up_date' => $follow_up_date,
            'status' => $status,
            'record_id' => $record_id,
            'doctor_id' => $doctor_id
        ]);
        
        $_SESSION['success'] = "Medical record updated successfully!";
        header('Location: medical_records.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating medical record: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Medical Record - Clinic Management System</title>
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
                        <h2 class="text-xl font-semibold text-gray-800">Edit Medical Record</h2>
                    </div>
                </div>
            </header>

            <main class="p-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Patient Information</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            <?= htmlspecialchars($record['patient_first_name'] . ' ' . $record['patient_last_name']) ?>
                        </p>
                    </div>

                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Record Date</label>
                                <input type="date" name="record_date" value="<?= htmlspecialchars($record['record_date']) ?>" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Follow-up Date (Optional)</label>
                                <input type="date" name="follow_up_date" value="<?= htmlspecialchars($record['follow_up_date'] ?? '') ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Diagnosis</label>
                            <input type="text" name="diagnosis" value="<?= htmlspecialchars($record['diagnosis']) ?>" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Treatment</label>
                            <textarea name="treatment" required rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($record['treatment']) ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($record['notes'] ?? '') ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="Active" <?= $record['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Completed" <?= $record['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="Archived" <?= $record['status'] === 'Archived' ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="medical_records.php" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html> 