<?php
/**
 * Telegram Webhook Handler for Makalanegama School Website
 * Processes incoming messages and media from Telegram bot
 */

require_once 'config.php';
require_once 'database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

class TelegramWebhook {
    private $bot_token;
    private $webhook_secret;
    private $db;
    
    public function __construct() {
        $this->bot_token = TELEGRAM_BOT_TOKEN;
        $this->webhook_secret = TELEGRAM_WEBHOOK_SECRET;
        $this->db = new Database();
    }
    
    /**
     * Process incoming webhook
     */
    public function processWebhook() {
        try {
            // Verify webhook secret if provided
            if (!empty($this->webhook_secret)) {
                $received_secret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
                if ($received_secret !== $this->webhook_secret) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Invalid secret token']);
                    return;
                }
            }
            
            // Get webhook data
            $input = file_get_contents('php://input');
            $update = json_decode($input, true);
            
            if (!$update) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON']);
                return;
            }
            
            // Log incoming webhook for debugging
            $this->logWebhook($update);
            
            // Process the update
            $this->handleUpdate($update);
            
            echo json_encode(['status' => 'ok']);
            
        } catch (Exception $e) {
            error_log("Telegram Webhook Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
    
    /**
     * Handle different types of updates
     */
    private function handleUpdate($update) {
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['edited_message'])) {
            $this->handleEditedMessage($update['edited_message']);
        } elseif (isset($update['channel_post'])) {
            $this->handleChannelPost($update['channel_post']);
        }
    }
    
    /**
     * Handle regular messages
     */
    private function handleMessage($message) {
        $chat_id = $message['chat']['id'];
        $message_id = $message['message_id'];
        $user_id = $message['from']['id'] ?? null;
        $text = $message['text'] ?? '';
        $caption = $message['caption'] ?? '';
        
        // Check if user is authorized
        if (!$this->isAuthorizedUser($user_id)) {
            $this->sendMessage($chat_id, "❌ You are not authorized to post content to the website.");
            return;
        }
        
        // Handle commands
        if (strpos($text, '/') === 0) {
            $this->handleCommand($message);
            return;
        }
        
        // Handle media with captions
        if (isset($message['photo']) || isset($message['document'])) {
            $this->handleMediaMessage($message);
            return;
        }
        
        // Handle text messages
        if (!empty($text)) {
            $this->handleTextMessage($message);
        }
    }
    
    /**
     * Handle bot commands
     */
    private function handleCommand($message) {
        $text = $message['text'];
        $chat_id = $message['chat']['id'];
        $parts = explode(' ', $text, 2);
        $command = $parts[0];
        $args = $parts[1] ?? '';
        
        switch ($command) {
            case '/start':
                $this->sendWelcomeMessage($chat_id);
                break;
                
            case '/help':
                $this->sendHelpMessage($chat_id);
                break;
                
            case '/status':
                $this->sendStatusMessage($chat_id);
                break;
                
            case '/achievement':
                $this->sendMessage($chat_id, "📚 To add an achievement, send a photo with caption in format:\n/achievement Title - Description");
                break;
                
            case '/event':
                $this->sendMessage($chat_id, "📅 To add an event, send a photo with caption in format:\n/event Title - Date - Description");
                break;
                
            case '/news':
                $this->sendMessage($chat_id, "📰 To add news, send a photo with caption in format:\n/news Title - Description");
                break;
                
            case '/teacher':
                $this->sendMessage($chat_id, "👨‍🏫 To add a teacher, send a photo with caption in format:\n/teacher Name - Qualification - Subject");
                break;
                
            default:
                $this->sendMessage($chat_id, "❓ Unknown command. Type /help for available commands.");
        }
    }
    
    /**
     * Handle media messages (photos, documents)
     */
    private function handleMediaMessage($message) {
        $chat_id = $message['chat']['id'];
        $caption = $message['caption'] ?? '';
        $message_id = $message['message_id'];
        
        if (empty($caption)) {
            $this->sendMessage($chat_id, "⚠️ Please provide a caption with your media. Type /help for format examples.");
            return;
        }
        
        // Parse caption to determine content type
        $content_type = $this->parseContentType($caption);
        
        if (!$content_type) {
            $this->sendMessage($chat_id, "⚠️ Invalid caption format. Type /help for format examples.");
            return;
        }
        
        // Download and process media
        $media_info = $this->processMedia($message);
        
        if (!$media_info) {
            $this->sendMessage($chat_id, "❌ Failed to process media file.");
            return;
        }
        
        // Save content to database
        $result = $this->saveContent($content_type, $caption, $media_info, $message);
        
        if ($result) {
            $this->sendMessage($chat_id, "✅ Content added successfully to the website!");
        } else {
            $this->sendMessage($chat_id, "❌ Failed to save content. Please try again.");
        }
    }
    
    /**
     * Parse content type from caption
     */
    private function parseContentType($caption) {
        if (strpos($caption, '/achievement') === 0) return 'achievement';
        if (strpos($caption, '/event') === 0) return 'event';
        if (strpos($caption, '/news') === 0) return 'news';
        if (strpos($caption, '/teacher') === 0) return 'teacher';
        if (strpos($caption, '/gallery') === 0) return 'gallery';
        
        return null;
    }
    
    /**
     * Process and download media files
     */
    private function processMedia($message) {
        $file_id = null;
        $file_type = null;
        
        // Determine file type and get file_id
        if (isset($message['photo'])) {
            $photos = $message['photo'];
            $file_id = end($photos)['file_id']; // Get highest resolution
            $file_type = 'photo';
        } elseif (isset($message['document'])) {
            $file_id = $message['document']['file_id'];
            $file_type = 'document';
        }
        
        if (!$file_id) {
            return null;
        }
        
        // Get file info from Telegram
        $file_info = $this->getFileInfo($file_id);
        if (!$file_info) {
            return null;
        }
        
        // Download file
        $local_path = $this->downloadFile($file_info);
        if (!$local_path) {
            return null;
        }
        
        return [
            'file_id' => $file_id,
            'file_type' => $file_type,
            'local_path' => $local_path,
            'original_name' => $message['document']['file_name'] ?? 'image.jpg'
        ];
    }
    
    /**
     * Get file information from Telegram
     */
    private function getFileInfo($file_id) {
        $url = "https://api.telegram.org/bot{$this->bot_token}/getFile?file_id={$file_id}";
        
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        
        return $data['ok'] ? $data['result'] : null;
    }
    
    /**
     * Download file from Telegram servers
     */
    private function downloadFile($file_info) {
        $file_path = $file_info['file_path'];
        $url = "https://api.telegram.org/file/bot{$this->bot_token}/{$file_path}";
        
        // Create uploads directory if it doesn't exist
        $upload_dir = '../assets/uploads/' . date('Y/m/');
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file_path, PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $local_path = $upload_dir . $filename;
        
        // Download file
        $file_content = file_get_contents($url);
        if ($file_content === false) {
            return null;
        }
        
        // Save file locally
        if (file_put_contents($local_path, $file_content) === false) {
            return null;
        }
        
        // Optimize image if it's a photo
        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp'])) {
            $this->optimizeImage($local_path);
        }
        
        return str_replace('../', '', $local_path);
    }
    
    /**
     * Optimize uploaded images
     */
    private function optimizeImage($file_path) {
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        // Load image based on type
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($file_path);
                break;
            case 'png':
                $image = imagecreatefrompng($file_path);
                break;
            case 'webp':
                $image = imagecreatefromwebp($file_path);
                break;
            default:
                return;
        }
        
        if (!$image) return;
        
        // Get original dimensions
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Resize if too large
        $max_width = 1200;
        $max_height = 800;
        
        if ($width > $max_width || $height > $max_height) {
            $ratio = min($max_width / $width, $max_height / $height);
            $new_width = (int)($width * $ratio);
            $new_height = (int)($height * $ratio);
            
            $resized = imagecreatetruecolor($new_width, $new_height);
            
            // Preserve transparency for PNG
            if ($extension === 'png') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                imagefill($resized, 0, 0, $transparent);
            }
            
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            
            // Save optimized image
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($resized, $file_path, 85);
                    break;
                case 'png':
                    imagepng($resized, $file_path, 8);
                    break;
                case 'webp':
                    imagewebp($resized, $file_path, 85);
                    break;
            }
            
            imagedestroy($resized);
        }
        
        imagedestroy($image);
    }
    
    /**
     * Save content to database
     */
    private function saveContent($type, $caption, $media_info, $message) {
        $parsed_data = $this->parseCaption($type, $caption);
        if (!$parsed_data) {
            return false;
        }
        
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => $parse_mode
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ],
        ];
        
        $context = stream_context_create($options);
        file_get_contents($url, false, $context);
    }
    
    /**
     * Send welcome message
     */
    private function sendWelcomeMessage($chat_id) {
        $message = "🏫 <b>Welcome to Makalanegama School Content Management Bot!</b>\n\n";
        $message .= "This bot allows you to easily add content to the school website.\n\n";
        $message .= "Available commands:\n";
        $message .= "📚 <b>/achievement</b> - Add school achievements\n";
        $message .= "📅 <b>/event</b> - Add upcoming events\n";
        $message .= "📰 <b>/news</b> - Add news updates\n";
        $message .= "👨‍🏫 <b>/teacher</b> - Add teacher profiles\n";
        $message .= "🖼 <b>/gallery</b> - Add gallery images\n";
        $message .= "ℹ️ <b>/help</b> - Show detailed help\n";
        $message .= "📊 <b>/status</b> - Check system status\n\n";
        $message .= "Type /help for detailed format instructions.";
        
        $this->sendMessage($chat_id, $message);
    }
    
    /**
     * Send help message
     */
    private function sendHelpMessage($chat_id) {
        $message = "📖 <b>Content Format Guide</b>\n\n";
        
        $message .= "📚 <b>ACHIEVEMENTS</b>\n";
        $message .= "Send photo with caption:\n";
        $message .= "<code>/achievement Title - Description - Category</code>\n";
        $message .= "Example: <code>/achievement Math Competition Win - Our students won first place - Academic</code>\n\n";
        
        $message .= "📅 <b>EVENTS</b>\n";
        $message .= "Send photo with caption:\n";
        $message .= "<code>/event Title - Date - Description - Location</code>\n";
        $message .= "Example: <code>/event Sports Day - 2024-03-15 - Annual athletics competition - School Grounds</code>\n\n";
        
        $message .= "📰 <b>NEWS</b>\n";
        $message .= "Send photo with caption:\n";
        $message .= "<code>/news Title - Description - Category - Author</code>\n";
        $message .= "Example: <code>/news New Computer Lab - Lab officially opened - Facilities - Principal</code>\n\n";
        
        $message .= "👨‍🏫 <b>TEACHERS</b>\n";
        $message .= "Send photo with caption:\n";
        $message .= "<code>/teacher Name - Qualification - Subject - Department</code>\n";
        $message .= "Example: <code>/teacher Mr. John Doe - B.Sc Mathematics - Mathematics - Science</code>\n\n";
        
        $message .= "🖼 <b>GALLERY</b>\n";
        $message .= "Send photo with caption:\n";
        $message .= "<code>/gallery Description - Category</code>\n";
        $message .= "Example: <code>/gallery Science experiment in progress - Academic</code>\n\n";
        
        $message .= "⚠️ <b>Important Notes:</b>\n";
        $message .= "• Always include a photo with your caption\n";
        $message .= "• Use ' - ' (space-dash-space) to separate fields\n";
        $message .= "• Some fields are optional (check examples)\n";
        $message .= "• Images will be automatically optimized";
        
        $this->sendMessage($chat_id, $message);
    }
    
    /**
     * Send status message
     */
    private function sendStatusMessage($chat_id) {
        $stats = $this->db->getContentStats();
        
        $message = "📊 <b>Website Content Statistics</b>\n\n";
        $message .= "📚 Achievements: {$stats['achievements']}\n";
        $message .= "📅 Events: {$stats['events']}\n";
        $message .= "📰 News Articles: {$stats['news']}\n";
        $message .= "👨‍🏫 Teachers: {$stats['teachers']}\n";
        $message .= "🖼 Gallery Images: {$stats['gallery']}\n\n";
        $message .= "🕒 Last Updated: " . date('Y-m-d H:i:s') . "\n";
        $message .= "✅ System Status: Online";
        
        $this->sendMessage($chat_id, $message);
    }
    
    /**
     * Check if user is authorized
     */
    private function isAuthorizedUser($user_id) {
        $authorized_users = TELEGRAM_AUTHORIZED_USERS;
        return in_array($user_id, $authorized_users);
    }
    
    /**
     * Log webhook for debugging
     */
    private function logWebhook($update) {
        if (ENVIRONMENT === 'development') {
            $log_entry = date('Y-m-d H:i:s') . " - " . json_encode($update) . "\n";
            file_put_contents('../logs/telegram_webhook.log', $log_entry, FILE_APPEND | LOCK_EX);
        }
    }
    
    /**
     * Handle edited messages
     */
    private function handleEditedMessage($message) {
        // For now, we'll treat edited messages the same as new messages
        $this->handleMessage($message);
    }
    
    /**
     * Handle channel posts
     */
    private function handleChannelPost($post) {
        // Handle posts from channels if needed
        // This can be used for automated content from official school channels
        if (isset($post['photo']) && isset($post['caption'])) {
            $this->handleMediaMessage($post);
        }
    }
}

// Main execution
try {
    $webhook = new TelegramWebhook();
    $webhook->processWebhook();
} catch (Exception $e) {
    error_log("Fatal error in Telegram webhook: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>
            'title' => $parsed_data['title'],
            'description' => $parsed_data['description'],
            'image_url' => $media_info['local_path'],
            'telegram_message_id' => $message['message_id'],
            'telegram_user_id' => $message['from']['id'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        switch ($type) {
            case 'achievement':
                $data['category'] = $parsed_data['category'] ?? 'General';
                return $this->db->insertAchievement($data);
                
            case 'event':
                $data['event_date'] = $parsed_data['date'] ?? date('Y-m-d');
                $data['location'] = $parsed_data['location'] ?? 'School';
                return $this->db->insertEvent($data);
                
            case 'news':
                $data['category'] = $parsed_data['category'] ?? 'General';
                $data['author'] = $parsed_data['author'] ?? 'Administration';
                return $this->db->insertNews($data);
                
            case 'teacher':
                $data['name'] = $parsed_data['name'];
                $data['qualification'] = $parsed_data['qualification'];
                $data['subject'] = $parsed_data['subject'];
                $data['department'] = $parsed_data['department'] ?? 'General';
                return $this->db->insertTeacher($data);
                
            case 'gallery':
                $data['category'] = $parsed_data['category'] ?? 'General';
                return $this->db->insertGalleryImage($data);
        }
        
        return false;
    }
    
    /**
     * Parse caption based on content type
     */
    private function parseCaption($type, $caption) {
        // Remove command from caption
        $caption = preg_replace('/^\/\w+\s*/', '', $caption);
        
        switch ($type) {
            case 'achievement':
                // Format: Title - Description [- Category]
                $parts = explode(' - ', $caption, 3);
                return [
                    'title' => trim($parts[0] ?? ''),
                    'description' => trim($parts[1] ?? ''),
                    'category' => trim($parts[2] ?? 'Academic')
                ];
                
            case 'event':
                // Format: Title - Date - Description [- Location]
                $parts = explode(' - ', $caption, 4);
                return [
                    'title' => trim($parts[0] ?? ''),
                    'date' => trim($parts[1] ?? ''),
                    'description' => trim($parts[2] ?? ''),
                    'location' => trim($parts[3] ?? 'School')
                ];
                
            case 'news':
                // Format: Title - Description [- Category] [- Author]
                $parts = explode(' - ', $caption, 4);
                return [
                    'title' => trim($parts[0] ?? ''),
                    'description' => trim($parts[1] ?? ''),
                    'category' => trim($parts[2] ?? 'General'),
                    'author' => trim($parts[3] ?? 'Administration')
                ];
                
            case 'teacher':
                // Format: Name - Qualification - Subject [- Department]
                $parts = explode(' - ', $caption, 4);
                return [
                    'name' => trim($parts[0] ?? ''),
                    'qualification' => trim($parts[1] ?? ''),
                    'subject' => trim($parts[2] ?? ''),
                    'department' => trim($parts[3] ?? 'General')
                ];
                
            case 'gallery':
                // Format: Description [- Category]
                $parts = explode(' - ', $caption, 2);
                return [
                    'title' => trim($parts[0] ?? ''),
                    'description' => trim($parts[0] ?? ''),
                    'category' => trim($parts[1] ?? 'General')
                ];
        }
        
        return null;
    }
    
    /**
     * Send message to Telegram chat
     */
    private function sendMessage($chat_id, $text, $parse_mode = 'HTML') {
        $url = "https://api.telegram.org/bot{$this->bot_token}/sendMessage";
        
        $data =