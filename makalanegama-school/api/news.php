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
error_log("News API called from: " . $_SERVER['REQUEST_URI']);

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
    $category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : null;
    $featured = isset($_GET['featured']) ? true : false;
    
    // Build query conditions
    $conditions = [];
    $params = [];
    
    if ($category && $category !== 'all') {
        $conditions[] = "category = ?";
        $params[] = $category;
    }
    
    if ($featured) {
        $conditions[] = "is_featured = 1";
    }
    
    // Build SQL query
    $sql = "SELECT id, title, content, excerpt, image_url, category, author, is_featured, created_at FROM news";
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY created_at DESC";
    
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
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Log the results
    error_log("Found " . count($news) . " news articles");
    
    // Format the response
    $response = [];
    foreach ($news as $item) {
        $response[] = [
            'id' => (int)$item['id'],
            'title' => $item['title'],
            'content' => $item['content'],
            'excerpt' => $item['excerpt'],
            'image_url' => $item['image_url'],
            'category' => $item['category'],
            'author' => $item['author'],
            'featured' => (bool)$item['is_featured'],
            'is_featured' => (bool)$item['is_featured'],
            'date' => $item['created_at'],
            'created_at' => $item['created_at']
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