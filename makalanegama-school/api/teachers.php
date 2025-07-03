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
    $department = isset($_GET['department']) ? sanitizeInput($_GET['department']) : null;
    $active_only = isset($_GET['active_only']) ? true : false;
    
    // Build query conditions
    $conditions = [];
    $params = [];
    
    if ($department && $department !== 'all') {
        $conditions[] = "department = ?";
        $params[] = $department;
    }
    
    if ($active_only) {
        $conditions[] = "is_active = 1";
    }
    
    // Build SQL query
    $sql = "SELECT id, name, qualification, subject, department, bio, experience_years, email, phone, photo_url, specializations, is_active, created_at FROM teachers";
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY name ASC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $db->getPDO()->prepare($sql);
    
    // Bind parameters with proper types
    for ($i = 0; $i < count($params); $i++) {
        $type = is_int($params[$i]) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($i + 1, $params[$i], $type);
    }
    
    $stmt->execute();
    $teachers = $stmt->fetchAll();
    
    // Format the response
    $response = [];
    foreach ($teachers as $teacher) {
        $specializations = null;
        if ($teacher['specializations']) {
            $specializations = json_decode($teacher['specializations'], true);
        }
        
        $response[] = [
            'id' => (int)$teacher['id'],
            'name' => $teacher['name'],
            'qualification' => $teacher['qualification'],
            'subject' => $teacher['subject'],
            'department' => $teacher['department'],
            'bio' => $teacher['bio'],
            'experience_years' => (int)$teacher['experience_years'],
            'email' => $teacher['email'],
            'phone' => $teacher['phone'],
            'photo_url' => $teacher['photo_url'],
            'specializations' => $specializations,
            'active' => (bool)$teacher['is_active'],
            'created_at' => $teacher['created_at']
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>