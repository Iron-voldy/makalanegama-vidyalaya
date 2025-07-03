<?php
require_once 'config.php';
require_once 'database.php';

$error = '';
$debug = ''; // For debugging purposes

// Check if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            $db = new Database();
            
            // Debug: Check what we're trying to authenticate
            $debug = "Attempting to authenticate: $username";
            
            $admin = $db->authenticateAdmin($username, $password);
            
            if ($admin) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['last_activity'] = time();
                
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid username or password';
                // Debug: Add more specific error information
                $debug .= " - Authentication failed";
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
            $debug .= " - Exception: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Makalanegama School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #800020 0%, #5d0017 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: #800020;
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus {
            border-color: #800020;
            box-shadow: 0 0 0 0.2rem rgba(128, 0, 32, 0.25);
        }
        .btn-login {
            background: #800020;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
        }
        .btn-login:hover {
            background: #5d0017;
        }
        .alert {
            border-radius: 10px;
        }
        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h3><i class="fas fa-shield-alt"></i> Admin Login</h3>
            <p class="mb-0">Makalanegama School</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <!-- Debug information (remove in production) -->
            <?php if ($debug && ENVIRONMENT === 'development'): ?>
                <div class="debug-info">
                    <strong>Debug:</strong> <?= $debug ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" required 
                               value="<?= htmlspecialchars($username ?? '') ?>" autocomplete="username">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="text-center mt-3">
                <small class="text-muted">
                    Default login: admin / admin123
                </small>
            </div>
            
            <?php if (ENVIRONMENT === 'development'): ?>
                <div class="text-center mt-2">
                    <small>
                        <a href="debug_login.php" target="_blank">Debug Login Issues</a>
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>