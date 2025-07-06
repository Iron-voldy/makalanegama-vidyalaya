<?php
/**
 * Database class for Makalanegama School Admin - FIXED VERSION
 */

require_once 'config.php';

class Database {
    private $pdo;
    
    public function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Test connection
            $this->pdo->query("SELECT 1");
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            if (ENVIRONMENT === 'development') {
                throw new Exception('Database connection failed: ' . $e->getMessage());
            } else {
                throw new Exception('Database connection failed. Please check your configuration.');
            }
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
        try {
            $sql = "SELECT id, username, password_hash, full_name, role, is_active FROM admin_users WHERE username = ? AND is_active = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if (ENVIRONMENT === 'development') {
                error_log("Login attempt for username: $username");
                error_log("User found: " . ($admin ? 'yes' : 'no'));
            }
            
            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Update last login
                $updateSql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
                $updateStmt = $this->pdo->prepare($updateSql);
                $updateStmt->execute([$admin['id']]);
                
                if (ENVIRONMENT === 'development') {
                    error_log("Password verification successful for user: $username");
                }
                
                return $admin;
            } else {
                if (ENVIRONMENT === 'development') {
                    if ($admin) {
                        error_log("Password verification failed for user: $username");
                    } else {
                        error_log("User not found or inactive: $username");
                    }
                }
                return false;
            }
            
        } catch (PDOException $e) {
            error_log("Authentication error: " . $e->getMessage());
            return false;
        }
    }
    
    // Create admin user (for setup purposes)
    public function createAdminUser($username, $email, $password, $fullName, $role = 'admin') {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, 1)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$username, $email, $hashedPassword, $fullName, $role]);
            
        } catch (PDOException $e) {
            error_log("Error creating admin user: " . $e->getMessage());
            return false;
        }
    }
    
    // Update admin password
    public function updateAdminPassword($username, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $sql = "UPDATE admin_users SET password_hash = ? WHERE username = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$hashedPassword, $username]);
            
        } catch (PDOException $e) {
            error_log("Error updating admin password: " . $e->getMessage());
            return false;
        }
    }
    
    // ==================== TEACHERS CRUD - FIXED VERSION ====================
    
    /**
     * Get all teachers ordered by position (admin staff first, then teachers)
     */
    public function getTeachers($limit = null, $offset = 0) {
        try {
            $sql = "SELECT * FROM teachers ORDER BY 
                    CASE 
                        WHEN LOWER(subject) LIKE '%principal%' OR LOWER(subject) LIKE '%principle%' THEN 1
                        WHEN LOWER(subject) LIKE '%vice principal%' OR LOWER(subject) LIKE '%vice principle%' THEN 2
                        WHEN LOWER(subject) LIKE '%office%' OR LOWER(subject) LIKE '%assistant%' THEN 3
                        ELSE 4
                    END,
                    name ASC";
            if ($limit) {
                $sql .= " LIMIT $limit OFFSET $offset";
            }
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching teachers: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get teacher by ID
     */
    public function getTeacherById($id) {
        try {
            $sql = "SELECT * FROM teachers WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error fetching teacher: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new teacher - FIXED
     */
    public function createTeacher($data) {
        try {
            $sql = "INSERT INTO teachers (name, qualification, subject, department, bio, experience_years, email, phone, photo_url, specializations, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['name'],
                $data['qualification'],
                $data['subject'],
                $data['department'],
                $data['bio'] ?? null,
                $data['experience_years'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['photo_url'] ?? null,
                $data['specializations'] ?? null,
                isset($data['is_active']) ? 1 : 0
            ]);
        } catch (PDOException $e) {
            error_log("Error creating teacher: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update teacher - FIXED
     */
    public function updateTeacher($id, $data) {
        try {
            $sql = "UPDATE teachers SET name = ?, qualification = ?, subject = ?, department = ?, bio = ?, experience_years = ?, email = ?, phone = ?, specializations = ?, is_active = ?";
            $params = [
                $data['name'],
                $data['qualification'],
                $data['subject'],
                $data['department'],
                $data['bio'] ?? null,
                $data['experience_years'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['specializations'] ?? null,
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
        } catch (PDOException $e) {
            error_log("Error updating teacher: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete teacher
     */
    public function deleteTeacher($id) {
        try {
            $sql = "DELETE FROM teachers WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting teacher: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get teachers by department
     */
    public function getTeachersByDepartment($department) {
        try {
            $sql = "SELECT * FROM teachers WHERE department = ? AND is_active = 1 ORDER BY name ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$department]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching teachers by department: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get active teachers only
     */
    public function getActiveTeachers($limit = null, $offset = 0) {
        try {
            $sql = "SELECT * FROM teachers WHERE is_active = 1 ORDER BY name ASC";
            if ($limit) {
                $sql .= " LIMIT $limit OFFSET $offset";
            }
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching active teachers: " . $e->getMessage());
            return [];
        }
    }
    
    // ==================== ACHIEVEMENTS CRUD ====================
    
    public function getAchievements($limit = null, $offset = 0) {
        try {
            $sql = "SELECT * FROM achievements ORDER BY created_at DESC";
            if ($limit) {
                $sql .= " LIMIT $limit OFFSET $offset";
            }
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching achievements: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAchievementById($id) {
        try {
            $sql = "SELECT * FROM achievements WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error fetching achievement: " . $e->getMessage());
            return false;
        }
    }
    
    public function createAchievement($data) {
        try {
            $sql = "INSERT INTO achievements (title, description, image_url, category, is_featured) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['title'],
                $data['description'],
                $data['image_url'] ?? null,
                $data['category'],
                isset($data['is_featured']) ? 1 : 0
            ]);
        } catch (PDOException $e) {
            error_log("Error creating achievement: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateAchievement($id, $data) {
        try {
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
        } catch (PDOException $e) {
            error_log("Error updating achievement: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteAchievement($id) {
        try {
            $sql = "DELETE FROM achievements WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting achievement: " . $e->getMessage());
            return false;
        }
    }
    
    // ==================== EVENTS CRUD ====================
    
    public function getEvents($limit = null, $offset = 0) {
        try {
            $sql = "SELECT * FROM events ORDER BY event_date DESC";
            if ($limit) {
                $sql .= " LIMIT $limit OFFSET $offset";
            }
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching events: " . $e->getMessage());
            return [];
        }
    }
    
    public function getEventById($id) {
        try {
            $sql = "SELECT * FROM events WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error fetching event: " . $e->getMessage());
            return false;
        }
    }
    
    public function createEvent($data) {
        try {
            $sql = "INSERT INTO events (title, description, event_date, event_time, location, image_url, category, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['title'],
                $data['description'],
                $data['event_date'],
                $data['event_time'] ?? null,
                $data['location'] ?? 'School',
                $data['image_url'] ?? null,
                $data['category'],
                isset($data['is_featured']) ? 1 : 0
            ]);
        } catch (PDOException $e) {
            error_log("Error creating event: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateEvent($id, $data) {
        try {
            $sql = "UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, category = ?, is_featured = ?";
            $params = [
                $data['title'],
                $data['description'],
                $data['event_date'],
                $data['event_time'] ?? null,
                $data['location'] ?? 'School',
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
        } catch (PDOException $e) {
            error_log("Error updating event: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteEvent($id) {
        try {
            $sql = "DELETE FROM events WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting event: " . $e->getMessage());
            return false;
        }
    }
    
    // ==================== NEWS CRUD ====================
    
    public function getNews($limit = null, $offset = 0) {
        try {
            $sql = "SELECT * FROM news ORDER BY created_at DESC";
            if ($limit) {
                $sql .= " LIMIT $limit OFFSET $offset";
            }
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching news: " . $e->getMessage());
            return [];
        }
    }
    
    public function getNewsById($id) {
        try {
            $sql = "SELECT * FROM news WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error fetching news: " . $e->getMessage());
            return false;
        }
    }
    
    public function createNews($data) {
        try {
            $excerpt = substr(strip_tags($data['content']), 0, 200) . '...';
            
            $sql = "INSERT INTO news (title, content, excerpt, image_url, category, author, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['title'],
                $data['content'],
                $excerpt,
                $data['image_url'] ?? null,
                $data['category'],
                $data['author'] ?? 'Administration',
                isset($data['is_featured']) ? 1 : 0
            ]);
        } catch (PDOException $e) {
            error_log("Error creating news: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateNews($id, $data) {
        try {
            $excerpt = substr(strip_tags($data['content']), 0, 200) . '...';
            
            $sql = "UPDATE news SET title = ?, content = ?, excerpt = ?, category = ?, author = ?, is_featured = ?";
            $params = [
                $data['title'],
                $data['content'],
                $excerpt,
                $data['category'],
                $data['author'] ?? 'Administration',
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
        } catch (PDOException $e) {
            error_log("Error updating news: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteNews($id) {
        try {
            $sql = "DELETE FROM news WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting news: " . $e->getMessage());
            return false;
        }
    }
    
    // ==================== CONTACT SUBMISSIONS ====================
    
    public function getContactSubmissions($limit = null, $offset = 0) {
        try {
            $sql = "SELECT * FROM contact_submissions ORDER BY created_at DESC";
            if ($limit) {
                $sql .= " LIMIT $limit OFFSET $offset";
            }
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching contact submissions: " . $e->getMessage());
            return [];
        }
    }
    
    public function getContactSubmissionById($id) {
        try {
            $sql = "SELECT * FROM contact_submissions WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error fetching contact submission: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateContactStatus($id, $status, $notes = null) {
        try {
            $sql = "UPDATE contact_submissions SET status = ?, admin_notes = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$status, $notes, $id]);
        } catch (PDOException $e) {
            error_log("Error updating contact status: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteContactSubmission($id) {
        try {
            $sql = "DELETE FROM contact_submissions WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting contact submission: " . $e->getMessage());
            return false;
        }
    }
    
    // ==================== DASHBOARD STATISTICS ====================
    
    public function getDashboardStats() {
        $stats = [];
        
        try {
            $sql = "SELECT COUNT(*) as count FROM achievements";
            $stmt = $this->pdo->query($sql);
            $stats['achievements'] = $stmt->fetch()['count'];
            
            $sql = "SELECT COUNT(*) as count FROM events";
            $stmt = $this->pdo->query($sql);
            $stats['events'] = $stmt->fetch()['count'];
            
            $sql = "SELECT COUNT(*) as count FROM news";
            $stmt = $this->pdo->query($sql);
            $stats['news'] = $stmt->fetch()['count'];
            
            $sql = "SELECT COUNT(*) as count FROM teachers WHERE is_active = 1";
            $stmt = $this->pdo->query($sql);
            $stats['teachers'] = $stmt->fetch()['count'];
            
            $sql = "SELECT COUNT(*) as count FROM contact_submissions WHERE status = 'new'";
            $stmt = $this->pdo->query($sql);
            $stats['new_contacts'] = $stmt->fetch()['count'];
            
        } catch (PDOException $e) {
            error_log("Error fetching dashboard stats: " . $e->getMessage());
            $stats = [
                'achievements' => 0,
                'events' => 0,
                'news' => 0,
                'teachers' => 0,
                'new_contacts' => 0
            ];
        }
        
        return $stats;
    }
    
    // ==================== COUNT METHODS FOR PAGINATION ====================
    
    public function countAchievements() {
        try {
            $sql = "SELECT COUNT(*) as count FROM achievements";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetch()['count'];
        } catch (PDOException $e) {
            error_log("Error counting achievements: " . $e->getMessage());
            return 0;
        }
    }
    
    public function countEvents() {
        try {
            $sql = "SELECT COUNT(*) as count FROM events";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetch()['count'];
        } catch (PDOException $e) {
            error_log("Error counting events: " . $e->getMessage());
            return 0;
        }
    }
    
    public function countNews() {
        try {
            $sql = "SELECT COUNT(*) as count FROM news";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetch()['count'];
        } catch (PDOException $e) {
            error_log("Error counting news: " . $e->getMessage());
            return 0;
        }
    }
    
    public function countTeachers() {
        try {
            $sql = "SELECT COUNT(*) as count FROM teachers";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetch()['count'];
        } catch (PDOException $e) {
            error_log("Error counting teachers: " . $e->getMessage());
            return 0;
        }
    }
    
    public function countActiveTeachers() {
        try {
            $sql = "SELECT COUNT(*) as count FROM teachers WHERE is_active = 1";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetch()['count'];
        } catch (PDOException $e) {
            error_log("Error counting active teachers: " . $e->getMessage());
            return 0;
        }
    }
    
    public function countContactSubmissions() {
        try {
            $sql = "SELECT COUNT(*) as count FROM contact_submissions";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetch()['count'];
        } catch (PDOException $e) {
            error_log("Error counting contact submissions: " . $e->getMessage());
            return 0;
        }
    }
}
?>