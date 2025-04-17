<?php
require_once 'config/database.php';
require_once 'auth_check.php';

// Check if prescription ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Prescription ID is required.";
    header('Location: prescriptions.php');
    exit;
}

$prescription_id = $_GET['id'];

// Fetch prescription details with patient and doctor information
$stmt = $pdo->prepare("
    SELECT p.*, 
           pt.first_name as patient_first_name, pt.last_name as patient_last_name,
           d.first_name as doctor_first_name, d.last_name as doctor_last_name
    FROM prescriptions p
    JOIN patients pt ON p.patient_id = pt.id
    JOIN doctors d ON p.doctor_id = d.id
    WHERE p.id = :prescription_id AND p.doctor_id = :doctor_id
");
$stmt->execute([
    'prescription_id' => $prescription_id,
    'doctor_id' => $_SESSION['doctor_id']
]);
$prescription = $stmt->fetch();

// If prescription not found or doesn't belong to the logged-in doctor
if (!$prescription) {
    $_SESSION['error'] = "Prescription not found.";
    header('Location: prescriptions.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Details - Clinic Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="prescriptions.php" class="text-gray-600 hover:text-gray-900 mr-4">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="text-2xl font-semibold text-gray-900">Prescription Details</h1>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <!-- Patient Information -->
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-medium text-gray-900">Patient Information</h2>
                    <div class="mt-2 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Name</p>
                            <p class="text-base font-medium text-gray-900">
                                <?php echo htmlspecialchars($prescription['patient_first_name'] . ' ' . $prescription['patient_last_name']); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Prescribed By</p>
                            <p class="text-base font-medium text-gray-900">
                                Dr. <?php echo htmlspecialchars($prescription['doctor_first_name'] . ' ' . $prescription['doctor_last_name']); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Medication Details -->
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-medium text-gray-900">Medication Details</h2>
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Medication</p>
                            <p class="text-base font-medium text-gray-900"><?php echo htmlspecialchars($prescription['medication']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Dosage</p>
                            <p class="text-base font-medium text-gray-900"><?php echo htmlspecialchars($prescription['dosage']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Frequency</p>
                            <p class="text-base font-medium text-gray-900"><?php echo htmlspecialchars($prescription['frequency']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Duration</p>
                            <p class="text-base font-medium text-gray-900"><?php echo htmlspecialchars($prescription['duration']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="px-6 py-4">
                    <h2 class="text-lg font-medium text-gray-900">Additional Information</h2>
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Prescription Date</p>
                            <p class="text-base font-medium text-gray-900">
                                <?php echo date('F j, Y', strtotime($prescription['prescription_date'])); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
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
                                <?php echo htmlspecialchars($prescription['status']); ?>
                            </span>
                        </div>
                    </div>
                    <?php if (!empty($prescription['instructions'])): ?>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">Instructions</p>
                        <p class="mt-1 text-base text-gray-900"><?php echo nl2br(htmlspecialchars($prescription['instructions'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 