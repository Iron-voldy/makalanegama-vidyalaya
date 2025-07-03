<?php
require_once 'config.php';
require_once 'database.php';

requireLogin();

$db = new Database();
$stats = $db->getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Makalanegama School</title>
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
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="text-muted">Welcome back, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</span>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Achievements</h5>
                                        <h2 class="mb-0"><?= $stats['achievements'] ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Events</h5>
                                        <h2 class="mb-0"><?= $stats['events'] ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">News Articles</h5>
                                        <h2 class="mb-0"><?= $stats['news'] ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-newspaper"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">Teachers</h5>
                                        <h2 class="mb-0"><?= $stats['teachers'] ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-plus"></i> Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <a href="achievements.php?action=add" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-trophy"></i><br>Add Achievement
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="events.php?action=add" class="btn btn-outline-success w-100">
                                            <i class="fas fa-calendar-plus"></i><br>Add Event
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="news.php?action=add" class="btn btn-outline-info w-100">
                                            <i class="fas fa-edit"></i><br>Add News
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="teachers.php?action=add" class="btn btn-outline-warning w-100">
                                            <i class="fas fa-user-plus"></i><br>Add Teacher
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-envelope"></i> Contact Messages</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($stats['new_contacts'] > 0): ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        You have <strong><?= $stats['new_contacts'] ?></strong> new contact message(s) to review.
                                    </div>
                                    <a href="contacts.php" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> View Messages
                                    </a>
                                <?php else: ?>
                                    <p class="text-muted mb-3">No new contact messages.</p>
                                    <a href="contacts.php" class="btn btn-outline-primary">
                                        <i class="fas fa-list"></i> View All Messages
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock"></i> Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Recent Achievements</h6>
                                <?php
                                $recentAchievements = $db->getAchievements(3);
                                if ($recentAchievements):
                                ?>
                                    <ul class="list-unstyled">
                                        <?php foreach ($recentAchievements as $achievement): ?>
                                            <li class="mb-2">
                                                <i class="fas fa-trophy text-warning"></i>
                                                <a href="achievements.php?action=edit&id=<?= $achievement['id'] ?>">
                                                    <?= htmlspecialchars($achievement['title']) ?>
                                                </a>
                                                <small class="text-muted d-block">
                                                    <?= formatDate($achievement['created_at']) ?>
                                                </small>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted">No achievements found.</p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>Upcoming Events</h6>
                                <?php
                                $upcomingEvents = $db->getEvents(3);
                                if ($upcomingEvents):
                                ?>
                                    <ul class="list-unstyled">
                                        <?php foreach ($upcomingEvents as $event): ?>
                                            <li class="mb-2">
                                                <i class="fas fa-calendar text-info"></i>
                                                <a href="events.php?action=edit&id=<?= $event['id'] ?>">
                                                    <?= htmlspecialchars($event['title']) ?>
                                                </a>
                                                <small class="text-muted d-block">
                                                    <?= formatDate($event['event_date']) ?>
                                                </small>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted">No events found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>