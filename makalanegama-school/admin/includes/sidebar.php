<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'achievements.php' ? 'active' : '' ?>" href="achievements.php">
                    <i class="fas fa-trophy"></i> Achievements
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : '' ?>" href="events.php">
                    <i class="fas fa-calendar"></i> Events
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'news.php' ? 'active' : '' ?>" href="news.php">
                    <i class="fas fa-newspaper"></i> News
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'teachers.php' ? 'active' : '' ?>" href="teachers.php">
                    <i class="fas fa-chalkboard-teacher"></i> Teachers
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'contacts.php' ? 'active' : '' ?>" href="contacts.php">
                    <i class="fas fa-envelope"></i> Contact Messages
                    <?php
                    if (isset($stats) && $stats['new_contacts'] > 0) {
                        echo '<span class="badge bg-danger ms-2">' . $stats['new_contacts'] . '</span>';
                    }
                    ?>
                </a>
            </li>
        </ul>
    </div>
</nav>