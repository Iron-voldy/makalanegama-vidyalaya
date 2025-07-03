<?php
/**
 * Database class for Makalanegama School Admin
 */

require_once 'config.php';

class Database {
    private $pdo;
    
    public function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get PDO instance for direct queries
     */
    public function getPDO() {
        return $this->pdo;
    }
    
    // Admin Authentication
    public function authenticateAdmin($username, $password) {
        $sql = "SELECT id, username, password_hash, full_name, role FROM admin_users WHERE username = ? AND is_active = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Update last login
            $updateSql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([$admin['id']]);
            
            return $admin;
        }
        
        return false;
    }
    
    // Achievements CRUD
    public function getAchievements($limit = null, $offset = 0) {
        $sql = "SELECT * FROM achievements ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getAchievementById($id) {
        $sql = "SELECT * FROM achievements WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function createAchievement($data) {
        $sql = "INSERT INTO achievements (title, description, image_url, category, is_featured) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['image_url'],
            $data['category'],
            isset($data['is_featured']) ? 1 : 0
        ]);
    }
    
    public function updateAchievement($id, $data) {
        $sql = "UPDATE achievements SET title = ?, description = ?, category = ?, is_featured = ?";
        $params = [$data['title'], $data['description'], $data['category'], isset($data['is_featured']) ? 1 : 0];
        
        if (!empty($data['image_url'])) {
            $sql .= ", image_url = ?";
            $params[] = $data['image_url'];
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function deleteAchievement($id) {
        $sql = "DELETE FROM achievements WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Events CRUD
    public function getEvents($limit = null, $offset = 0) {
        $sql = "SELECT * FROM events ORDER BY event_date DESC";
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getEventById($id) {
        $sql = "SELECT * FROM events WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function createEvent($data) {
        $sql = "INSERT INTO events (title, description, event_date, event_time, location, image_url, category, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['event_date'],
            $data['event_time'],
            $data['location'],
            $data['image_url'],
            $data['category'],
            isset($data['is_featured']) ? 1 : 0
        ]);
    }
    
    public function updateEvent($id, $data) {
        $sql = "UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, category = ?, is_featured = ?";
        $params = [
            $data['title'],
            $data['description'],
            $data['event_date'],
            $data['event_time'],
            $data['location'],
            $data['category'],
            isset($data['is_featured']) ? 1 : 0
        ];
        
        if (!empty($data['image_url'])) {
            $sql .= ", image_url = ?";
            $params[] = $data['image_url'];
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function deleteEvent($id) {
        $sql = "DELETE FROM events WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // News CRUD
    public function getNews($limit = null, $offset = 0) {
        $sql = "SELECT * FROM news ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getNewsById($id) {
        $sql = "SELECT * FROM news WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function createNews($data) {
        $excerpt = substr(strip_tags($data['content']), 0, 200) . '...';
        
        $sql = "INSERT INTO news (title, content, excerpt, image_url, category, author, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['content'],
            $excerpt,
            $data['image_url'],
            $data['category'],
            $data['author'],
            isset($data['is_featured']) ? 1 : 0
        ]);
    }
    
    public function updateNews($id, $data) {
        $excerpt = substr(strip_tags($data['content']), 0, 200) . '...';
        
        $sql = "UPDATE news SET title = ?, content = ?, excerpt = ?, category = ?, author = ?, is_featured = ?";
        $params = [
            $data['title'],
            $data['content'],
            $excerpt,
            $data['category'],
            $data['author'],
            isset($data['is_featured']) ? 1 : 0
        ];
        
        if (!empty($data['image_url'])) {
            $sql .= ", image_url = ?";
            $params[] = $data['image_url'];
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function deleteNews($id) {
        $sql = "DELETE FROM news WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Teachers CRUD
    public function getTeachers($limit = null, $offset = 0) {
        $sql = "SELECT * FROM teachers ORDER BY name ASC";
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getTeacherById($id) {
        $sql = "SELECT * FROM teachers WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function createTeacher($data) {
        $sql = "INSERT INTO teachers (name, qualification, subject, department, bio, experience_years, email, phone, photo_url, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['qualification'],
            $data['subject'],
            $data['department'],
            $data['bio'],
            $data['experience_years'],
            $data['email'],
            $data['phone'],
            $data['photo_url'],
            isset($data['is_active']) ? 1 : 0
        ]);
    }
    
    public function updateTeacher($id, $data) {
        $sql = "UPDATE teachers SET name = ?, qualification = ?, subject = ?, department = ?, bio = ?, experience_years = ?, email = ?, phone = ?, is_active = ?";
        $params = [
            $data['name'],
            $data['qualification'],
            $data['subject'],
            $data['department'],
            $data['bio'],
            $data['experience_years'],
            $data['email'],
            $data['phone'],
            isset($data['is_active']) ? 1 : 0
        ];
        
        if (!empty($data['photo_url'])) {
            $sql .= ", photo_url = ?";
            $params[] = $data['photo_url'];
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function deleteTeacher($id) {
        $sql = "DELETE FROM teachers WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Contact Submissions
    public function getContactSubmissions($limit = null, $offset = 0) {
        $sql = "SELECT * FROM contact_submissions ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getContactSubmissionById($id) {
        $sql = "SELECT * FROM contact_submissions WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function updateContactStatus($id, $status, $notes = null) {
        $sql = "UPDATE contact_submissions SET status = ?, admin_notes = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$status, $notes, $id]);
    }
    
    public function deleteContactSubmission($id) {
        $sql = "DELETE FROM contact_submissions WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Dashboard Statistics
    public function getDashboardStats() {
        $stats = [];
        
        $sql = "SELECT COUNT(*) as count FROM achievements";
        $stmt = $this->pdo->query($sql);
        $stats['achievements'] = $stmt->fetch()['count'];
        
        $sql = "SELECT COUNT(*) as count FROM events";
        $stmt = $this->pdo->query($sql);
        $stats['events'] = $stmt->fetch()['count'];
        
        $sql = "SELECT COUNT(*) as count FROM news";
        $stmt = $this->pdo->query($sql);
        $stats['news'] = $stmt->fetch()['count'];
        
        $sql = "SELECT COUNT(*) as count FROM teachers";
        $stmt = $this->pdo->query($sql);
        $stats['teachers'] = $stmt->fetch()['count'];
        
        $sql = "SELECT COUNT(*) as count FROM contact_submissions WHERE status = 'new'";
        $stmt = $this->pdo->query($sql);
        $stats['new_contacts'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    // Count methods for pagination
    public function countAchievements() {
        $sql = "SELECT COUNT(*) as count FROM achievements";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch()['count'];
    }
    
    public function countEvents() {
        $sql = "SELECT COUNT(*) as count FROM events";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch()['count'];
    }
    
    public function countNews() {
        $sql = "SELECT COUNT(*) as count FROM news";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch()['count'];
    }
    
    public function countTeachers() {
        $sql = "SELECT COUNT(*) as count FROM teachers";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch()['count'];
    }
    
    public function countContactSubmissions() {
        $sql = "SELECT COUNT(*) as count FROM contact_submissions";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch()['count'];
    }
}
?>