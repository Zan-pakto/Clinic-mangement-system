<?php
require_once 'config/database.php';
require_once 'config/auth_check.php';

// Get bill ID from URL
$bill_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$bill_id) {
    header('Location: billing.php');
    exit;
}

// Fetch doctor name
$stmt = $pdo->prepare("SELECT first_name, last_name FROM doctors WHERE id = :doctor_id");
$stmt->execute(['doctor_id' => $_SESSION['doctor_id']]);
$doctor = $stmt->fetch();
$doctor_name = $doctor['first_name'] . ' ' . $doctor['last_name'];

// Fetch bill details
$stmt = $pdo->prepare("SELECT b.*, p.first_name as patient_first_name, p.last_name as patient_last_name, p.email as patient_email 
                      FROM billing b 
                      JOIN patients p ON b.patient_id = p.id 
                      WHERE b.id = :bill_id");
$stmt->execute(['bill_id' => $bill_id]);
$bill = $stmt->fetch();

if (!$bill) {
    header('Location: billing.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Details - Clinic Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Bill Details</h1>
                <div class="flex items-center space-x-2">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($doctor_name) ?>&background=0D8ABC&color=fff" alt="Profile" class="w-8 h-8 rounded-full">
                    <span class="text-gray-700">Dr. <?= htmlspecialchars($doctor_name) ?></span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-semibold mb-4">Patient Information</h2>
                    <p><strong>Name:</strong> <?= htmlspecialchars($bill['patient_first_name'] . ' ' . $bill['patient_last_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($bill['patient_email']) ?></p>
                </div>
                <div>
                    <h2 class="text-lg font-semibold mb-4">Bill Information</h2>
                    <p><strong>Bill Date:</strong> 
                        <?php if (isset($bill['bill_date']) && !empty($bill['bill_date'])): ?>
                            <?= date('F j, Y', strtotime($bill['bill_date'])) ?>
                        <?php else: ?>
                            <span class="text-gray-500 italic">Not specified</span>
                        <?php endif; ?>
                    </p>
                    <p><strong>Amount:</strong> 
                        <?php if (isset($bill['amount'])): ?>
                            $<?= number_format($bill['amount'], 2) ?>
                        <?php else: ?>
                            <span class="text-gray-500 italic">Not specified</span>
                        <?php endif; ?>
                    </p>
                    <p><strong>Payment Status:</strong> 
                        <?php if (isset($bill['payment_status']) && !empty($bill['payment_status'])): ?>
                            <span class="<?= $bill['payment_status'] === 'Paid' ? 'text-green-600' : 'text-yellow-600' ?>">
                                <?= htmlspecialchars($bill['payment_status']) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-gray-500 italic">Not specified</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-lg font-semibold mb-4">Description</h2>
                <p class="text-gray-700">
                    <?php if (isset($bill['description']) && !empty($bill['description'])): ?>
                        <?= nl2br(htmlspecialchars($bill['description'])) ?>
                    <?php else: ?>
                        <span class="text-gray-500 italic">No description provided</span>
                    <?php endif; ?>
                </p>
            </div>

            <div class="mt-8 flex justify-end">
                <a href="billing.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Back to Billing
                </a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html> 