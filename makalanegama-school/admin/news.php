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

$stats = $db->getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-newspaper"></i> 
                        <?= $action === 'add' ? 'Add News Article' : ($action === 'edit' ? 'Edit News Article' : 'Manage News') ?>
                    </h1>
                    <?php if ($action === 'list'): ?>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <a href="?action=add" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Article
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <!-- News List -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">All News Articles (<?= count($news) ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($news)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No news articles found. <a href="?action=add">Add the first one!</a></p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Author</th>
                                                <th>Featured</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($news as $item): ?>
                                                <tr>
                                                    <td>
                                                        <?php if ($item['image_url']): ?>
                                                            <img src="../<?= htmlspecialchars($item['image_url']) ?>" 
                                                                 alt="News" class="img-preview">
                                                        <?php else: ?>
                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                                 style="width: 60px; height: 60px;">
                                                                <i class="fas fa-image text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($item['title']) ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars(substr($item['excerpt'] ?? $item['content'], 0, 80)) ?>...
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?= $item['category'] ?></span>
                                                    </td>
                                                    <td><?= htmlspecialchars($item['author']) ?></td>
                                                    <td>
                                                        <?php if ($item['is_featured']): ?>
                                                            <i class="fas fa-star featured-indicator" title="Featured"></i>
                                                        <?php else: ?>
                                                            <i class="far fa-star text-muted" title="Not Featured"></i>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= formatDate($item['created_at']) ?></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="?action=edit&id=<?= $item['id'] ?>" 
                                                               class="btn btn-outline-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="?action=delete&id=<?= $item['id'] ?>" 
                                                               class="btn btn-outline-danger"
                                                               onclick="return confirm('Are you sure you want to delete this news article?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php elseif ($action === 'add' || $action === 'edit'): ?>
                    <!-- Add/Edit Form -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><?= $action === 'add' ? 'Add New News Article' : 'Edit News Article' ?></h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                        
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Title *</label>
                                            <input type="text" class="form-control" id="title" name="title" 
                                                   value="<?= htmlspecialchars($newsItem['title'] ?? '') ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="content" class="form-label">Content *</label>
                                            <textarea class="form-control" id="content" name="content" 
                                                      rows="8" required><?= htmlspecialchars($newsItem['content'] ?? '') ?></textarea>
                                            <small class="form-text text-muted">Write the full news article content here</small>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="category" class="form-label">Category *</label>
                                                    <select class="form-select" id="category" name="category" required>
                                                        <?php
                                                        $categories = ['General', 'Academic', 'Sports', 'Events', 'Facilities', 'Announcements', 'Achievements', 'Admissions'];
                                                        foreach ($categories as $cat):
                                                        ?>
                                                            <option value="<?= $cat ?>" 
                                                                    <?= ($newsItem['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                                                <?= $cat ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="author" class="form-label">Author</label>
                                                    <input type="text" class="form-control" id="author" name="author" 
                                                           value="<?= htmlspecialchars($newsItem['author'] ?? 'Administration') ?>">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="image" class="form-label">Featured Image</label>
                                            <input type="file" class="form-control" id="image" name="image" 
                                                   accept="image/*">
                                            <small class="form-text text-muted">Max size: 10MB. Formats: JPG, PNG, WebP</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
                                                       <?= ($newsItem['is_featured'] ?? false) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_featured">
                                                    Featured Article
                                                </label>
                                                <small class="form-text text-muted d-block">Featured articles appear prominently on the website</small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> 
                                                <?= $action === 'add' ? 'Create Article' : 'Update Article' ?>
                                            </button>
                                            <a href="news.php" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Cancel
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <?php if ($action === 'edit' && $newsItem && $newsItem['image_url']): ?>
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Current Image</h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <img src="../<?= htmlspecialchars($newsItem['image_url']) ?>" 
                                             alt="Current News Image" class="img-fluid rounded">
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Writing Tips</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="small mb-0">
                                        <li>Write compelling headlines</li>
                                        <li>Start with the most important information</li>
                                        <li>Use clear and simple language</li>
                                        <li>Include relevant details and facts</li>
                                        <li>Choose appropriate categories</li>
                                        <li>Use high-quality images</li>
                                        <li>Mark breaking news as featured</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($action === 'delete'): ?>
                    <!-- Delete Confirmation -->
                    <?php 
                    $newsItem = $db->getNewsById($id);
                    if ($newsItem):
                    ?>
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h5>
                            </div>
                            <div class="card-body">
                                <p>Are you sure you want to delete this news article?</p>
                                <div class="alert alert-warning">
                                    <strong><?= htmlspecialchars($newsItem['title']) ?></strong><br>
                                    Category: <?= htmlspecialchars($newsItem['category']) ?><br>
                                    Author: <?= htmlspecialchars($newsItem['author']) ?><br>
                                    Date: <?= formatDate($newsItem['created_at']) ?><br><br>
                                    <?= htmlspecialchars(substr($newsItem['content'], 0, 200)) ?>...
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="?action=delete&id=<?= $id ?>&confirm=1" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Yes, Delete
                                    </a>
                                    <a href="news.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
