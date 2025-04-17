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

// Fetch doctor details from database
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch();

// Get success/error messages
$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Fetch all patients for the dropdown
$stmt = $pdo->prepare("SELECT id, first_name, last_name FROM patients ORDER BY first_name, last_name");
$stmt->execute();
$patients = $stmt->fetchAll();

// Handle form submission for adding new medical record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_record') {
        try {
            $patient_id = $_POST['patient_id'];
            $diagnosis = $_POST['diagnosis'];
            $treatment = $_POST['treatment'];
            $notes = $_POST['notes'] ?? '';
            $record_date = $_POST['record_date'];
            $follow_up_date = $_POST['follow_up_date'] ?? null;
            
            $stmt = $pdo->prepare("
                INSERT INTO medical_records (
                    patient_id, doctor_id, diagnosis, treatment, 
                    notes, record_date, follow_up_date, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')
            ");
            
            $stmt->execute([
                $patient_id, $doctor_id, $diagnosis, $treatment, 
                $notes, $record_date, $follow_up_date
            ]);
            
            $_SESSION['success'] = "Medical record added successfully!";
            header('Location: medical_records.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error adding medical record: " . $e->getMessage();
        }
    }
}

// Fetch medical records with patient information
$stmt = $pdo->prepare("
    SELECT mr.*, p.first_name as patient_first_name, p.last_name as patient_last_name
    FROM medical_records mr
    JOIN patients p ON mr.patient_id = p.id
    WHERE mr.doctor_id = ?
    ORDER BY mr.record_date DESC
");
$stmt->execute([$doctor_id]);
$medical_records = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - Clinic Management System</title>
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
                    <h2 class="text-xl font-semibold text-gray-800">Medical Records</h2>
                    <div class="flex items-center space-x-4">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors" onclick="openNewRecordModal()">
                            <i class="fas fa-plus mr-2"></i>New Medical Record
                        </button>
                        <div class="flex items-center space-x-2">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($doctor_name) ?>&background=0D8ABC&color=fff" alt="Profile" class="w-8 h-8 rounded-full">
                            <span class="text-gray-700">Dr. <?= htmlspecialchars($doctor_name) ?></span>
                        </div>
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

                <!-- Medical Records List -->
                <div class="bg-white rounded-lg shadow-sm p-6" data-aos="fade-up">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">Recent Medical Records</h3>
                        <div class="flex space-x-4">
                            <div class="relative">
                                <input type="text" placeholder="Search records..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                            <select class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Status</option>
                                <option value="Active">Active</option>
                                <option value="Completed">Completed</option>
                                <option value="Archived">Archived</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <?php if (empty($medical_records)): ?>
                        <div class="text-center py-8">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                                <i class="fas fa-file-medical text-2xl text-blue-600"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Medical Records Yet</h3>
                            <p class="text-gray-500 mb-4">Start by creating a new medical record for your patient.</p>
                            <button onclick="openNewRecordModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="fas fa-plus mr-2"></i>Create New Record
                            </button>
                        </div>
                        <?php else: ?>
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diagnosis</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($medical_records as $record): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=<?= urlencode($record['patient_first_name'] . '+' . $record['patient_last_name']) ?>&background=0D8ABC&color=fff" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($record['patient_first_name'] . ' ' . $record['patient_last_name']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            <?= htmlspecialchars($record['diagnosis']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?= date('M d, Y', strtotime($record['record_date'])) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
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
                                            <?= htmlspecialchars($record['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="medical_record_details.php?id=<?php echo $record['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button onclick="editRecord(<?php echo $record['id']; ?>)" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Edit Record">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteRecord(<?php echo $record['id']; ?>)" class="text-red-600 hover:text-red-900" title="Delete Record">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- New Medical Record Modal -->
    <div id="newRecordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-[800px] shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">New Medical Record</h3>
                <form id="newRecordForm" class="space-y-4" method="POST" action="medical_records.php">
                    <input type="hidden" name="action" value="add_record">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Patient</label>
                            <select name="patient_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                <option value="<?php echo $patient['id']; ?>">
                                    <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Record Date</label>
                            <input type="date" name="record_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Diagnosis</label>
                        <input type="text" name="diagnosis" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Treatment</label>
                        <textarea name="treatment" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" rows="3"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" rows="2"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Follow-up Date (Optional)</label>
                        <input type="date" name="follow_up_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300" onclick="closeNewRecordModal()">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Save
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

        function openNewRecordModal() {
            document.getElementById('newRecordModal').classList.remove('hidden');
        }

        function closeNewRecordModal() {
            document.getElementById('newRecordModal').classList.add('hidden');
        }

        function editRecord(id) {
            window.location.href = 'edit_medical_record.php?id=' + id;
        }

        function deleteRecord(id) {
            if (confirm('Are you sure you want to delete this medical record? This action cannot be undone.')) {
                window.location.href = 'delete_medical_record.php?id=' + id;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('newRecordModal');
            if (event.target == modal) {
                closeNewRecordModal();
            }
        }
    </script>
</body>
</html> 