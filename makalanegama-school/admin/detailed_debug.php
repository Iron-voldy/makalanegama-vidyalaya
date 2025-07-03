<?php
// Enhanced debug script to identify the exact authentication issue
// Place this in your admin folder as detailed_debug.php

require_once 'config.php';

echo "<h2>🔍 Detailed Authentication Debug</h2>";

// Test database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful<br><br>";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Check admin_users table structure
echo "<h3>📋 Table Structure Check</h3>";
try {
    $stmt = $pdo->query("DESCRIBE admin_users");
    $columns = $stmt->fetchAll();
    echo "✅ admin_users table structure:<br>";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})<br>";
    }
    echo "<br>";
} catch (PDOException $e) {
    echo "❌ Error checking table structure: " . $e->getMessage() . "<br>";
}

// Check current admin users
echo "<h3>👥 Current Admin Users</h3>";
try {
    $stmt = $pdo->query("SELECT id, username, email, full_name, is_active, password_hash FROM admin_users");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "⚠️ No admin users found in database<br>";
    } else {
        foreach ($users as $user) {
            echo "ID: {$user['id']}<br>";
            echo "Username: {$user['username']}<br>";
            echo "Email: {$user['email']}<br>";
            echo "Full Name: {$user['full_name']}<br>";
            echo "Active: " . ($user['is_active'] ? 'Yes' : 'No') . "<br>";
            echo "Password Hash: " . substr($user['password_hash'], 0, 20) . "...<br>";
            echo "Hash Length: " . strlen($user['password_hash']) . "<br>";
            echo "<hr>";
        }
    }
} catch (PDOException $e) {
    echo "❌ Error fetching users: " . $e->getMessage() . "<br>";
}

// Test password hashing and verification
echo "<h3>🔐 Password Testing</h3>";
$testUsername = 'admin';
$testPassword = 'admin123';

// Generate a fresh password hash
$newHash = password_hash($testPassword, PASSWORD_DEFAULT);
echo "New password hash for '{$testPassword}': {$newHash}<br>";
echo "New hash length: " . strlen($newHash) . "<br>";

// Test verification with new hash
$verifyTest = password_verify($testPassword, $newHash);
echo "Verification test with new hash: " . ($verifyTest ? "✅ PASS" : "❌ FAIL") . "<br><br>";

// Delete existing admin user and create new one
echo "<h3>🔄 Recreating Admin User</h3>";
try {
    // Delete existing admin user
    $stmt = $pdo->prepare("DELETE FROM admin_users WHERE username = ?");
    $stmt->execute([$testUsername]);
    echo "✅ Deleted existing admin user (if any)<br>";
    
    // Create new admin user with fresh hash
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([
        $testUsername,
        'admin@makalanegamaschool.lk',
        $newHash,
        'School Administrator',
        'admin',
        1
    ]);
    
    if ($result) {
        echo "✅ Created new admin user successfully<br>";
    } else {
        echo "❌ Failed to create admin user<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Error recreating admin user: " . $e->getMessage() . "<br>";
}

// Verify the newly created user
echo "<h3>✅ Verification Test</h3>";
try {
    $stmt = $pdo->prepare("SELECT id, username, password_hash, full_name, role, is_active FROM admin_users WHERE username = ? AND is_active = 1");
    $stmt->execute([$testUsername]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✅ User found in database<br>";
        echo "Username: {$admin['username']}<br>";
        echo "Full Name: {$admin['full_name']}<br>";
        echo "Role: {$admin['role']}<br>";
        echo "Active: {$admin['is_active']}<br>";
        
        // Test password verification
        $passwordMatch = password_verify($testPassword, $admin['password_hash']);
        echo "Password verification: " . ($passwordMatch ? "✅ SUCCESS" : "❌ FAILED") . "<br>";
        
        if ($passwordMatch) {
            echo "<br><div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px;'>";
            echo "<strong>🎉 Authentication should now work!</strong><br>";
            echo "Username: <strong>{$testUsername}</strong><br>";
            echo "Password: <strong>{$testPassword}</strong>";
            echo "</div>";
        }
        
    } else {
        echo "❌ User not found in database<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Error during verification: " . $e->getMessage() . "<br>";
}

// Test the actual authentication function
echo "<h3>🧪 Testing Authentication Function</h3>";
try {
    require_once 'database.php';
    $db = new Database();
    
    echo "Testing Database class authentication...<br>";
    $authResult = $db->authenticateAdmin($testUsername, $testPassword);
    
    if ($authResult) {
        echo "✅ Database::authenticateAdmin() SUCCESS<br>";
        echo "Returned user data: " . print_r($authResult, true) . "<br>";
    } else {
        echo "❌ Database::authenticateAdmin() FAILED<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing authentication function: " . $e->getMessage() . "<br>";
}

echo "<br><h3>📝 Next Steps:</h3>";
echo "1. Try logging in with username: <strong>admin</strong> and password: <strong>admin123</strong><br>";
echo "2. If it still doesn't work, check the PHP error logs<br>";
echo "3. Make sure sessions are working on your server<br>";
echo "4. Delete this debug file after fixing the issue<br>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
hr { margin: 10px 0; }
</style>