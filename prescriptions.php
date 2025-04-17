<?php
require_once 'config/database.php';
require_once 'config/auth_check.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $patient_id = $_POST['patient_id'];
        $prescription_date = $_POST['prescription_date'];
        $medication = $_POST['medication'];
        $dosage = $_POST['dosage'];
        $frequency = $_POST['frequency'];
        $duration = $_POST['duration'];
        $instructions = $_POST['instructions'] ?? '';

        // Insert prescription
        $stmt = $pdo->prepare("
            INSERT INTO prescriptions (
                doctor_id, patient_id, prescription_date, medication, 
                dosage, frequency, duration, instructions, status
            ) VALUES (
                :doctor_id, :patient_id, :prescription_date, :medication,
                :dosage, :frequency, :duration, :instructions, 'Active'
            )
        ");

        $stmt->execute([
            'doctor_id' => $_SESSION['doctor_id'],
            'patient_id' => $patient_id,
            'prescription_date' => $prescription_date,
            'medication' => $medication,
            'dosage' => $dosage,
            'frequency' => $frequency,
            'duration' => $duration,
            'instructions' => $instructions
        ]);

        $_SESSION['success'] = "Prescription added successfully!";
        header('Location: prescriptions.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error adding prescription: " . $e->getMessage();
    }
}

// Display success/error messages
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetch doctor name
$stmt = $pdo->prepare("SELECT first_name, last_name FROM doctors WHERE id = :doctor_id");
$stmt->execute(['doctor_id' => $_SESSION['doctor_id']]);
$doctor = $stmt->fetch();
$doctor_name = $doctor['first_name'] . ' ' . $doctor['last_name'];

// Fetch all patients for the dropdown
$stmt = $pdo->prepare("SELECT id, first_name, last_name FROM patients ORDER BY first_name, last_name");
$stmt->execute();
$allPatients = $stmt->fetchAll();

// Fetch prescriptions with patient and doctor information
$stmt = $pdo->prepare("
    SELECT p.*, pt.first_name as patient_first_name, pt.last_name as patient_last_name,
           d.first_name as doctor_first_name, d.last_name as doctor_last_name
    FROM prescriptions p
    JOIN patients pt ON p.patient_id = pt.id
    JOIN doctors d ON p.doctor_id = d.id
    WHERE p.doctor_id = :doctor_id
    ORDER BY p.created_at DESC
");
$stmt->execute(['doctor_id' => $_SESSION['doctor_id']]);
$prescriptions = $stmt->fetchAll();

// Add debug information
if (empty($prescriptions)) {
    $debug_message = "No prescriptions found for doctor ID: " . $_SESSION['doctor_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions - Clinic Management System</title>
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
                <a href="prescriptions.php" class="menu-item flex items-center px-4 py-3 text-gray-700 bg-blue-50">
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
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Prescriptions</h2>
                    <div class="flex items-center space-x-4">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors" onclick="openNewPrescriptionModal()">
                            <i class="fas fa-plus mr-2"></i>New Prescription
                        </button>
                        <div class="flex items-center space-x-2">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($doctor_name) ?>&background=0D8ABC&color=fff" alt="Profile" class="w-8 h-8 rounded-full">
                            <span class="text-gray-700">Dr. <?= htmlspecialchars($doctor_name) ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-6">
                <!-- Prescriptions List -->
                <div class="bg-white rounded-lg shadow-sm p-6" data-aos="fade-up">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">Recent Prescriptions</h3>
                        <div class="flex space-x-4">
                            <div class="relative">
                                <input type="text" placeholder="Search prescriptions..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                            <select class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Status</option>
                                <option value="Active">Active</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <?php if (empty($prescriptions)): ?>
                        <div class="text-center py-8">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                                <i class="fas fa-prescription text-2xl text-blue-600"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Prescriptions Yet</h3>
                            <p class="text-gray-500 mb-4">Start by creating a new prescription for your patient.</p>
                            <button onclick="openNewPrescriptionModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="fas fa-plus mr-2"></i>Create New Prescription
                            </button>
                        </div>
                        <?php else: ?>
                        <?php if (isset($debug_message)): ?>
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                            <p><?php echo $debug_message; ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($success_message)): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                            <p><?php echo $success_message; ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($error_message)): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                            <p><?php echo $error_message; ?></p>
                        </div>
                        <?php endif; ?>
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($prescriptions as $prescription): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=<?= urlencode($prescription['patient_first_name'] . '+' . $prescription['patient_last_name']) ?>&background=0D8ABC&color=fff" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($prescription['patient_first_name'] . ' ' . $prescription['patient_last_name']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            Dr. <?= htmlspecialchars($prescription['doctor_first_name'] . ' ' . $prescription['doctor_last_name']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?= date('M d, Y', strtotime($prescription['prescription_date'])) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="prescription_details.php?id=<?php echo $prescription['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button onclick="deletePrescription(<?php echo $prescription['id']; ?>)" class="text-red-600 hover:text-red-900" title="Delete Prescription">
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

    <!-- New Prescription Modal -->
    <div id="newPrescriptionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-[800px] shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">New Prescription</h3>
                <form id="newPrescriptionForm" class="space-y-4" method="POST" action="prescriptions.php">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Patient</label>
                            <select name="patient_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Patient</option>
                                <?php foreach ($allPatients as $patient): ?>
                                <option value="<?php echo $patient['id']; ?>">
                                    <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="prescription_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <!-- Medications Section -->
                    <div class="border-t pt-4">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Medications</h4>
                        <div class="medication-item grid grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Medication</label>
                                <input type="text" name="medication" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Dosage</label>
                                <input type="text" name="dosage" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Frequency</label>
                                <input type="text" name="frequency" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Duration</label>
                                <input type="text" name="duration" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Instructions</label>
                        <textarea name="instructions" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" rows="2"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300" onclick="closeNewPrescriptionModal()">
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

        function openNewPrescriptionModal() {
            document.getElementById('newPrescriptionModal').classList.remove('hidden');
        }

        function closeNewPrescriptionModal() {
            document.getElementById('newPrescriptionModal').classList.add('hidden');
        }

        function deletePrescription(id) {
            if (confirm('Are you sure you want to delete this prescription? This action cannot be undone.')) {
                window.location.href = 'delete_prescription.php?id=' + id;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('newPrescriptionModal');
            if (event.target == modal) {
                closeNewPrescriptionModal();
            }
        }
    </script>
</body>
</html> 