<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';

try {
    // List of tables to clean
    $tables = [
        'appointments',
        'lab_results',
        'medical_records',
        'prescriptions',
        'patients',
        'doctors'
    ];

    // Clean each table
    foreach ($tables as $table) {
        // Check if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("DELETE FROM $table");
            $stmt->execute();
            echo "Cleaned table: $table\n";
        } else {
            echo "Table '$table' does not exist, skipping...\n";
        }
    }

    echo "\nAll dummy data has been removed from the database.\n";
    echo "You can now register a new doctor account and start fresh.\n";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 