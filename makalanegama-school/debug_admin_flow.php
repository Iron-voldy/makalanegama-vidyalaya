<?php
// Simulate the admin form submission logic
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'admin/config.php';
require_once 'admin/database.php';

echo "Debugging admin form submission flow...\n\n";

// Simulate POST data like the form would send
$_POST = [
    'csrf_token' => generateCSRFToken(), // Valid token
    'action' => 'add',
    'name' => 'Debug Test Teacher',
    'qualification' => 'Master of Education',
    'subject' => 'Physics',
    'department' => 'Science & Mathematics',
    'bio' => 'Experienced physics teacher',
    'experience_years' => '8',
    'email' => 'physics@school.lk',
    'phone' => '0779876543',
    'specializations' => 'Quantum Physics, Mechanics',
    'is_active' => '1'
];

$_FILES = []; // No file upload for this test

echo "Simulated POST data:\n";
print_r($_POST);
echo "\n";

$db = new Database();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$id = $_GET['id'] ?? $_POST['id'] ?? null;
$message = '';
$error = '';

echo "Initial action: " . $action . "\n";
echo "Initial ID: " . ($id ?? 'none') . "\n\n";

// Replicate the exact logic from teachers.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "=== FORM SUBMISSION DETECTED ===\n";
    
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
        echo "❌ CSRF validation failed\n";
    } else {
        echo "✅ CSRF validation passed\n";
        
        // Validate required fields
        if (empty($_POST['name']) || empty($_POST['qualification']) || empty($_POST['subject'])) {
            $error = 'Name, qualification, and subject are required fields.';
            echo "❌ Required field validation failed\n";
        } else {
            echo "✅ Required field validation passed\n";
            
            $data = [
                'name' => sanitizeInput($_POST['name']),
                'qualification' => sanitizeInput($_POST['qualification']),
                'subject' => sanitizeInput($_POST['subject']),
                'department' => sanitizeInput($_POST['department']),
                'bio' => sanitizeInput($_POST['bio']),
                'experience_years' => !empty($_POST['experience_years']) ? (int)$_POST['experience_years'] : null,
                'email' => sanitizeInput($_POST['email']),
                'phone' => sanitizeInput($_POST['phone']),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            echo "Data array created:\n";
            print_r($data);
            echo "\n";
            
            // Handle specializations
            $specializations = [];
            if (!empty($_POST['specializations'])) {
                $specializations = array_map('trim', explode(',', $_POST['specializations']));
                $specializations = array_filter($specializations);
                $data['specializations'] = json_encode($specializations);
                echo "Specializations processed: " . $data['specializations'] . "\n";
            } else {
                $data['specializations'] = null;
                echo "No specializations provided\n";
            }
            
            // Handle photo upload (skipping for this test)
            echo "Photo upload: skipped for debug\n";
        }
        
        if (empty($error)) {
            echo "✅ No errors so far, attempting database operation\n";
            
            try {
                echo "Action check: " . $action . "\n";
                echo "ID check: " . ($id ?? 'none') . "\n";
                
                if ($action === 'add' || (!$id && !empty($_POST['name']))) {
                    echo "✅ Condition met for teacher creation\n";
                    
                    $result = $db->createTeacher($data);
                    echo "Database result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
                    
                    if ($result) {
                        $message = 'Teacher added successfully!';
                        $action = 'list';
                        echo "✅ Teacher creation successful\n";
                    } else {
                        $error = 'Failed to add teacher. Database operation failed.';
                        echo "❌ Teacher creation failed\n";
                    }
                } else {
                    echo "❌ Condition NOT met for teacher creation\n";
                    echo "- action === 'add': " . ($action === 'add' ? 'true' : 'false') . "\n";
                    echo "- !id: " . (!$id ? 'true' : 'false') . "\n";
                    echo "- !empty(name): " . (!empty($_POST['name']) ? 'true' : 'false') . "\n";
                }
                
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
                echo "❌ Exception: " . $e->getMessage() . "\n";
            }
        } else {
            echo "❌ Errors present, skipping database operation: " . $error . "\n";
        }
    }
} else {
    echo "No POST request detected\n";
}

echo "\nFinal results:\n";
echo "Message: " . $message . "\n";
echo "Error: " . $error . "\n";
echo "Action: " . $action . "\n";
?>