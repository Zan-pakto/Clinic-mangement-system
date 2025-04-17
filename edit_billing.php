<?php
session_start();
require_once 'config/database.php';
require_once 'config/auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['doctor_id'])) {
    header('Location: login.php');
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

// Fetch doctor name
$stmt = $pdo->prepare("SELECT first_name, last_name FROM doctors WHERE id = :doctor_id");
$stmt->execute(['doctor_id' => $doctor_id]);
$doctor = $stmt->fetch();
$doctor_name = $doctor['first_name'] . ' ' . $doctor['last_name'];

// Check if billing ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Billing ID is required";
    header('Location: billing.php');
    exit;
}

$billing_id = $_GET['id'];

// Fetch billing details
try {
    $stmt = $pdo->prepare("
        SELECT b.*, 
               p.first_name as patient_first_name, 
               p.last_name as patient_last_name
        FROM billing b
        JOIN patients p ON b.patient_id = p.id
        WHERE b.id = :id AND b.doctor_id = :doctor_id
    ");
    $stmt->execute(['id' => $billing_id, 'doctor_id' => $doctor_id]);
    $billing = $stmt->fetch();

    if (!$billing) {
        $_SESSION['error_message'] = "Billing record not found or you don't have permission to edit it";
        header('Location: billing.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching billing record: " . $e->getMessage();
    header('Location: billing.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['amount', 'payment_method', 'payment_status'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields.");
            }
        }

        // Validate amount is a positive number
        if (!is_numeric($_POST['amount']) || $_POST['amount'] <= 0) {
            throw new Exception("Amount must be a positive number.");
        }

        $stmt = $pdo->prepare("
            UPDATE billing SET 
                amount = :amount,
                payment_method = :payment_method,
                payment_status = :payment_status,
                payment_date = :payment_date,
                notes = :notes
            WHERE id = :id AND doctor_id = :doctor_id
        ");

        $stmt->execute([
            'amount' => $_POST['amount'],
            'payment_method' => $_POST['payment_method'],
            'payment_status' => $_POST['payment_status'],
            'payment_date' => $_POST['payment_status'] === 'Paid' ? $_POST['payment_date'] : null,
            'notes' => $_POST['notes'] ?? null,
            'id' => $billing_id,
            'doctor_id' => $doctor_id
        ]);

        $_SESSION['success_message'] = "Billing record updated successfully";
        header('Location: billing.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Billing - Clinic Management System</title>
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
                <a href="billing.php" class="menu-item flex items-center px-4 py-3 text-gray-700 bg-blue-50">
                    <i class="fas fa-file-invoice-dollar w-6"></i>
                    <span>Billing</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Edit Billing</h2>
                    <div class="flex items-center space-x-2">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($doctor_name) ?>&background=0D8ABC&color=fff" alt="Profile" class="w-8 h-8 rounded-full">
                        <span class="text-gray-700">Dr. <?= htmlspecialchars($doctor_name) ?></span>
                    </div>
                </div>
            </header>

            <main class="p-6">
                <?php if (isset($error)): ?>
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Edit Billing Form -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Patient</label>
                                <input type="text" value="<?= htmlspecialchars($billing['patient_first_name'] . ' ' . $billing['patient_last_name']) ?>" disabled
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Amount</label>
                                <input type="number" step="0.01" name="amount" value="<?= htmlspecialchars($billing['amount']) ?>" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                <select name="payment_method" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="Cash" <?= $billing['payment_method'] === 'Cash' ? 'selected' : '' ?>>Cash</option>
                                    <option value="Credit Card" <?= $billing['payment_method'] === 'Credit Card' ? 'selected' : '' ?>>Credit Card</option>
                                    <option value="Debit Card" <?= $billing['payment_method'] === 'Debit Card' ? 'selected' : '' ?>>Debit Card</option>
                                    <option value="Insurance" <?= $billing['payment_method'] === 'Insurance' ? 'selected' : '' ?>>Insurance</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Status</label>
                                <select name="payment_status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" onchange="togglePaymentDate(this.value)">
                                    <option value="Pending" <?= $billing['payment_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Paid" <?= $billing['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
                                    <option value="Cancelled" <?= $billing['payment_status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                            <div id="payment_date_field" class="<?= $billing['payment_status'] === 'Paid' ? '' : 'hidden' ?>">
                                <label class="block text-sm font-medium text-gray-700">Payment Date</label>
                                <input type="date" name="payment_date" value="<?= $billing['payment_status'] === 'Paid' ? ($billing['payment_date'] ?? date('Y-m-d')) : '' ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($billing['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="billing.php" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script>
        function togglePaymentDate(status) {
            const paymentDateField = document.getElementById('payment_date_field');
            if (status === 'Paid') {
                paymentDateField.classList.remove('hidden');
            } else {
                paymentDateField.classList.add('hidden');
            }
        }
    </script>
</body>
</html> 