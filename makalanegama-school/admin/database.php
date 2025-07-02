<?php
/**
 * Complete Database class for Makalanegama School Website - FIXED VERSION
 * Handles all database operations, queries, and data management
 * 
 * @author School IT Team
 * @version 1.1
 * @since 2024
 */

// Include configuration first
if (!defined('INCLUDED')) {
    define('INCLUDED', true);
}

require_once 'config.php';

class Database {
    private $pdo;
    private $host;
    private $dbname;
    private $username;
    private $password;
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->host = DB_HOST;
        $this->dbname = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        
        $this->connect();
    }
    
    /**
     * Establish database connection with error handling
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_PERSISTENT => false,
                PDO::ATTR_TIMEOUT => 30
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            
            // Set SQL mode for strict data handling
            $this->pdo->exec("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            
        } catch (PDOException $e) {
            if (function_exists('logMessage')) {
                logMessage('ERROR', 'Database connection failed: ' . $e->getMessage());
            }
            
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                throw new Exception('Database connection failed: ' . $e->getMessage());
            } else {
                throw new Exception('Database connection failed. Please try again later.');
            }
        }
    }
    
    /**
     * Get PDO instance for advanced operations
     */
    public function getPDO() {
        return $this->pdo;
    }
    
    /**
     * Execute a prepared statement with error handling (PRIVATE)
     */
    private function execute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            
            // Bind parameters with proper data types
            foreach ($params as $key => $value) {
                $type = PDO::PARAM_STR;
                if (is_int($value)) {
                    $type = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $type = PDO::PARAM_NULL;
                }
                
                $stmt->bindValue($key, $value, $type);
            }
            
            $stmt->execute();
            return $stmt;
            
        } catch (PDOException $e) {
            if (function_exists('logMessage')) {
                logMessage('ERROR', 'Database query failed: ' . $e->getMessage(), [
                    'sql' => $sql,
                    'params' => $params
                ]);
            }
            
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                throw new Exception('Database query failed: ' . $e->getMessage());
            } else {
                throw new Exception('Database operation failed. Please try again.');
            }
        }
    }
    
    /**
     * PUBLIC method to execute queries (for external use)
     */
    public function executeQuery($sql, $params = []) {
        return $this->execute($sql, $params);
    }
    
    /**
     * PUBLIC method to safely execute and fetch results
     */
    public function query($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * PUBLIC method to safely execute and fetch single result
     */
    public function queryOne($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetch();
    }
    
    // =============================
    // ACHIEVEMENTS METHODS
    // =============================
    
    /**
     * Insert new achievement
     */
    public function insertAchievement($data) {
        $sql = "INSERT INTO achievements (
                    title, description, image_url, category, 
                    telegram_message_id, telegram_user_id, 
                    is_approved, is_featured, created_at
                ) VALUES (
                    :title, :description, :image_url, :category, 
                    :telegram_message_id, :telegram_user_id, 
                    :is_approved, :is_featured, :created_at
                )";
        
        $params = [
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':image_url' => $data['image_url'] ?? null,
            ':category' => $data['category'] ?? 'Academic',
            ':telegram_message_id' => $data['telegram_message_id'] ?? null,
            ':telegram_user_id' => $data['telegram_user_id'] ?? null,
            ':is_approved' => isset($data['is_approved']) ? (int)$data['is_approved'] : (defined('AUTO_APPROVE_CONTENT') && AUTO_APPROVE_CONTENT ? 1 : 0),
            ':is_featured' => isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
            ':created_at' => $data['created_at'] ?? date('Y-m-d H:i:s')
        ];
        
        $stmt = $this->execute($sql, $params);
        $insertId = $this->pdo->lastInsertId();
        
        // Log the insertion
        if (function_exists('logMessage')) {
            logMessage('INFO', 'Achievement inserted', ['id' => $insertId, 'title' => $data['title']]);
        }
        
        return $insertId;
    }
    
    /**
     * Get achievements with filtering and pagination
     */
    public function getAchievements($options = []) {
        $limit = $options['limit'] ?? null;
        $offset = $options['offset'] ?? 0;
        $category = $options['category'] ?? null;
        $approved_only = $options['approved_only'] ?? true;
        $featured_only = $options['featured_only'] ?? false;
        $search = $options['search'] ?? null;
        
        $sql = "SELECT * FROM achievements";
        $params = [];
        $conditions = [];
        
        if ($approved_only) {
            $conditions[] = "is_approved = 1";
        }
        
        if ($featured_only) {
            $conditions[] = "is_featured = 1";
        }
        
        if ($category && $category !== 'all') {
            $conditions[] = "category = :category";
            $params[':category'] = $category;
        }
        
        if ($search) {
            $conditions[] = "(title LIKE :search OR description LIKE :search)";
            $params[':search'] = "%{$search}%";
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = (int)$limit;
            $params[':offset'] = (int)$offset;
        }
        
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get single achievement by ID
     */
    public function getAchievementById($id) {
        $sql = "SELECT * FROM achievements WHERE id = :id";
        $stmt = $this->execute($sql, [':id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Update achievement
     */
    public function updateAchievement($id, $data) {
        $allowedFields = ['title', 'description', 'image_url', 'category', 'is_approved', 'is_featured'];
        $setClause = [];
        $params = [':id' => $id];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $setClause[] = "{$field} = :{$field}";
                $params[":{$field}"] = $value;
            }
        }
        
        if (empty($setClause)) {
            return false;
        }
        
        $sql = "UPDATE achievements SET " . implode(', ', $setClause) . ", updated_at = NOW() WHERE id = :id";
        $stmt = $this->execute($sql, $params);
        
        if (function_exists('logMessage')) {
            logMessage('INFO', 'Achievement updated', ['id' => $id]);
        }
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Delete achievement
     */
    public function deleteAchievement($id) {
        // Get image URL before deleting
        $achievement = $this->getAchievementById($id);
        
        $sql = "DELETE FROM achievements WHERE id = :id";
        $stmt = $this->execute($sql, [':id' => $id]);
        
        // Delete associated image file
        if ($achievement && $achievement['image_url']) {
            $this->deleteFile($achievement['image_url']);
        }
        
        if (function_exists('logMessage')) {
            logMessage('INFO', 'Achievement deleted', ['id' => $id]);
        }
        return $stmt->rowCount() > 0;
    }
    
    // =============================
    // EVENTS METHODS
    // =============================
    
    /**
     * Insert new event
     */
    public function insertEvent($data) {
        $sql = "INSERT INTO events (
                    title, description, event_date, event_time, 
                    location, image_url, category, 
                    telegram_message_id, telegram_user_id, 
                    is_approved, is_featured, created_at
                ) VALUES (
                    :title, :description, :event_date, :event_time, 
                    :location, :image_url, :category, 
                    :telegram_message_id, :telegram_user_id, 
                    :is_approved, :is_featured, :created_at
                )";
        
        $params = [
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':event_date' => $data['event_date'] ?? date('Y-m-d'),
            ':event_time' => $data['event_time'] ?? null,
            ':location' => $data['location'] ?? 'School',
            ':image_url' => $data['image_url'] ?? null,
            ':category' => $data['category'] ?? 'Academic',
            ':telegram_message_id' => $data['telegram_message_id'] ?? null,
            ':telegram_user_id' => $data['telegram_user_id'] ?? null,
            ':is_approved' => isset($data['is_approved']) ? (int)$data['is_approved'] : (defined('AUTO_APPROVE_CONTENT') && AUTO_APPROVE_CONTENT ? 1 : 0),
            ':is_featured' => isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
            ':created_at' => $data['created_at'] ?? date('Y-m-d H:i:s')
        ];
        
        $stmt = $this->execute($sql, $params);
        $insertId = $this->pdo->lastInsertId();
        
        if (function_exists('logMessage')) {
            logMessage('INFO', 'Event inserted', ['id' => $insertId, 'title' => $data['title']]);
        }
        return $insertId;
    }
    
    /**
     * Get events with filtering and pagination
     */
    public function getEvents($options = []) {
        $limit = $options['limit'] ?? null;
        $offset = $options['offset'] ?? 0;
        $category = $options['category'] ?? null;
        $approved_only = $options['approved_only'] ?? true;
        $upcoming_only = $options['upcoming_only'] ?? false;
        $featured_only = $options['featured_only'] ?? false;
        $search = $options['search'] ?? null;
        
        $sql = "SELECT * FROM events";
        $params = [];
        $conditions = [];
        
        if ($approved_only) {
            $conditions[] = "is_approved = 1";
        }
        
        if ($featured_only) {
            $conditions[] = "is_featured = 1";
        }
        
        if ($upcoming_only) {
            $conditions[] = "event_date >= CURDATE()";
        }
        
        if ($category && $category !== 'all') {
            $conditions[] = "category = :category";
            $params[':category'] = $category;
        }
        
        if ($search) {
            $conditions[] = "(title LIKE :search OR description LIKE :search)";
            $params[':search'] = "%{$search}%";
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        if ($upcoming_only) {
            $sql .= " ORDER BY event_date ASC";
        } else {
            $sql .= " ORDER BY event_date DESC";
        }
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = (int)$limit;
            $params[':offset'] = (int)$offset;
        }
        
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get single event by ID
     */
    public function getEventById($id) {
        $sql = "SELECT * FROM events WHERE id = :id";
        $stmt = $this->execute($sql, [':id' => $id]);
        return $stmt->fetch();
    }
    
    // =============================
    // NEWS METHODS
    // =============================
    
    /**
     * Insert new news article
     */
    public function insertNews($data) {
        $sql = "INSERT INTO news (
                    title, content, excerpt, image_url, category, author,
                    telegram_message_id, telegram_user_id, 
                    is_approved, is_featured, created_at
                ) VALUES (
                    :title, :content, :excerpt, :image_url, :category, :author,
                    :telegram_message_id, :telegram_user_id, 
                    :is_approved, :is_featured, :created_at
                )";
        
        // Generate excerpt if not provided
        $content = $data['content'] ?? $data['description'] ?? '';
        $excerpt = $data['excerpt'] ?? $this->generateExcerpt($content);
        
        $params = [
            ':title' => $data['title'],
            ':content' => $content,
            ':excerpt' => $excerpt,
            ':image_url' => $data['image_url'] ?? null,
            ':category' => $data['category'] ?? 'General',
            ':author' => $data['author'] ?? 'Administration',
            ':telegram_message_id' => $data['telegram_message_id'] ?? null,
            ':telegram_user_id' => $data['telegram_user_id'] ?? null,
            ':is_approved' => isset($data['is_approved']) ? (int)$data['is_approved'] : (defined('AUTO_APPROVE_CONTENT') && AUTO_APPROVE_CONTENT ? 1 : 0),
            ':is_featured' => isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
            ':created_at' => $data['created_at'] ?? date('Y-m-d H:i:s')
        ];
        
        $stmt = $this->execute($sql, $params);
        $insertId = $this->pdo->lastInsertId();
        
        if (function_exists('logMessage')) {
            logMessage('INFO', 'News article inserted', ['id' => $insertId, 'title' => $data['title']]);
        }
        return $insertId;
    }
    
    /**
     * Get news articles with filtering and pagination
     */
    public function getNews($options = []) {
        $limit = $options['limit'] ?? null;
        $offset = $options['offset'] ?? 0;
        $category = $options['category'] ?? null;
        $approved_only = $options['approved_only'] ?? true;
        $featured_only = $options['featured_only'] ?? false;
        $search = $options['search'] ?? null;
        
        $sql = "SELECT * FROM news";
        $params = [];
        $conditions = [];
        
        if ($approved_only) {
            $conditions[] = "is_approved = 1";
        }
        
        if ($featured_only) {
            $conditions[] = "is_featured = 1";
        }
        
        if ($category && $category !== 'all') {
            $conditions[] = "category = :category";
            $params[':category'] = $category;
        }
        
        if ($search) {
            $conditions[] = "(title LIKE :search OR content LIKE :search)";
            $params[':search'] = "%{$search}%";
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = (int)$limit;
            $params[':offset'] = (int)$offset;
        }
        
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    // =============================
    // TEACHERS METHODS
    // =============================
    
    /**
     * Insert new teacher
     */
    public function insertTeacher($data) {
        $sql = "INSERT INTO teachers (
                    name, qualification, subject, department, bio, 
                    experience_years, email, phone, photo_url, specializations,
                    telegram_message_id, telegram_user_id, 
                    is_approved, is_active, created_at
                ) VALUES (
                    :name, :qualification, :subject, :department, :bio, 
                    :experience_years, :email, :phone, :photo_url, :specializations,
                    :telegram_message_id, :telegram_user_id, 
                    :is_approved, :is_active, :created_at
                )";
        
        $params = [
            ':name' => $data['name'],
            ':qualification' => $data['qualification'],
            ':subject' => $data['subject'],
            ':department' => $data['department'] ?? 'General',
            ':bio' => $data['bio'] ?? $data['description'] ?? null,
            ':experience_years' => $data['experience_years'] ?? null,
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':photo_url' => $data['photo_url'] ?? $data['image_url'] ?? null,
            ':specializations' => isset($data['specializations']) ? json_encode($data['specializations']) : null,
            ':telegram_message_id' => $data['telegram_message_id'] ?? null,
            ':telegram_user_id' => $data['telegram_user_id'] ?? null,
            ':is_approved' => isset($data['is_approved']) ? (int)$data['is_approved'] : (defined('AUTO_APPROVE_CONTENT') && AUTO_APPROVE_CONTENT ? 1 : 0),
            ':is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            ':created_at' => $data['created_at'] ?? date('Y-m-d H:i:s')
        ];
        
        $stmt = $this->execute($sql, $params);
        $insertId = $this->pdo->lastInsertId();
        
        if (function_exists('logMessage')) {
            logMessage('INFO', 'Teacher inserted', ['id' => $insertId, 'name' => $data['name']]);
        }
        return $insertId;
    }
    
    /**
     * Get teachers with filtering and pagination
     */
    public function getTeachers($options = []) {
        $limit = $options['limit'] ?? null;
        $offset = $options['offset'] ?? 0;
        $department = $options['department'] ?? null;
        $approved_only = $options['approved_only'] ?? true;
        $active_only = $options['active_only'] ?? true;
        $search = $options['search'] ?? null;
        
        $sql = "SELECT * FROM teachers";
        $params = [];
        $conditions = [];
        
        if ($approved_only) {
            $conditions[] = "is_approved = 1";
        }
        
        if ($active_only) {
            $conditions[] = "is_active = 1";
        }
        
        if ($department && $department !== 'all') {
            $conditions[] = "department = :department";
            $params[':department'] = $department;
        }
        
        if ($search) {
            $conditions[] = "(name LIKE :search OR qualification LIKE :search OR subject LIKE :search OR bio LIKE :search)";
            $params[':search'] = "%{$search}%";
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY name ASC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = (int)$limit;
            $params[':offset'] = (int)$offset;
        }
        
        $stmt = $this->execute($sql, $params);
        $teachers = $stmt->fetchAll();
        
        // Decode specializations JSON
        foreach ($teachers as &$teacher) {
            if ($teacher['specializations']) {
                $teacher['specializations'] = json_decode($teacher['specializations'], true);
            }
        }
        
        return $teachers;
    }
    
    // =============================
    // GALLERY METHODS
    // =============================
    
    /**
     * Insert gallery image
     */
    public function insertGalleryImage($data) {
        $sql = "INSERT INTO gallery (
                    title, description, image_url, category, alt_text,
                    telegram_message_id, telegram_user_id, 
                    is_approved, is_featured, created_at
                ) VALUES (
                    :title, :description, :image_url, :category, :alt_text,
                    :telegram_message_id, :telegram_user_id, 
                    :is_approved, :is_featured, :created_at
                )";
        
        $params = [
            ':title' => $data['title'],
            ':description' => $data['description'] ?? null,
            ':image_url' => $data['image_url'],
            ':category' => $data['category'] ?? 'General',
            ':alt_text' => $data['alt_text'] ?? $data['title'],
            ':telegram_message_id' => $data['telegram_message_id'] ?? null,
            ':telegram_user_id' => $data['telegram_user_id'] ?? null,
            ':is_approved' => isset($data['is_approved']) ? (int)$data['is_approved'] : (defined('AUTO_APPROVE_CONTENT') && AUTO_APPROVE_CONTENT ? 1 : 0),
            ':is_featured' => isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
            ':created_at' => $data['created_at'] ?? date('Y-m-d H:i:s')
        ];
        
        $stmt = $this->execute($sql, $params);
        $insertId = $this->pdo->lastInsertId();
        
        if (function_exists('logMessage')) {
            logMessage('INFO', 'Gallery image inserted', ['id' => $insertId, 'title' => $data['title']]);
        }
        return $insertId;
    }
    
    /**
     * Get gallery images with filtering and pagination
     */
    public function getGalleryImages($options = []) {
        $limit = $options['limit'] ?? null;
        $offset = $options['offset'] ?? 0;
        $category = $options['category'] ?? null;
        $approved_only = $options['approved_only'] ?? true;
        $featured_only = $options['featured_only'] ?? false;
        $search = $options['search'] ?? null;
        
        $sql = "SELECT * FROM gallery";
        $params = [];
        $conditions = [];
        
        if ($approved_only) {
            $conditions[] = "is_approved = 1";
        }
        
        if ($featured_only) {
            $conditions[] = "is_featured = 1";
        }
        
        if ($category && $category !== 'all') {
            $conditions[] = "category = :category";
            $params[':category'] = $category;
        }
        
        if ($search) {
            $conditions[] = "(title LIKE :search OR description LIKE :search)";
            $params[':search'] = "%{$search}%";
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = (int)$limit;
            $params[':offset'] = (int)$offset;
        }
        
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    // =============================
    // CONTACT & SUBMISSIONS METHODS
    // =============================
    
    /**
     * Log contact form submission
     */
    public function logContactSubmission($data) {
        $sql = "INSERT INTO contact_submissions (
                    name, email, phone, subject, message, 
                    ip_address, user_agent, status, created_at
                ) VALUES (
                    :name, :email, :phone, :subject, :message, 
                    :ip_address, :user_agent, :status, :created_at
                )";
        
        $params = [
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?? null,
            ':subject' => $data['subject'],
            ':message' => $data['message'],
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ':status' => $data['status'] ?? 'new',
            ':created_at' => date('Y-m-d H:i:s')
        ];
        
        $stmt = $this->execute($sql, $params);
        $insertId = $this->pdo->lastInsertId();
        
        if (function_exists('logMessage')) {
            logMessage('INFO', 'Contact submission logged', [
                'id' => $insertId, 
                'email' => $data['email'],
                'subject' => $data['subject']
            ]);
        }
        
        return $insertId;
    }
    
    // =============================
    // CONTENT STATISTICS
    // =============================
    
    /**
     * Get comprehensive content statistics
     */
    public function getContentStats() {
        $stats = [];
        
        try {
            // Count achievements
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as pending
                    FROM achievements";
            $stmt = $this->execute($sql);
            $stats['achievements'] = $stmt->fetch()['approved'] ?? 0;
            
            // Count events
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN event_date >= CURDATE() AND is_approved = 1 THEN 1 ELSE 0 END) as upcoming
                    FROM events";
            $stmt = $this->execute($sql);
            $eventStats = $stmt->fetch();
            $stats['events'] = $eventStats['approved'] ?? 0;
            $stats['upcoming_events'] = $eventStats['upcoming'] ?? 0;
            
            // Count news
            $sql = "SELECT COUNT(*) as count FROM news WHERE is_approved = 1";
            $stmt = $this->execute($sql);
            $stats['news'] = $stmt->fetch()['count'] ?? 0;
            
            // Count teachers
            $sql = "SELECT COUNT(*) as count FROM teachers WHERE is_approved = 1 AND is_active = 1";
            $stmt = $this->execute($sql);
            $stats['teachers'] = $stmt->fetch()['count'] ?? 0;
            
            // Count gallery images
            $sql = "SELECT COUNT(*) as count FROM gallery WHERE is_approved = 1";
            $stmt = $this->execute($sql);
            $stats['gallery'] = $stmt->fetch()['count'] ?? 0;
            
        } catch (Exception $e) {
            // Return default stats if queries fail
            $stats = [
                'achievements' => 0,
                'events' => 0,
                'upcoming_events' => 0,
                'news' => 0,
                'teachers' => 0,
                'gallery' => 0
            ];
        }
        
        return $stats;
    }
    
    // =============================
    // CONTENT APPROVAL & MODERATION
    // =============================
    
    /**
     * Get all pending content for approval
     */
    public function getPendingContent($type = null) {
        $tables = ['achievements', 'events', 'news', 'teachers', 'gallery'];
        $results = [];
        
        if ($type && in_array($type, $tables)) {
            $tables = [$type];
        }
        
        foreach ($tables as $table) {
            try {
                $sql = "SELECT 
                            id, created_at, telegram_user_id,
                            '{$table}' as content_type,
                            CASE 
                                WHEN '{$table}' = 'teachers' THEN name
                                ELSE title 
                            END as display_title,
                            CASE 
                                WHEN '{$table}' = 'teachers' THEN bio
                                WHEN '{$table}' = 'news' THEN content
                                ELSE description 
                            END as display_description
                        FROM {$table} 
                        WHERE is_approved = 0 
                        ORDER BY created_at DESC";
                
                $stmt = $this->execute($sql);
                $tableResults = $stmt->fetchAll();
                
                foreach ($tableResults as &$result) {
                    $result['content_type'] = $table;
                }
                
                $results = array_merge($results, $tableResults);
                
            } catch (Exception $e) {
                if (function_exists('logMessage')) {
                    logMessage('WARNING', 'Failed to get pending content', ['table' => $table, 'error' => $e->getMessage()]);
                }
            }
        }
        
        // Sort by created_at
        usort($results, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $results;
    }
    
    /**
     * Approve content by type and ID
     */
    public function approveContent($type, $id) {
        $allowedTypes = ['achievements', 'events', 'news', 'teachers', 'gallery'];
        
        if (!in_array($type, $allowedTypes)) {
            return false;
        }
        
        $sql = "UPDATE {$type} SET is_approved = 1, approved_at = NOW() WHERE id = :id";
        $stmt = $this->execute($sql, [':id' => $id]);
        
        if ($stmt->rowCount() > 0) {
            if (function_exists('logMessage')) {
                logMessage('INFO', 'Content approved', ['type' => $type, 'id' => $id]);
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Reject/delete content by type and ID
     */
    public function rejectContent($type, $id) {
        $allowedTypes = ['achievements', 'events', 'news', 'teachers', 'gallery'];
        
        if (!in_array($type, $allowedTypes)) {
            return false;
        }
        
        // Get file URL before deleting
        $sql = "SELECT image_url, photo_url FROM {$type} WHERE id = :id";
        $stmt = $this->execute($sql, [':id' => $id]);
        $content = $stmt->fetch();
        
        // Delete from database
        $sql = "DELETE FROM {$type} WHERE id = :id";
        $stmt = $this->execute($sql, [':id' => $id]);
        
        if ($stmt->rowCount() > 0) {
            // Delete associated files
            if ($content) {
                if (isset($content['image_url']) && $content['image_url']) {
                    $this->deleteFile($content['image_url']);
                }
                if (isset($content['photo_url']) && $content['photo_url']) {
                    $this->deleteFile($content['photo_url']);
                }
            }
            
            if (function_exists('logMessage')) {
                logMessage('INFO', 'Content rejected and deleted', ['type' => $type, 'id' => $id]);
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Set content as featured
     */
    public function setFeatured($type, $id, $featured = true) {
        $allowedTypes = ['achievements', 'events', 'news', 'gallery'];
        
        if (!in_array($type, $allowedTypes)) {
            return false;
        }
        
        $sql = "UPDATE {$type} SET is_featured = :featured WHERE id = :id";
        $stmt = $this->execute($sql, [
            ':featured' => $featured ? 1 : 0,
            ':id' => $id
        ]);
        
        if ($stmt->rowCount() > 0) {
            if (function_exists('logMessage')) {
                logMessage('INFO', 'Content featured status updated', [
                    'type' => $type, 
                    'id' => $id, 
                    'featured' => $featured
                ]);
            }
            return true;
        }
        
        return false;
    }
    
    // =============================
    // SEARCH FUNCTIONALITY
    // =============================
    
    /**
     * Global content search across all tables
     */
    public function searchContent($query, $options = []) {
        $type = $options['type'] ?? null;
        $limit = $options['limit'] ?? 20;
        $approved_only = $options['approved_only'] ?? true;
        
        $results = [];
        $tables = $type ? [$type] : ['achievements', 'events', 'news', 'teachers'];
        
        foreach ($tables as $table) {
            try {
                $sql = "SELECT 
                            id, created_at, '{$table}' as content_type,
                            CASE 
                                WHEN '{$table}' = 'teachers' THEN name
                                ELSE title 
                            END as title,
                            CASE 
                                WHEN '{$table}' = 'teachers' THEN CONCAT(qualification, ' - ', subject)
                                WHEN '{$table}' = 'news' THEN content
                                ELSE description 
                            END as description,
                            CASE 
                                WHEN '{$table}' = 'teachers' THEN photo_url
                                ELSE image_url 
                            END as image_url
                        FROM {$table} 
                        WHERE ";
                
                $conditions = [];
                $params = [':query' => "%{$query}%"];
                
                if ($approved_only) {
                    $conditions[] = "is_approved = 1";
                }
                
                if ($table === 'teachers') {
                    $conditions[] = "is_active = 1";
                    $conditions[] = "(name LIKE :query OR qualification LIKE :query OR subject LIKE :query OR bio LIKE :query)";
                } else {
                    $conditions[] = "(title LIKE :query OR description LIKE :query OR content LIKE :query)";
                }
                
                $sql .= implode(" AND ", $conditions);
                $sql .= " ORDER BY created_at DESC LIMIT :limit";
                $params[':limit'] = $limit;
                
                $stmt = $this->execute($sql, $params);
                $tableResults = $stmt->fetchAll();
                
                $results = array_merge($results, $tableResults);
                
            } catch (Exception $e) {
                if (function_exists('logMessage')) {
                    logMessage('WARNING', 'Search failed for table', ['table' => $table, 'error' => $e->getMessage()]);
                }
            }
        }
        
        // Sort by relevance and date
        usort($results, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($results, 0, $limit);
    }
    
    // =============================
    // UTILITY & HELPER METHODS
    // =============================
    
    /**
     * Generate excerpt from content
     */
    private function generateExcerpt($content, $length = 150) {
        $content = strip_tags($content);
        $content = trim(preg_replace('/\s+/', ' ', $content));
        
        if (strlen($content) <= $length) {
            return $content;
        }
        
        $excerpt = substr($content, 0, $length);
        $lastSpace = strrpos($excerpt, ' ');
        
        if ($lastSpace !== false) {
            $excerpt = substr($excerpt, 0, $lastSpace);
        }
        
        return $excerpt . '...';
    }
    
    /**
     * Delete file from filesystem
     */
    private function deleteFile($filePath) {
        if (!$filePath) return false;
        
        $fullPath = '../' . ltrim($filePath, '/');
        
        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
    
    /**
     * Get content by Telegram message ID
     */
    public function getContentByTelegramId($messageId) {
        $tables = ['achievements', 'events', 'news', 'teachers', 'gallery'];
        
        foreach ($tables as $table) {
            try {
                $sql = "SELECT *, '{$table}' as content_type FROM {$table} WHERE telegram_message_id = :message_id";
                $stmt = $this->execute($sql, [':message_id' => $messageId]);
                $result = $stmt->fetch();
                
                if ($result) {
                    return $result;
                }
            } catch (Exception $e) {
                if (function_exists('logMessage')) {
                    logMessage('WARNING', 'Failed to search by telegram ID', ['table' => $table, 'id' => $messageId]);
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get featured content across all types
     */
    public function getFeaturedContent($limit = 6) {
        try {
            $sql = "SELECT 
                        'achievement' as type, id, title, description, image_url, created_at, category
                    FROM achievements 
                    WHERE is_approved = 1 AND is_featured = 1
                    UNION ALL
                    SELECT 
                        'event' as type, id, title, description, image_url, created_at, category
                    FROM events 
                    WHERE is_approved = 1 AND is_featured = 1
                    UNION ALL
                    SELECT 
                        'news' as type, id, title, content as description, image_url, created_at, category
                    FROM news 
                    WHERE is_approved = 1 AND is_featured = 1
                    ORDER BY created_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->execute($sql, [':limit' => $limit]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            if (function_exists('logMessage')) {
                logMessage('WARNING', 'Failed to get featured content', ['error' => $e->getMessage()]);
            }
            return [];
        }
    }
    
    // =============================
    // SETTINGS MANAGEMENT
    // =============================
    
    /**
     * Get setting value
     */
    public function getSetting($key, $default = null) {
        try {
            $sql = "SELECT setting_value, setting_type FROM settings WHERE setting_key = :key";
            $stmt = $this->execute($sql, [':key' => $key]);
            $setting = $stmt->fetch();
            
            if (!$setting) {
                return $default;
            }
            
            $value = $setting['setting_value'];
            
            // Convert based on type
            switch ($setting['setting_type']) {
                case 'boolean':
                    return (bool)$value;
                case 'number':
                    return is_numeric($value) ? (float)$value : $default;
                case 'json':
                    return json_decode($value, true) ?: $default;
                default:
                    return $value;
            }
        } catch (Exception $e) {
            return $default;
        }
    }
    
    /**
     * Set setting value
     */
    public function setSetting($key, $value, $type = 'text') {
        try {
            // Convert value based on type
            switch ($type) {
                case 'boolean':
                    $value = $value ? '1' : '0';
                    break;
                case 'json':
                    $value = json_encode($value);
                    break;
                default:
                    $value = (string)$value;
            }
            
            $sql = "INSERT INTO settings (setting_key, setting_value, setting_type) 
                    VALUES (:key, :value, :type)
                    ON DUPLICATE KEY UPDATE 
                    setting_value = :value, setting_type = :type, updated_at = NOW()";
            
            $stmt = $this->execute($sql, [
                ':key' => $key,
                ':value' => $value,
                ':type' => $type
            ]);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // =============================
    // TRANSACTION METHODS
    // =============================
    
    /**
     * Begin database transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Execute multiple operations in a transaction
     */
    public function executeTransaction(callable $operations) {
        try {
            $this->beginTransaction();
            
            $result = $operations($this);
            
            $this->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->rollback();
            if (function_exists('logMessage')) {
                logMessage('ERROR', 'Transaction failed and rolled back', ['error' => $e->getMessage()]);
            }
            throw $e;
        }
    }
    
    // =============================
    // MAINTENANCE METHODS
    // =============================
    
    /**
     * Clean up old records (for maintenance)
     */
    public function cleanupOldRecords($days = 365) {
        $tables = [
            'contact_submissions' => 'created_at',
            'audit_log' => 'created_at'
        ];
        
        $deletedCount = 0;
        
        foreach ($tables as $table => $dateColumn) {
            try {
                $sql = "DELETE FROM {$table} WHERE {$dateColumn} < DATE_SUB(NOW(), INTERVAL :days DAY)";
                $stmt = $this->execute($sql, [':days' => $days]);
                $deletedCount += $stmt->rowCount();
            } catch (Exception $e) {
                if (function_exists('logMessage')) {
                    logMessage('WARNING', 'Cleanup failed for table', ['table' => $table, 'error' => $e->getMessage()]);
                }
            }
        }
        
        if (function_exists('logMessage')) {
            logMessage('INFO', 'Cleanup completed', ['deleted_records' => $deletedCount]);
        }
        return $deletedCount;
    }
    
    /**
     * Create database backup
     */
    public function createBackup() {
        if (!defined('BACKUP_PATH')) {
            return false;
        }
        
        $backupFile = BACKUP_PATH . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($this->host),
            escapeshellarg($this->username),
            escapeshellarg($this->password),
            escapeshellarg($this->dbname),
            escapeshellarg($backupFile)
        );
        
        $returnVar = null;
        $output = [];
        exec($command, $output, $returnVar);
        
        if ($returnVar === 0 && file_exists($backupFile)) {
            if (function_exists('logMessage')) {
                logMessage('INFO', 'Database backup created', ['file' => $backupFile]);
            }
            return $backupFile;
        }
        
        if (function_exists('logMessage')) {
            logMessage('ERROR', 'Database backup failed', ['command' => $command, 'return_var' => $returnVar]);
        }
        return false;
    }
    
    // =============================
    // CLEANUP AND DESTRUCTION
    // =============================
    
    /**
     * Close database connection
     */
    public function close() {
        $this->pdo = null;
    }
    
    /**
     * Destructor
     */
    public function __destruct() {
        $this->close();
    }
}

/**
 * Database connection singleton for global access
 */
class DatabaseManager {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    private function __construct() {}
    private function __clone() {}
}

// =============================
// GLOBAL HELPER FUNCTIONS (FIXED)
// =============================

/**
 * Get database instance
 */
function getDB() {
    return DatabaseManager::getInstance();
}

/**
 * Execute a query safely
 */
function executeQuery($sql, $params = []) {
    return getDB()->executeQuery($sql, $params);
}

/**
 * Get content statistics
 */
function getContentStats() {
    return getDB()->getContentStats();
}

/**
 * Quick database query function
 */
function dbQuery($sql, $params = []) {
    return getDB()->query($sql, $params);
}

/**
 * Quick single result query function
 */
function dbQueryOne($sql, $params = []) {
    return getDB()->queryOne($sql, $params);
}

/**
 * Insert data helper
 */
function dbInsert($table, $data) {
    $db = getDB();
    
    switch ($table) {
        case 'achievements':
            return $db->insertAchievement($data);
        case 'events':
            return $db->insertEvent($data);
        case 'news':
            return $db->insertNews($data);
        case 'teachers':
            return $db->insertTeacher($data);
        case 'gallery':
            return $db->insertGalleryImage($data);
        default:
            return false;
    }
}

/**
 * Search content helper
 */
function searchContent($query, $options = []) {
    return getDB()->searchContent($query, $options);
}

/**
 * Get featured content helper
 */
function getFeaturedContent($limit = 6) {
    return getDB()->getFeaturedContent($limit);
}

/**
 * Approve content helper
 */
function approveContent($type, $id) {
    return getDB()->approveContent($type, $id);
}

/**
 * Safe database operation with error handling
 */
function safeDbOperation(callable $operation) {
    try {
        return $operation(getDB());
    } catch (Exception $e) {
        if (function_exists('logMessage')) {
            logMessage('ERROR', 'Database operation failed', ['error' => $e->getMessage()]);
        }
        return false;
    }
}

?>