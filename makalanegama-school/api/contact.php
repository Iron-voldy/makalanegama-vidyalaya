<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../admin/config.php';
require_once '../admin/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required_fields = ['name', 'email', 'subject', 'message'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field '$field' is required"]);
            exit;
        }
    }
    
    // Sanitize input
    $data = [
        'name' => sanitizeInput($input['name']),
        'email' => sanitizeInput($input['email']),
        'phone' => sanitizeInput($input['phone'] ?? ''),
        'subject' => sanitizeInput($input['subject']),
        'message' => sanitizeInput($input['message']),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email address']);
        exit;
    }
    
    // Insert into database
    $db = new Database();
    $sql = "INSERT INTO contact_submissions (name, email, phone, subject, message, ip_address) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->getPDO()->prepare($sql);
    
    if ($stmt->execute([
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['subject'],
        $data['message'],
        $data['ip_address']
    ])) {
        echo json_encode([
            'success' => true,
            'message' => 'Your message has been sent successfully! We will get back to you soon.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to send message']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>