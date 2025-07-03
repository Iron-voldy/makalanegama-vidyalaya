<?php
require_once 'config.php';
require_once 'database.php';

requireLogin();

$db = new Database();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Handle form submissions for events
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $data = [
            'title' => sanitizeInput($_POST['title']),
            'description' => sanitizeInput($_POST['description']),
            'event_date' => sanitizeInput($_POST['event_date']),
            'event_time' => sanitizeInput($_POST['event_time']),
            'location' => sanitizeInput($_POST['location']),
            'category' => sanitizeInput($_POST['category']),
            'is_featured' => isset($_POST['is_featured'])
        ];
        
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $imageUrl = uploadImage($_FILES['image']);
            if ($imageUrl) {
                $data['image_url'] = $imageUrl;
            }
        }
        
        if (empty($error)) {
            try {
                if ($action === 'add') {
                    if ($db->createEvent($data)) {
                        $message = 'Event created successfully!';
                        $action = 'list';
                    }
                } elseif ($action === 'edit' && $id) {
                    if ($db->updateEvent($id, $data)) {
                        $message = 'Event updated successfully!';
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
    if ($db->deleteEvent($id)) {
        $message = 'Event deleted successfully!';
    }
    $action = 'list';
}

$event = null;
if ($action === 'edit' && $id) {
    $event = $db->getEventById($id);
}

$events = [];
if ($action === 'list') {
    $events = $db->getEvents();
}
?>