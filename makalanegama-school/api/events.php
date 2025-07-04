<?php
// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

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
    $upcoming = isset($_GET['upcoming']) ? true : false;
    
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
    
    if ($upcoming) {
        $conditions[] = "event_date >= CURDATE()";
    }
    
    // Build SQL query
    $sql = "SELECT id, title, description, event_date, event_time, location, image_url, category, is_featured, created_at FROM events";
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY event_date ASC";
    
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
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    $response = [];
    foreach ($events as $event) {
        $response[] = [
            'id' => (int)$event['id'],
            'title' => $event['title'],
            'description' => $event['description'],
            'event_date' => $event['event_date'],
            'event_time' => $event['event_time'],
            'location' => $event['location'],
            'image_url' => $event['image_url'],
            'category' => $event['category'],
            'featured' => (bool)$event['is_featured'],
            'is_featured' => (bool)$event['is_featured'],
            'date' => $event['event_date'],
            'time' => $event['event_time'],
            'created_at' => $event['created_at']
        ];
    }
    
    // Return JSON response
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database connection failed'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'API Error'
    ]);
}
?>