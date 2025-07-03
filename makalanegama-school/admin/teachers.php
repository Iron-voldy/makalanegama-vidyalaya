<?php
require_once 'config.php';
require_once 'database.php';

requireLogin();

$db = new Database();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Handle form submissions for teachers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $data = [
            'name' => sanitizeInput($_POST['name']),
            'qualification' => sanitizeInput($_POST['qualification']),
            'subject' => sanitizeInput($_POST['subject']),
            'department' => sanitizeInput($_POST['department']),
            'bio' => sanitizeInput($_POST['bio']),
            'experience_years' => (int)$_POST['experience_years'],
            'email' => sanitizeInput($_POST['email']),
            'phone' => sanitizeInput($_POST['phone']),
            'is_active' => isset($_POST['is_active'])
        ];
        
        if (isset($_FILES['photo']) && $_FILES['photo']['size'] > 0) {
            $photoUrl = uploadImage($_FILES['photo']);
            if ($photoUrl) {
                $data['photo_url'] = $photoUrl;
            }
        }
        
        if (empty($error)) {
            try {
                if ($action === 'add') {
                    if ($db->createTeacher($data)) {
                        $message = 'Teacher added successfully!';
                        $action = 'list';
                    }
                } elseif ($action === 'edit' && $id) {
                    if ($db->updateTeacher($id, $data)) {
                        $message = 'Teacher updated successfully!';
                        $action = 'list';
                    }
                }
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

if ($action === 'delete' && $id && $_GET['confirm'] ?? false) {
    if ($db->deleteTeacher($id)) {
        $message = 'Teacher deleted successfully!';
    }
    $action = 'list';
}

$teacher = null;
if ($action === 'edit' && $id) {
    $teacher = $db->getTeacherById($id);
}

$teachers = [];
if ($action === 'list') {
    $teachers = $db->getTeachers();
}
?>