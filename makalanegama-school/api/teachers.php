<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Log the request
error_log("Teachers API called from: " . $_SERVER['REQUEST_URI']);

try {
    // Check if the admin files exist
    $configPath = dirname(__FILE__) . '/../admin/config.php';
    $databasePath = dirname(__FILE__) . '/../admin/database.php';
    
    if (!file_exists($configPath)) {
        throw new Exception("Config file not found at: $configPath");
    }
    
    if (!file_exists($databasePath)) {
        throw new Exception("Database file not found at: $databasePath");
    }
    
    // Include the admin configuration and database
    require_once $configPath;
    require_once $databasePath;
    
    // Create database instance
    $db = new Database();
    
    // Get parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $department = isset($_GET['department']) ? sanitizeInput($_GET['department']) : null;
    $active_only = isset($_GET['active_only']) ? true : false;
    
    // Build query conditions
    $conditions = [];
    $params = [];
    
    if ($department && $department !== 'all') {
        // Map filter departments to database departments
        $departmentMap = [
            'science' => 'Science & Mathematics',
            'languages' => 'Languages',
            'social' => 'Social Sciences',
            'arts' => 'Arts',
            'sports' => 'Physical Education',
            'technology' => 'Technology',
            'other' => ['Physical Education', 'Technology', 'Special Education', 'Arts']
        ];
        
        if (isset($departmentMap[$department])) {
            if (is_array($departmentMap[$department])) {
                $placeholders = str_repeat('?,', count($departmentMap[$department]) - 1) . '?';
                $conditions[] = "department IN ($placeholders)";
                $params = array_merge($params, $departmentMap[$department]);
            } else {
                $conditions[] = "department = ?";
                $params[] = $departmentMap[$department];
            }
        }
    }
    
    if ($active_only) {
        $conditions[] = "is_active = 1";
    }
    
    // Build SQL query
    $sql = "SELECT id, name, qualification, subject, department, bio, experience_years, email, phone, photo_url, specializations, is_active, created_at FROM teachers";
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY name ASC";
    
    if ($limit > 0) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    // Execute query
    $stmt = $db->getPDO()->prepare($sql);
    
    // Bind parameters with proper types
    for ($i = 0; $i < count($params); $i++) {
        $type = is_int($params[$i]) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($i + 1, $params[$i], $type);
    }
    
    $stmt->execute();
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Log the results
    error_log("Found " . count($teachers) . " teachers");
    
    // Format the response
    $response = [];
    foreach ($teachers as $teacher) {
        $specializations = null;
        if ($teacher['specializations']) {
            $decoded = json_decode($teacher['specializations'], true);
            $specializations = is_array($decoded) ? $decoded : [];
        }
        
        $response[] = [
            'id' => (int)$teacher['id'],
            'name' => $teacher['name'],
            'qualification' => $teacher['qualification'],
            'subject' => $teacher['subject'],
            'department' => $teacher['department'],
            'bio' => $teacher['bio'],
            'experience_years' => (int)($teacher['experience_years'] ?? 0),
            'email' => $teacher['email'],
            'phone' => $teacher['phone'],
            'photo_url' => $teacher['photo_url'],
            'specializations' => $specializations,
            'active' => (bool)$teacher['is_active'],
            'is_active' => (bool)$teacher['is_active'],
            'created_at' => $teacher['created_at']
        ];
    }
    
    // Return JSON response
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Database connection failed',
        'message' => $e->getMessage(),
        'debug' => [
            'config_path' => $configPath ?? 'not set',
            'database_path' => $databasePath ?? 'not set',
            'config_exists' => file_exists($configPath ?? ''),
            'database_exists' => file_exists($databasePath ?? '')
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'API Error',
        'message' => $e->getMessage(),
        'debug' => [
            'current_dir' => __DIR__,
            'script_path' => __FILE__,
            'config_path' => $configPath ?? 'not set',
            'database_path' => $databasePath ?? 'not set'
        ]
    ], JSON_PRETTY_PRINT);
}
?>