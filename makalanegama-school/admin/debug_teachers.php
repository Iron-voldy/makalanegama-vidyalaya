<?php
/**
 * Teacher Addition Debug Script
 * Place this file in your admin folder as debug_teacher.php
 * Access it via: yoursite.com/admin/debug_teacher.php
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Teacher Addition Debug Script</h2>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; }
.error { color: red; }
.warning { color: orange; }
.info { color: blue; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
</style>";

// Test 1: Check if files exist
echo "<h3>📁 File Existence Check</h3>";
$files = [
    'config.php' => file_exists('config.php'),
    'database.php' => file_exists('database.php'),
    '../api/teachers.php' => file_exists('../api/teachers.php'),
    '../assets/js/teachers.js' => file_exists('../assets/js/teachers.js')
];

foreach ($files as $file => $exists) {
    echo $exists ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>";
    echo " $file<br>";
}

// Test 2: Include files and test database connection
echo "<h3>🔗 Database Connection Test</h3>";
try {
    require_once 'config.php';
    echo "<span class='success'>✅ Config loaded successfully</span><br>";
    
    require_once 'database.php';
    echo "<span class='success'>✅ Database class loaded</span><br>";
    
    $db = new Database();
    echo "<span class='success'>✅ Database connection successful</span><br>";
    
    // Test if teachers table exists
    $stmt = $db->getPDO()->query("SHOW TABLES LIKE 'teachers'");
    if ($stmt->rowCount() > 0) {
        echo "<span class='success'>✅ Teachers table exists</span><br>";
        
        // Check table structure
        $stmt = $db->getPDO()->query("DESCRIBE teachers");
        $columns = $stmt->fetchAll();
        echo "<span class='info'>📋 Teachers table structure:</span><br>";
        foreach ($columns as $column) {
            echo "&nbsp;&nbsp;- {$column['Field']} ({$column['Type']})<br>";
        }
    } else {
        echo "<span class='error'>❌ Teachers table does not exist</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Database error: " . $e->getMessage() . "</span><br>";
}

// Test 3: Check upload directory
echo "<h3>📂 Upload Directory Test</h3>";
if (defined('UPLOAD_PATH')) {
    $uploadDir = UPLOAD_PATH;
    echo "Upload directory: $uploadDir<br>";
    
    if (is_dir($uploadDir)) {
        echo "<span class='success'>✅ Upload directory exists</span><br>";
        
        if (is_writable($uploadDir)) {
            echo "<span class='success'>✅ Upload directory is writable</span><br>";
            
            // Test file creation
            $testFile = $uploadDir . 'test_' . time() . '.txt';
            if (file_put_contents($testFile, 'test')) {
                echo "<span class='success'>✅ Can create files in upload directory</span><br>";
                unlink($testFile);
            } else {
                echo "<span class='error'>❌ Cannot create files in upload directory</span><br>";
            }
        } else {
            echo "<span class='error'>❌ Upload directory is not writable</span><br>";
            echo "<span class='warning'>⚠️ Try running: chmod 755 " . $uploadDir . "</span><br>";
        }
    } else {
        echo "<span class='error'>❌ Upload directory does not exist</span><br>";
        echo "<span class='warning'>⚠️ Try creating directory: " . $uploadDir . "</span><br>";
    }
} else {
    echo "<span class='error'>❌ UPLOAD_PATH not defined in config</span><br>";
}

// Test 4: Test teacher creation with minimal data
echo "<h3>👨‍🏫 Teacher Creation Test</h3>";
if (isset($db)) {
    try {
        // Test data
        $testData = [
            'name' => 'Debug Test Teacher',
            'qualification' => 'B.Ed (Test)',
            'subject' => 'Test Subject',
            'department' => 'Science & Mathematics',
            'bio' => 'This is a test teacher for debugging purposes.',
            'experience_years' => 5,
            'email' => 'debug@test.com',
            'phone' => '0712345678',
            'specializations' => '["Testing", "Debugging"]',
            'is_active' => 1
        ];
        
        echo "<span class='info'>📝 Test data:</span><br>";
        echo "<pre>" . print_r($testData, true) . "</pre>";
        
        // Try to create teacher
        $result = $db->createTeacher($testData);
        
        if ($result) {
            echo "<span class='success'>✅ Teacher creation successful!</span><br>";
            
            // Get the created teacher
            $pdo = $db->getPDO();
            $stmt = $pdo->prepare("SELECT * FROM teachers WHERE email = ?");
            $stmt->execute(['debug@test.com']);
            $teacher = $stmt->fetch();
            
            if ($teacher) {
                echo "<span class='success'>✅ Teacher found in database</span><br>";
                echo "<span class='info'>📄 Created teacher data:</span><br>";
                echo "<pre>" . print_r($teacher, true) . "</pre>";
                
                // Clean up - delete the test teacher
                $stmt = $pdo->prepare("DELETE FROM teachers WHERE email = ?");
                if ($stmt->execute(['debug@test.com'])) {
                    echo "<span class='success'>✅ Test teacher cleaned up</span><br>";
                }
            }
        } else {
            echo "<span class='error'>❌ Teacher creation failed</span><br>";
            
            // Get PDO error info
            $pdo = $db->getPDO();
            $errorInfo = $pdo->errorInfo();
            echo "<span class='error'>SQL Error Info: " . print_r($errorInfo, true) . "</span><br>";
        }
        
    } catch (Exception $e) {
        echo "<span class='error'>❌ Exception during teacher creation: " . $e->getMessage() . "</span><br>";
        echo "<span class='error'>Stack trace:</span><br>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}

// Test 5: Test form simulation
echo "<h3>📝 Form Submission Simulation</h3>";
if (isset($db)) {
    // Simulate what happens when form is submitted
    echo "<span class='info'>🔍 Simulating form submission...</span><br>";
    
    // Mock POST data
    $_POST = [
        'name' => 'Form Test Teacher',
        'qualification' => 'B.Ed (Form Test)',
        'subject' => 'Form Test Subject',
        'department' => 'Languages',
        'bio' => 'Form test bio',
        'experience_years' => '3',
        'email' => 'formtest@test.com',
        'phone' => '0771234567',
        'specializations' => 'Writing, Reading, Grammar',
        'is_active' => '1',
        'csrf_token' => 'test_token'
    ];
    
    // Mock session for CSRF (bypass for testing)
    $_SESSION['csrf_token'] = 'test_token';
    
    // Process like in teachers.php
    $data = [
        'name' => $_POST['name'],
        'qualification' => $_POST['qualification'],
        'subject' => $_POST['subject'],
        'department' => $_POST['department'],
        'bio' => $_POST['bio'],
        'experience_years' => !empty($_POST['experience_years']) ? (int)$_POST['experience_years'] : null,
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    
    // Handle specializations
    $specializations = [];
    if (!empty($_POST['specializations'])) {
        $specializations = array_map('trim', explode(',', $_POST['specializations']));
        $specializations = array_filter($specializations);
        $data['specializations'] = json_encode($specializations);
    } else {
        $data['specializations'] = null;
    }
    
    echo "<span class='info'>📊 Processed form data:</span><br>";
    echo "<pre>" . print_r($data, true) . "</pre>";
    
    // Test creation
    try {
        $result = $db->createTeacher($data);
        if ($result) {
            echo "<span class='success'>✅ Form simulation successful!</span><br>";
            
            // Clean up
            $pdo = $db->getPDO();
            $stmt = $pdo->prepare("DELETE FROM teachers WHERE email = ?");
            $stmt->execute(['formtest@test.com']);
            echo "<span class='success'>✅ Form test data cleaned up</span><br>";
        } else {
            echo "<span class='error'>❌ Form simulation failed</span><br>";
        }
    } catch (Exception $e) {
        echo "<span class='error'>❌ Form simulation exception: " . $e->getMessage() . "</span><br>";
    }
}

// Test 6: Check PHP configuration
echo "<h3>⚙️ PHP Configuration Check</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'fileinfo'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span class='success'>✅ $ext extension loaded</span><br>";
    } else {
        echo "<span class='error'>❌ $ext extension NOT loaded</span><br>";
    }
}

// Test 7: Check existing teachers
echo "<h3>👥 Existing Teachers Check</h3>";
if (isset($db)) {
    try {
        $teachers = $db->getTeachers();
        echo "<span class='info'>📊 Found " . count($teachers) . " teachers in database</span><br>";
        
        if (count($teachers) > 0) {
            echo "<span class='info'>📝 Sample teacher data:</span><br>";
            echo "<pre>" . print_r($teachers[0], true) . "</pre>";
        }
    } catch (Exception $e) {
        echo "<span class='error'>❌ Error getting teachers: " . $e->getMessage() . "</span><br>";
    }
}

echo "<h3>🎯 Recommendations</h3>";
echo "<ul>";
echo "<li>If database connection failed: Check your database credentials in config.php</li>";
echo "<li>If teachers table doesn't exist: Run the school.sql script to create tables</li>";
echo "<li>If upload directory issues: Create the directory and set proper permissions (755)</li>";
echo "<li>If teacher creation failed: Check the SQL error info above for specific issues</li>";
echo "<li>If all tests pass but form still fails: Check browser console for JavaScript errors</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Fix any red ❌ issues shown above</li>";
echo "<li>Try adding a teacher through the admin panel again</li>";
echo "<li>If still failing, check the PHP error log for detailed errors</li>";
echo "<li>Enable development mode in config.php for more detailed error messages</li>";
echo "</ol>";
?>