<?php
/**
 * Enhanced Teacher Addition Debug Script - COMPLETE DIAGNOSIS
 * This will identify and help fix teacher addition issues
 */

// Enable comprehensive error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h2>🔍 Teacher Addition Complete Debug Analysis</h2>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
.info { color: blue; font-weight: bold; }
.section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
pre { background: #f0f0f0; padding: 10px; border-radius: 5px; overflow-x: auto; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
td, th { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>";

$debugLog = [];
$errors = [];
$fixes = [];

// Test 1: File Existence and Permissions
echo "<div class='section'>";
echo "<h3>📁 File System Check</h3>";

$files = [
    'config.php' => 'Configuration file',
    'database.php' => 'Database class',
    'teachers.php' => 'Teachers management page',
    '../assets/uploads/' => 'Upload directory',
    '../api/teachers.php' => 'Teachers API endpoint'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "<span class='success'>✅ {$description}: EXISTS</span><br>";
        if (is_dir($file)) {
            $writable = is_writable($file);
            echo "<span class='" . ($writable ? 'success' : 'error') . "'>" . 
                 ($writable ? "✅" : "❌") . " Directory writable: " . 
                 ($writable ? "YES" : "NO") . "</span><br>";
            if (!$writable) {
                $fixes[] = "Run: chmod 755 {$file}";
            }
        }
    } else {
        echo "<span class='error'>❌ {$description}: MISSING</span><br>";
        $errors[] = "Missing file: {$file}";
        if (strpos($file, '/') !== false) {
            $fixes[] = "Create directory: mkdir -p " . dirname($file);
        }
    }
}
echo "</div>";

// Test 2: Database Connection and Table Structure
echo "<div class='section'>";
echo "<h3>🔗 Database Analysis</h3>";

try {
    require_once 'config.php';
    echo "<span class='success'>✅ Config loaded successfully</span><br>";
    
    // Test database connection
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<span class='success'>✅ Database connection successful</span><br>";
    
    // Check if teachers table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'teachers'");
    if ($stmt->rowCount() > 0) {
        echo "<span class='success'>✅ Teachers table exists</span><br>";
        
        // Get table structure
        $stmt = $pdo->query("DESCRIBE teachers");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>📋 Current Table Structure:</h4>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check for required columns
        $requiredColumns = ['id', 'name', 'qualification', 'subject', 'department', 'bio', 'experience_years', 'email', 'phone', 'photo_url', 'specializations', 'is_active', 'created_at', 'updated_at'];
        $existingColumns = array_column($columns, 'Field');
        
        echo "<h4>🔍 Column Analysis:</h4>";
        foreach ($requiredColumns as $required) {
            if (in_array($required, $existingColumns)) {
                echo "<span class='success'>✅ Column '{$required}' exists</span><br>";
            } else {
                echo "<span class='error'>❌ Column '{$required}' missing</span><br>";
                $errors[] = "Missing column: {$required}";
            }
        }
        
    } else {
        echo "<span class='error'>❌ Teachers table does not exist</span><br>";
        $errors[] = "Teachers table missing";
        $fixes[] = "Run the database schema SQL to create tables";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Database error: " . $e->getMessage() . "</span><br>";
    $errors[] = "Database connection failed: " . $e->getMessage();
}
echo "</div>";

// Test 3: Database Class Testing
echo "<div class='section'>";
echo "<h3>🔧 Database Class Testing</h3>";

try {
    require_once 'database.php';
    echo "<span class='success'>✅ Database class loaded</span><br>";
    
    $db = new Database();
    echo "<span class='success'>✅ Database class instantiated</span><br>";
    
    // Test method existence
    $methods = ['createTeacher', 'getTeachers', 'getTeacherById', 'updateTeacher', 'deleteTeacher'];
    foreach ($methods as $method) {
        if (method_exists($db, $method)) {
            echo "<span class='success'>✅ Method '{$method}' exists</span><br>";
        } else {
            echo "<span class='error'>❌ Method '{$method}' missing</span><br>";
            $errors[] = "Missing method: {$method}";
        }
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Database class error: " . $e->getMessage() . "</span><br>";
    $errors[] = "Database class issue: " . $e->getMessage();
}
echo "</div>";

// Test 4: Simulate Form Submission
echo "<div class='section'>";
echo "<h3>📝 Form Submission Simulation</h3>";

if (isset($db) && isset($pdo)) {
    try {
        // Mock form data
        $formData = [
            'name' => 'Test Teacher Debug',
            'qualification' => 'B.Ed (Test)',
            'subject' => 'Debug Subject',
            'department' => 'Science & Mathematics',
            'bio' => 'This is a test teacher created for debugging purposes.',
            'experience_years' => 5,
            'email' => 'debug@test.school',
            'phone' => '0712345678',
            'specializations' => 'Testing, Debugging, Problem Solving',
            'is_active' => 1
        ];
        
        echo "<span class='info'>📋 Test form data prepared</span><br>";
        echo "<pre>" . print_r($formData, true) . "</pre>";
        
        // Process specializations like in real form
        $specializations = [];
        if (!empty($formData['specializations'])) {
            $specializations = array_map('trim', explode(',', $formData['specializations']));
            $specializations = array_filter($specializations);
            $formData['specializations'] = json_encode($specializations);
        } else {
            $formData['specializations'] = null;
        }
        
        echo "<span class='info'>📊 Processed specializations: " . $formData['specializations'] . "</span><br>";
        
        // Try manual SQL insert first
        echo "<h4>🔍 Manual SQL Test:</h4>";
        $sql = "INSERT INTO teachers (name, qualification, subject, department, bio, experience_years, email, phone, specializations, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        $result = $stmt->execute([
            $formData['name'],
            $formData['qualification'],
            $formData['subject'],
            $formData['department'],
            $formData['bio'],
            $formData['experience_years'],
            $formData['email'],
            $formData['phone'],
            $formData['specializations'],
            $formData['is_active']
        ]);
        
        if ($result) {
            echo "<span class='success'>✅ Manual SQL insert successful</span><br>";
            $teacherId = $pdo->lastInsertId();
            echo "<span class='success'>✅ Teacher created with ID: {$teacherId}</span><br>";
            
            // Verify the insert
            $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ?");
            $stmt->execute([$teacherId]);
            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($teacher) {
                echo "<span class='success'>✅ Teacher retrieved successfully</span><br>";
                echo "<pre>" . print_r($teacher, true) . "</pre>";
                
                // Clean up
                $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
                if ($stmt->execute([$teacherId])) {
                    echo "<span class='success'>✅ Test teacher cleaned up</span><br>";
                }
            }
            
        } else {
            echo "<span class='error'>❌ Manual SQL insert failed</span><br>";
            $errorInfo = $stmt->errorInfo();
            echo "<span class='error'>SQL Error: " . print_r($errorInfo, true) . "</span><br>";
            $errors[] = "SQL insert failed: " . $errorInfo[2];
        }
        
        // Now test with Database class
        echo "<h4>🔍 Database Class Method Test:</h4>";
        if (method_exists($db, 'createTeacher')) {
            $formData['email'] = 'debug2@test.school'; // Different email for second test
            $classResult = $db->createTeacher($formData);
            
            if ($classResult) {
                echo "<span class='success'>✅ Database class createTeacher() successful</span><br>";
                
                // Clean up
                $stmt = $pdo->prepare("DELETE FROM teachers WHERE email = ?");
                $stmt->execute([$formData['email']]);
                echo "<span class='success'>✅ Class test teacher cleaned up</span><br>";
            } else {
                echo "<span class='error'>❌ Database class createTeacher() failed</span><br>";
                $errors[] = "Database class createTeacher method failed";
            }
        }
        
    } catch (Exception $e) {
        echo "<span class='error'>❌ Form simulation error: " . $e->getMessage() . "</span><br>";
        echo "<span class='error'>Stack trace:</span><br>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
        $errors[] = "Form simulation failed: " . $e->getMessage();
    }
}
echo "</div>";

// Test 5: CSRF and Session Check
echo "<div class='section'>";
echo "<h3>🔒 Security and Session Check</h3>";

session_start();
echo "<span class='success'>✅ Session started</span><br>";

if (function_exists('generateCSRFToken')) {
    $token = generateCSRFToken();
    echo "<span class='success'>✅ CSRF token generated: " . substr($token, 0, 10) . "...</span><br>";
    
    if (function_exists('validateCSRFToken')) {
        $valid = validateCSRFToken($token);
        echo "<span class='" . ($valid ? 'success' : 'error') . "'>" . 
             ($valid ? "✅" : "❌") . " CSRF validation: " . 
             ($valid ? "PASS" : "FAIL") . "</span><br>";
    }
} else {
    echo "<span class='error'>❌ CSRF functions not available</span><br>";
    $errors[] = "CSRF functions missing";
}

// Test login requirement
if (function_exists('isLoggedIn')) {
    $loggedIn = isLoggedIn();
    echo "<span class='" . ($loggedIn ? 'success' : 'warning') . "'>" . 
         ($loggedIn ? "✅" : "⚠️") . " Login status: " . 
         ($loggedIn ? "LOGGED IN" : "NOT LOGGED IN") . "</span><br>";
    
    if (!$loggedIn) {
        echo "<span class='warning'>⚠️ You need to be logged in to add teachers</span><br>";
        $fixes[] = "Log in through admin/login.php first";
    }
}
echo "</div>";

// Test 6: File Upload Configuration
echo "<div class='section'>";
echo "<h3>📤 File Upload Configuration</h3>";

echo "<table>";
echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";

$uploadSettings = [
    'file_uploads' => ini_get('file_uploads'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time')
];

foreach ($uploadSettings as $setting => $value) {
    $status = 'info';
    if ($setting === 'file_uploads' && !$value) {
        $status = 'error';
        $errors[] = "File uploads disabled";
    }
    echo "<tr><td>{$setting}</td><td>{$value}</td><td class='{$status}'>" . 
         ($status === 'error' ? '❌' : '✅') . "</td></tr>";
}
echo "</table>";

if (defined('UPLOAD_PATH')) {
    echo "<span class='info'>📁 Defined upload path: " . UPLOAD_PATH . "</span><br>";
    if (!is_dir(UPLOAD_PATH)) {
        echo "<span class='error'>❌ Upload directory doesn't exist</span><br>";
        $errors[] = "Upload directory missing: " . UPLOAD_PATH;
        $fixes[] = "Create upload directory: mkdir -p " . UPLOAD_PATH;
    }
} else {
    echo "<span class='error'>❌ UPLOAD_PATH not defined</span><br>";
    $errors[] = "UPLOAD_PATH constant not defined";
}
echo "</div>";

// Summary and Recommendations
echo "<div class='section'>";
echo "<h3>📊 Summary and Action Plan</h3>";

if (empty($errors)) {
    echo "<span class='success'>🎉 All tests passed! Teacher addition should work correctly.</span><br>";
} else {
    echo "<span class='error'>⚠️ Found " . count($errors) . " issue(s) that need to be fixed:</span><br>";
    echo "<ol>";
    foreach ($errors as $error) {
        echo "<li class='error'>{$error}</li>";
    }
    echo "</ol>";
    
    if (!empty($fixes)) {
        echo "<h4>🔧 Recommended Fixes:</h4>";
        echo "<ol>";
        foreach ($fixes as $fix) {
            echo "<li class='info'>{$fix}</li>";
        }
        echo "</ol>";
    }
}

echo "<h4>🚀 Next Steps:</h4>";
echo "<ol>";
echo "<li>Fix any red ❌ issues listed above</li>";
echo "<li>Ensure you're logged in to the admin panel</li>";
echo "<li>Try adding a teacher through admin/teachers.php</li>";
echo "<li>If it still fails, check PHP error logs for detailed error messages</li>";
echo "<li>Enable error reporting in config.php (set ENVIRONMENT to 'development')</li>";
echo "</ol>";
echo "</div>";

// Show current environment info
echo "<div class='section'>";
echo "<h3>🌍 Environment Information</h3>";
echo "<table>";
echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>Operating System</td><td>" . php_uname() . "</td></tr>";
echo "<tr><td>Server Software</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</td></tr>";
echo "<tr><td>Document Root</td><td>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</td></tr>";
echo "<tr><td>Script Path</td><td>" . __FILE__ . "</td></tr>";
echo "<tr><td>Current Working Directory</td><td>" . getcwd() . "</td></tr>";
echo "</table>";
echo "</div>";

?>