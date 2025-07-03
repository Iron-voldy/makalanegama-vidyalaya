<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../admin/config.php';
require_once '../admin/database.php';

try {
    $db = new Database();
    
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
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
    $sql = "SELECT id, title, description, image_url, category, is_featured, created_at FROM achievements";
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $db->getPDO()->prepare($sql);
    
    // Bind parameters with proper types
    for ($i = 0; $i < count($params); $i++) {
        $type = is_int($params[$i]) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($i + 1, $params[$i], $type);
    }
    
    $stmt->execute();
    $achievements = $stmt->fetchAll();
    
    // Format the response
    $response = [];
    foreach ($achievements as $achievement) {
        $response[] = [
            'id' => (int)$achievement['id'],
            'title' => $achievement['title'],
            'description' => $achievement['description'],
            'image_url' => $achievement['image_url'],
            'category' => $achievement['category'],
            'featured' => (bool)$achievement['is_featured'],
            'date' => $achievement['created_at']
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>