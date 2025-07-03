<?php
require_once 'config.php';
require_once 'database.php';

requireLogin();

$db = new Database();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Handle form submissions for news
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $data = [
            'title' => sanitizeInput($_POST['title']),
            'content' => sanitizeInput($_POST['content']),
            'category' => sanitizeInput($_POST['category']),
            'author' => sanitizeInput($_POST['author']),
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
                    if ($db->createNews($data)) {
                        $message = 'News article created successfully!';
                        $action = 'list';
                    }
                } elseif ($action === 'edit' && $id) {
                    if ($db->updateNews($id, $data)) {
                        $message = 'News article updated successfully!';
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
    if ($db->deleteNews($id)) {
        $message = 'News article deleted successfully!';
    }
    $action = 'list';
}

$newsItem = null;
if ($action === 'edit' && $id) {
    $newsItem = $db->getNewsById($id);
}

$news = [];
if ($action === 'list') {
    $news = $db->getNews();
}
?>
