<?php
require_once 'config.php';
require_once 'database.php';

requireLogin();

$db = new Database();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_status') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $status = sanitizeInput($_POST['status']);
        $notes = sanitizeInput($_POST['admin_notes']);
        
        try {
            if ($db->updateContactStatus($id, $status, $notes)) {
                $message = 'Contact status updated successfully!';
            } else {
                $error = 'Failed to update status.';
            }
            $action = 'view';
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Handle delete
if ($action === 'delete' && $id && $_GET['confirm'] ?? false) {
    try {
        if ($db->deleteContactSubmission($id)) {
            $message = 'Contact message deleted successfully!';
        } else {
            $error = 'Failed to delete message.';
        }
        $action = 'list';
    } catch (Exception $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

$contact = null;
if (($action === 'view' || $action === 'update_status') && $id) {
    $contact = $db->getContactSubmissionById($id);
    if (!$contact) {
        $error = 'Contact message not found.';
        $action = 'list';
    }
}

$contacts = [];
if ($action === 'list') {
    $contacts = $db->getContactSubmissions();
}

$stats = $db->getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin Panel</title>
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
                        <i class="fas fa-envelope"></i> 
                        <?= $action === 'view' ? 'View Contact Message' : 'Contact Messages' ?>
                    </h1>
                    <?php if ($action === 'view'): ?>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <a href="contacts.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
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
                    <!-- Contact Messages List -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">All Contact Messages (<?= count($contacts) ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($contacts)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No contact messages found.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Subject</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($contacts as $item): ?>
                                                <tr class="<?= $item['status'] === 'new' ? 'table-warning' : '' ?>">
                                                    <td>
                                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                                        <?php if ($item['phone']): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars($item['phone']) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($item['email']) ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($item['subject']) ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars(substr($item['message'], 0, 50)) ?>...
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span class="badge status-<?= $item['status'] ?>">
                                                            <?= ucfirst($item['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= formatDateTime($item['created_at']) ?></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="?action=view&id=<?= $item['id'] ?>" 
                                                               class="btn btn-outline-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="?action=delete&id=<?= $item['id'] ?>" 
                                                               class="btn btn-outline-danger"
                                                               onclick="return confirm('Are you sure you want to delete this message?')">
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

                <?php elseif ($action === 'view'): ?>
                    <!-- View Contact Message -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Message Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-3"><strong>Name:</strong></div>
                                        <div class="col-sm-9"><?= htmlspecialchars($contact['name']) ?></div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-3"><strong>Email:</strong></div>
                                        <div class="col-sm-9">
                                            <a href="mailto:<?= htmlspecialchars($contact['email']) ?>">
                                                <?= htmlspecialchars($contact['email']) ?>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <?php if ($contact['phone']): ?>
                                        <div class="row mb-3">
                                            <div class="col-sm-3"><strong>Phone:</strong></div>
                                            <div class="col-sm-9">
                                                <a href="tel:<?= htmlspecialchars($contact['phone']) ?>">
                                                    <?= htmlspecialchars($contact['phone']) ?>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-3"><strong>Subject:</strong></div>
                                        <div class="col-sm-9"><?= htmlspecialchars($contact['subject']) ?></div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-3"><strong>Date:</strong></div>
                                        <div class="col-sm-9"><?= formatDateTime($contact['created_at']) ?></div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-3"><strong>Status:</strong></div>
                                        <div class="col-sm-9">
                                            <span class="badge status-<?= $contact['status'] ?>">
                                                <?= ucfirst($contact['status']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="mb-3">
                                        <strong>Message:</strong>
                                        <div class="mt-2 p-3 bg-light rounded">
                                            <?= nl2br(htmlspecialchars($contact['message'])) ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($contact['admin_notes']): ?>
                                        <div class="mb-3">
                                            <strong>Admin Notes:</strong>
                                            <div class="mt-2 p-3 bg-info bg-opacity-10 rounded">
                                                <?= nl2br(htmlspecialchars($contact['admin_notes'])) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Update Status -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Update Status</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="?action=update_status&id=<?= $contact['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                        
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" name="status" required>
                                                <option value="new" <?= $contact['status'] === 'new' ? 'selected' : '' ?>>New</option>
                                                <option value="read" <?= $contact['status'] === 'read' ? 'selected' : '' ?>>Read</option>
                                                <option value="replied" <?= $contact['status'] === 'replied' ? 'selected' : '' ?>>Replied</option>
                                                <option value="archived" <?= $contact['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="admin_notes" class="form-label">Admin Notes</label>
                                            <textarea class="form-control" name="admin_notes" rows="4"><?= htmlspecialchars($contact['admin_notes'] ?? '') ?></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-save"></i> Update Status
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Quick Actions -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="mailto:<?= htmlspecialchars($contact['email']) ?>?subject=Re: <?= htmlspecialchars($contact['subject']) ?>" 
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-reply"></i> Reply via Email
                                        </a>
                                        
                                        <?php if ($contact['phone']): ?>
                                            <a href="tel:<?= htmlspecialchars($contact['phone']) ?>" 
                                               class="btn btn-outline-success">
                                                <i class="fas fa-phone"></i> Call
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="?action=delete&id=<?= $contact['id'] ?>" 
                                           class="btn btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this message?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($action === 'delete'): ?>
                    <!-- Delete Confirmation -->
                    <?php 
                    $contact = $db->getContactSubmissionById($id);
                    if ($contact):
                    ?>
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h5>
                            </div>
                            <div class="card-body">
                                <p>Are you sure you want to delete this contact message?</p>
                                <div class="alert alert-warning">
                                    <strong>From:</strong> <?= htmlspecialchars($contact['name']) ?> (<?= htmlspecialchars($contact['email']) ?>)<br>
                                    <strong>Subject:</strong> <?= htmlspecialchars($contact['subject']) ?><br>
                                    <strong>Date:</strong> <?= formatDateTime($contact['created_at']) ?>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="?action=delete&id=<?= $id ?>&confirm=1" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Yes, Delete
                                    </a>
                                    <a href="contacts.php" class="btn btn-secondary">
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