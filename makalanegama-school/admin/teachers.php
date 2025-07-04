<?php
require_once 'config.php';
require_once 'database.php';

requireLogin();

$db = new Database();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Handle form submissions
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
            'experience_years' => (int)($_POST['experience_years'] ?? 0),
            'email' => sanitizeInput($_POST['email']),
            'phone' => sanitizeInput($_POST['phone']),
            'is_active' => isset($_POST['is_active'])
        ];
        
        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['size'] > 0) {
            $photoUrl = uploadImage($_FILES['photo']);
            if ($photoUrl) {
                $data['photo_url'] = $photoUrl;
            } else {
                $error = 'Failed to upload photo. Please check file size and format.';
            }
        }
        
        // Handle specializations
        $specializations = [];
        if (!empty($_POST['specializations'])) {
            $specializations = array_map('trim', explode(',', $_POST['specializations']));
            $data['specializations'] = json_encode($specializations);
        }
        
        if (empty($error)) {
            try {
                if ($action === 'add') {
                    if ($db->createTeacher($data)) {
                        $message = 'Teacher added successfully!';
                        $action = 'list';
                    } else {
                        $error = 'Failed to add teacher.';
                    }
                } elseif ($action === 'edit' && $id) {
                    if ($db->updateTeacher($id, $data)) {
                        $message = 'Teacher updated successfully!';
                        $action = 'list';
                    } else {
                        $error = 'Failed to update teacher.';
                    }
                }
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Handle delete action
if ($action === 'delete' && $id && $_GET['confirm'] ?? false) {
    try {
        if ($db->deleteTeacher($id)) {
            $message = 'Teacher deleted successfully!';
        } else {
            $error = 'Failed to delete teacher.';
        }
        $action = 'list';
    } catch (Exception $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Get data for edit form
$teacher = null;
if ($action === 'edit' && $id) {
    $teacher = $db->getTeacherById($id);
    if (!$teacher) {
        $error = 'Teacher not found.';
        $action = 'list';
    }
}

// Get teachers list
$teachers = [];
if ($action === 'list') {
    $teachers = $db->getTeachers();
}

$stats = $db->getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers - Admin Panel</title>
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
                        <i class="fas fa-chalkboard-teacher"></i> 
                        <?= $action === 'add' ? 'Add Teacher' : ($action === 'edit' ? 'Edit Teacher' : 'Manage Teachers') ?>
                    </h1>
                    <?php if ($action === 'list'): ?>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <a href="?action=add" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Teacher
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
                    <!-- Teachers List -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">All Teachers (<?= count($teachers) ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($teachers)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No teachers found. <a href="?action=add">Add the first one!</a></p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Photo</th>
                                                <th>Name</th>
                                                <th>Subject</th>
                                                <th>Department</th>
                                                <th>Experience</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($teachers as $item): ?>
                                                <tr>
                                                    <td>
                                                        <?php if ($item['photo_url']): ?>
                                                            <img src="../<?= htmlspecialchars($item['photo_url']) ?>" 
                                                                 alt="Teacher Photo" class="img-preview rounded-circle">
                                                        <?php else: ?>
                                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" 
                                                                 style="width: 60px; height: 60px;">
                                                                <i class="fas fa-user text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars($item['qualification']) ?>
                                                        </small>
                                                    </td>
                                                    <td><?= htmlspecialchars($item['subject']) ?></td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?= $item['department'] ?></span>
                                                    </td>
                                                    <td><?= $item['experience_years'] ?? 0 ?> years</td>
                                                    <td>
                                                        <?php if ($item['is_active']): ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="?action=edit&id=<?= $item['id'] ?>" 
                                                               class="btn btn-outline-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="?action=delete&id=<?= $item['id'] ?>" 
                                                               class="btn btn-outline-danger"
                                                               onclick="return confirm('Are you sure you want to delete this teacher?')">
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
                                    <h5 class="mb-0"><?= $action === 'add' ? 'Add New Teacher' : 'Edit Teacher' ?></h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Full Name *</label>
                                                    <input type="text" class="form-control" id="name" name="name" 
                                                           value="<?= htmlspecialchars($teacher['name'] ?? '') ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="subject" class="form-label">Subject *</label>
                                                    <input type="text" class="form-control" id="subject" name="subject" 
                                                           value="<?= htmlspecialchars($teacher['subject'] ?? '') ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="qualification" class="form-label">Qualification *</label>
                                            <input type="text" class="form-control" id="qualification" name="qualification" 
                                                   value="<?= htmlspecialchars($teacher['qualification'] ?? '') ?>" required>
                                            <small class="form-text text-muted">e.g., B.Ed (Mathematics), Dip. in Education</small>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="department" class="form-label">Department *</label>
                                                    <select class="form-select" id="department" name="department" required>
                                                        <?php
                                                        $departments = [
                                                            'Science & Mathematics',
                                                            'Languages', 
                                                            'Social Sciences',
                                                            'Arts',
                                                            'Physical Education',
                                                            'Technology',
                                                            'Special Education'
                                                        ];
                                                        foreach ($departments as $dept):
                                                        ?>
                                                            <option value="<?= $dept ?>" 
                                                                    <?= ($teacher['department'] ?? '') === $dept ? 'selected' : '' ?>>
                                                                <?= $dept ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="experience_years" class="form-label">Experience (Years)</label>
                                                    <input type="number" class="form-control" id="experience_years" name="experience_years" 
                                                           value="<?= htmlspecialchars($teacher['experience_years'] ?? '') ?>" min="0" max="50">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="bio" class="form-label">Bio/Description</label>
                                            <textarea class="form-control" id="bio" name="bio" 
                                                      rows="3"><?= htmlspecialchars($teacher['bio'] ?? '') ?></textarea>
                                            <small class="form-text text-muted">Brief description of the teacher's background and teaching approach</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="specializations" class="form-label">Specializations</label>
                                            <input type="text" class="form-control" id="specializations" name="specializations" 
                                                   value="<?= htmlspecialchars(isset($teacher['specializations']) ? implode(', ', json_decode($teacher['specializations'], true) ?? []) : '') ?>">
                                            <small class="form-text text-muted">Comma-separated list (e.g., Advanced Mathematics, Statistics, Problem Solving)</small>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="email" name="email" 
                                                           value="<?= htmlspecialchars($teacher['email'] ?? '') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="phone" class="form-label">Phone</label>
                                                    <input type="text" class="form-control" id="phone" name="phone" 
                                                           value="<?= htmlspecialchars($teacher['phone'] ?? '') ?>">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="photo" class="form-label">Photo</label>
                                            <input type="file" class="form-control" id="photo" name="photo" 
                                                   accept="image/*">
                                            <small class="form-text text-muted">Max size: 10MB. Formats: JPG, PNG, WebP</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                                       <?= ($teacher['is_active'] ?? true) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_active">
                                                    Active Teacher
                                                </label>
                                                <small class="form-text text-muted d-block">Inactive teachers won't appear on the website</small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> 
                                                <?= $action === 'add' ? 'Add Teacher' : 'Update Teacher' ?>
                                            </button>
                                            <a href="teachers.php" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Cancel
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <?php if ($action === 'edit' && $teacher && $teacher['photo_url']): ?>
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Current Photo</h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <img src="../<?= htmlspecialchars($teacher['photo_url']) ?>" 
                                             alt="Current Teacher Photo" class="img-fluid rounded">
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Tips</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="small mb-0">
                                        <li>Use professional headshot photos</li>
                                        <li>Include complete qualifications</li>
                                        <li>Write engaging teacher bios</li>
                                        <li>List relevant specializations</li>
                                        <li>Keep contact information updated</li>
                                        <li>Set inactive status for former teachers</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($action === 'delete'): ?>
                    <!-- Delete Confirmation -->
                    <?php 
                    $teacher = $db->getTeacherById($id);
                    if ($teacher):
                    ?>
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h5>
                            </div>
                            <div class="card-body">
                                <p>Are you sure you want to delete this teacher?</p>
                                <div class="alert alert-warning">
                                    <strong><?= htmlspecialchars($teacher['name']) ?></strong><br>
                                    Subject: <?= htmlspecialchars($teacher['subject']) ?><br>
                                    Department: <?= htmlspecialchars($teacher['department']) ?>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="?action=delete&id=<?= $id ?>&confirm=1" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Yes, Delete
                                    </a>
                                    <a href="teachers.php" class="btn btn-secondary">
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