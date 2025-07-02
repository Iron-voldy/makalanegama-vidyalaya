<?php
/**
 * Complete Telegram Webhook Handler for Makalanegama School Website
 * Processes incoming messages and media from Telegram bot
 * 
 * Setup Instructions:
 * 1. Create a Telegram bot using @BotFather
 * 2. Get your bot token and add it to config.php
 * 3. Set webhook URL: https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://yoursite.com/admin/telegram-webhook.php
 * 4. Add authorized user IDs to config.php
 * 5. Test by sending /start to your bot
 */

// Prevent direct access without proper setup
if (!file_exists('config.php')) {
    http_response_code(500);
    die('Configuration file not found. Please set up config.php first.');
}

require_once 'config.php';
require_once 'database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

class TelegramWebhook {
    private $bot_token;
    private $webhook_secret;
    private $db;
    private $authorized_users;
    
    public function __construct() {
        $this->bot_token = TELEGRAM_BOT_TOKEN;
        $this->webhook_secret = TELEGRAM_WEBHOOK_SECRET ?? '';
        $this->authorized_users = TELEGRAM_AUTHORIZED_USERS;
        
        try {
            $this->db = new Database();
        } catch (Exception $e) {
            $this->logError("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    /**
     * Main webhook processing method
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
            if (empty($input)) {
                http_response_code(400);
                echo json_encode(['error' => 'No input data']);
                return;
            }
            
            $update = json_decode($input, true);
            if (!$update || json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON: ' . json_last_error_msg()]);
                return;
            }
            
            // Log incoming webhook for debugging
            $this->logWebhook($update);
            
            // Process the update
            $this->handleUpdate($update);
            
            echo json_encode(['status' => 'ok']);
            
        } catch (Exception $e) {
            $this->logError("Telegram Webhook Error: " . $e->getMessage());
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
        } elseif (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
        }
    }
    
    /**
     * Handle regular messages
     */
    private function handleMessage($message) {
        $chat_id = $message['chat']['id'];
        $message_id = $message['message_id'];
        $user_id = $message['from']['id'] ?? null;
        $username = $message['from']['username'] ?? 'Unknown';
        $first_name = $message['from']['first_name'] ?? 'User';
        $text = $message['text'] ?? '';
        $caption = $message['caption'] ?? '';
        
        // Log message details
        $this->logMessage("Received message from user $user_id ($username): " . ($text ?: $caption));
        
        // Check if user is authorized
        if (!$this->isAuthorizedUser($user_id)) {
            $this->sendMessage($chat_id, "❌ <b>Access Denied</b>\n\nYou are not authorized to post content to the website.\n\nContact the school administrator to get access.", 'HTML');
            $this->logMessage("Unauthorized access attempt from user $user_id ($username)");
            return;
        }
        
        // Handle commands
        if (strpos($text, '/') === 0) {
            $this->handleCommand($message);
            return;
        }
        
        // Handle media with captions
        if (isset($message['photo']) || isset($message['document']) || isset($message['video'])) {
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
        $user_id = $message['from']['id'];
        $first_name = $message['from']['first_name'] ?? 'User';
        
        $parts = explode(' ', $text, 2);
        $command = strtolower($parts[0]);
        $args = $parts[1] ?? '';
        
        $this->logMessage("Processing command: $command from user $user_id");
        
        switch ($command) {
            case '/start':
                $this->sendWelcomeMessage($chat_id, $first_name);
                break;
                
            case '/help':
                $this->sendHelpMessage($chat_id);
                break;
                
            case '/status':
                $this->sendStatusMessage($chat_id);
                break;
                
            case '/achievement':
                $this->sendFormatHelp($chat_id, 'achievement');
                break;
                
            case '/event':
                $this->sendFormatHelp($chat_id, 'event');
                break;
                
            case '/news':
                $this->sendFormatHelp($chat_id, 'news');
                break;
                
            case '/teacher':
                $this->sendFormatHelp($chat_id, 'teacher');
                break;
                
            case '/gallery':
                $this->sendFormatHelp($chat_id, 'gallery');
                break;
                
            case '/pending':
                $this->showPendingContent($chat_id);
                break;
                
            case '/approve':
                $this->handleApprovalCommand($chat_id, $args);
                break;
                
            case '/reject':
                $this->handleRejectionCommand($chat_id, $args);
                break;
                
            case '/backup':
                $this->createBackup($chat_id);
                break;
                
            default:
                $this->sendMessage($chat_id, "❓ <b>Unknown command:</b> $command\n\nType /help for available commands.", 'HTML');
        }
    }
    
    /**
     * Handle media messages (photos, documents, videos)
     */
    private function handleMediaMessage($message) {
        $chat_id = $message['chat']['id'];
        $caption = $message['caption'] ?? '';
        $message_id = $message['message_id'];
        $user_id = $message['from']['id'];
        
        if (empty($caption)) {
            $this->sendMessage($chat_id, 
                "⚠️ <b>Caption Required</b>\n\n" .
                "Please provide a caption with your media using one of these formats:\n\n" .
                "📚 <code>/achievement Title - Description - Category</code>\n" .
                "📅 <code>/event Title - Date - Description - Location</code>\n" .
                "📰 <code>/news Title - Description - Category</code>\n" .
                "👨‍🏫 <code>/teacher Name - Qualification - Subject</code>\n" .
                "🖼 <code>/gallery Description - Category</code>\n\n" .
                "Type /help for detailed examples.", 'HTML');
            return;
        }
        
        // Parse caption to determine content type
        $content_type = $this->parseContentType($caption);
        
        if (!$content_type) {
            $this->sendMessage($chat_id, 
                "⚠️ <b>Invalid Format</b>\n\n" .
                "Caption must start with a command:\n" .
                "/achievement, /event, /news, /teacher, or /gallery\n\n" .
                "Type /help for format examples.", 'HTML');
            return;
        }
        
        // Show processing message
        $processing_msg = $this->sendMessage($chat_id, "⏳ <b>Processing...</b>\n\nDownloading and optimizing media file...", 'HTML');
        
        // Download and process media
        $media_info = $this->processMedia($message);
        
        if (!$media_info) {
            $this->editMessage($chat_id, $processing_msg['message_id'], 
                "❌ <b>Media Processing Failed</b>\n\n" .
                "Could not download or process the media file.\n" .
                "Please try again with a different image.", 'HTML');
            return;
        }
        
        // Parse caption content
        $parsed_data = $this->parseCaption($content_type, $caption);
        if (!$parsed_data) {
            $this->editMessage($chat_id, $processing_msg['message_id'], 
                "❌ <b>Invalid Caption Format</b>\n\n" .
                "Please check the format and try again.\n" .
                "Type /help for examples.", 'HTML');
            return;
        }
        
        // Save content to database
        $result = $this->saveContent($content_type, $parsed_data, $media_info, $message);
        
        if ($result) {
            $approval_status = AUTO_APPROVE_CONTENT ? "✅ <b>Published</b>" : "⏳ <b>Pending Approval</b>";
            $this->editMessage($chat_id, $processing_msg['message_id'], 
                "✅ <b>Content Added Successfully!</b>\n\n" .
                "📄 <b>Type:</b> " . ucfirst($content_type) . "\n" .
                "📝 <b>Title:</b> " . $parsed_data['title'] . "\n" .
                "📊 <b>Status:</b> $approval_status\n\n" .
                "The content has been added to the website!", 'HTML');
                
            $this->logMessage("Content added successfully: $content_type by user $user_id");
            
            // Send notification to admins if approval required
            if (!AUTO_APPROVE_CONTENT) {
                $this->notifyAdmins($content_type, $parsed_data, $result);
            }
        } else {
            $this->editMessage($chat_id, $processing_msg['message_id'], 
                "❌ <b>Database Error</b>\n\n" .
                "Failed to save content to database.\n" .
                "Please try again or contact the administrator.", 'HTML');
            $this->logError("Failed to save content: $content_type by user $user_id");
        }
    }
    
    /**
     * Parse content type from caption
     */
    private function parseContentType($caption) {
        $caption = strtolower(trim($caption));
        
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
        $file_size = 0;
        
        // Determine file type and get file_id
        if (isset($message['photo'])) {
            $photos = $message['photo'];
            $largest_photo = end($photos); // Get highest resolution
            $file_id = $largest_photo['file_id'];
            $file_size = $largest_photo['file_size'] ?? 0;
            $file_type = 'photo';
        } elseif (isset($message['document'])) {
            $document = $message['document'];
            $file_id = $document['file_id'];
            $file_size = $document['file_size'] ?? 0;
            $file_type = 'document';
            
            // Check if document is an image
            $mime_type = $document['mime_type'] ?? '';
            if (strpos($mime_type, 'image/') === 0) {
                $file_type = 'image_document';
            }
        } elseif (isset($message['video'])) {
            $video = $message['video'];
            $file_id = $video['file_id'];
            $file_size = $video['file_size'] ?? 0;
            $file_type = 'video';
        }
        
        if (!$file_id) {
            $this->logError("No valid file_id found in message");
            return null;
        }
        
        // Check file size (Telegram Bot API limit is 20MB)
        if ($file_size > UPLOAD_MAX_SIZE) {
            $this->logError("File size too large: $file_size bytes");
            return null;
        }
        
        // Get file info from Telegram
        $file_info = $this->getFileInfo($file_id);
        if (!$file_info) {
            $this->logError("Failed to get file info for file_id: $file_id");
            return null;
        }
        
        // Download file
        $local_path = $this->downloadFile($file_info, $file_type);
        if (!$local_path) {
            $this->logError("Failed to download file: $file_id");
            return null;
        }
        
        return [
            'file_id' => $file_id,
            'file_type' => $file_type,
            'local_path' => $local_path,
            'file_size' => $file_size,
            'original_name' => $message['document']['file_name'] ?? 'telegram_file.jpg'
        ];
    }
    
    /**
     * Get file information from Telegram
     */
    private function getFileInfo($file_id) {
        $url = "https://api.telegram.org/bot{$this->bot_token}/getFile?file_id=" . urlencode($file_id);
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'method' => 'GET'
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        if ($response === false) {
            $this->logError("Failed to get file info from Telegram API");
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !$data['ok']) {
            $this->logError("Telegram API error: " . ($data['description'] ?? 'Unknown error'));
            return null;
        }
        
        return $data['result'];
    }
    
    /**
     * Download file from Telegram servers
     */
    private function downloadFile($file_info, $file_type) {
        $file_path = $file_info['file_path'];
        $url = "https://api.telegram.org/file/bot{$this->bot_token}/" . $file_path;
        
        // Create uploads directory structure
        $upload_dir = '../assets/uploads/' . date('Y/m/');
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $this->logError("Failed to create upload directory: $upload_dir");
                return null;
            }
        }
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        // Validate file extension
        if (!in_array($extension, ALLOWED_IMAGE_TYPES) && !in_array($extension, ALLOWED_DOCUMENT_TYPES)) {
            $this->logError("Invalid file extension: $extension");
            return null;
        }
        
        $filename = uniqid('telegram_', true) . '.' . $extension;
        $local_path = $upload_dir . $filename;
        
        // Download file with timeout
        $context = stream_context_create([
            'http' => [
                'timeout' => 60,
                'method' => 'GET'
            ]
        ]);
        
        $file_content = file_get_contents($url, false, $context);
        if ($file_content === false) {
            $this->logError("Failed to download file from Telegram servers");
            return null;
        }
        
        // Save file locally
        if (file_put_contents($local_path, $file_content) === false) {
            $this->logError("Failed to save file locally: $local_path");
            return null;
        }
        
        // Optimize image if it's a photo
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp']) && $file_type !== 'video') {
            $this->optimizeImage($local_path);
        }
        
        // Return relative path for database storage
        return str_replace('../', '', $local_path);
    }
    
    /**
     * Optimize uploaded images
     */
    private function optimizeImage($file_path) {
        if (!extension_loaded('gd')) {
            $this->logError("GD extension not available for image optimization");
            return false;
        }
        
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        // Load image based on type
        $image = null;
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = @imagecreatefromjpeg($file_path);
                break;
            case 'png':
                $image = @imagecreatefrompng($file_path);
                break;
            case 'webp':
                $image = @imagecreatefromwebp($file_path);
                break;
            default:
                return false;
        }
        
        if (!$image) {
            $this->logError("Failed to load image for optimization: $file_path");
            return false;
        }
        
        // Get original dimensions
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Resize if too large
        $max_width = MAX_IMAGE_WIDTH;
        $max_height = MAX_IMAGE_HEIGHT;
        
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
                    imagejpeg($resized, $file_path, JPEG_QUALITY);
                    break;
                case 'png':
                    imagepng($resized, $file_path, PNG_COMPRESSION);
                    break;
                case 'webp':
                    imagewebp($resized, $file_path, WEBP_QUALITY);
                    break;
            }
            
            imagedestroy($resized);
            $this->logMessage("Image optimized: {$width}x{$height} -> {$new_width}x{$new_height}");
        }
        
        imagedestroy($image);
        return true;
    }
    
    /**
     * Save content to database
     */
    private function saveContent($type, $parsed_data, $media_info, $message) {
        try {
            $data = [
                'title' => $parsed_data['title'],
                'description' => $parsed_data['description'],
                'image_url' => $media_info['local_path'],
                'telegram_message_id' => $message['message_id'],
                'telegram_user_id' => $message['from']['id'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            switch ($type) {
                case 'achievement':
                    $data['category'] = $parsed_data['category'] ?? 'Academic';
                    return $this->db->insertAchievement($data);
                    
                case 'event':
                    $data['event_date'] = $parsed_data['date'] ?? date('Y-m-d');
                    $data['location'] = $parsed_data['location'] ?? 'School';
                    $data['event_time'] = $parsed_data['time'] ?? null;
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
                    $data['bio'] = $parsed_data['description'];
                    $data['photo_url'] = $media_info['local_path'];
                    unset($data['image_url']); // Teachers use photo_url
                    return $this->db->insertTeacher($data);
                    
                case 'gallery':
                    $data['category'] = $parsed_data['category'] ?? 'General';
                    return $this->db->insertGalleryImage($data);
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logError("Database error while saving content: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Parse caption based on content type
     */
    private function parseCaption($type, $caption) {
        // Remove command from caption
        $caption = preg_replace('/^\/\w+\s*/', '', $caption);
        $caption = trim($caption);
        
        if (empty($caption)) {
            return null;
        }
        
        switch ($type) {
            case 'achievement':
                // Format: Title - Description [- Category]
                $parts = explode(' - ', $caption, 3);
                if (count($parts) < 2) return null;
                
                return [
                    'title' => trim($parts[0]),
                    'description' => trim($parts[1]),
                    'category' => isset($parts[2]) ? trim($parts[2]) : 'Academic'
                ];
                
            case 'event':
                // Format: Title - Date - Description [- Location] [- Time]
                $parts = explode(' - ', $caption, 5);
                if (count($parts) < 3) return null;
                
                // Parse date
                $date = $this->parseDate(trim($parts[1]));
                
                return [
                    'title' => trim($parts[0]),
                    'date' => $date,
                    'description' => trim($parts[2]),
                    'location' => isset($parts[3]) ? trim($parts[3]) : 'School',
                    'time' => isset($parts[4]) ? $this->parseTime(trim($parts[4])) : null
                ];
                
            case 'news':
                // Format: Title - Description [- Category] [- Author]
                $parts = explode(' - ', $caption, 4);
                if (count($parts) < 2) return null;
                
                return [
                    'title' => trim($parts[0]),
                    'description' => trim($parts[1]),
                    'category' => isset($parts[2]) ? trim($parts[2]) : 'General',
                    'author' => isset($parts[3]) ? trim($parts[3]) : 'Administration'
                ];
                
            case 'teacher':
                // Format: Name - Qualification - Subject [- Department]
                $parts = explode(' - ', $caption, 4);
                if (count($parts) < 3) return null;
                
                return [
                    'name' => trim($parts[0]),
                    'qualification' => trim($parts[1]),
                    'subject' => trim($parts[2]),
                    'department' => isset($parts[3]) ? trim($parts[3]) : 'General',
                    'title' => trim($parts[0]), // For consistency with other content types
                    'description' => 'Teacher profile for ' . trim($parts[0])
                ];
                
            case 'gallery':
                // Format: Description [- Category]
                $parts = explode(' - ', $caption, 2);
                
                return [
                    'title' => trim($parts[0]),
                    'description' => trim($parts[0]),
                    'category' => isset($parts[1]) ? trim($parts[1]) : 'General'
                ];
        }
        
        return null;
    }
    
    /**
     * Parse date from string
     */
    private function parseDate($dateString) {
        // Try various date formats
        $formats = [
            'Y-m-d',     // 2024-03-15
            'd/m/Y',     // 15/03/2024
            'd-m-Y',     // 15-03-2024
            'm/d/Y',     // 03/15/2024
            'Y/m/d',     // 2024/03/15
            'd.m.Y',     // 15.03.2024
        ];
        
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $dateString);
            if ($date && $date->format($format) === $dateString) {
                return $date->format('Y-m-d');
            }
        }
        
        // If no format matches, try strtotime
        $timestamp = strtotime($dateString);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        // Default to current date if parsing fails
        return date('Y-m-d');
    }
    
    /**
     * Parse time from string
     */
    private function parseTime($timeString) {
        // Try various time formats
        $formats = [
            'H:i',       // 14:30
            'H:i:s',     // 14:30:00
            'g:i A',     // 2:30 PM
            'g:i a',     // 2:30 pm
        ];
        
        foreach ($formats as $format) {
            $time = DateTime::createFromFormat($format, $timeString);
            if ($time) {
                return $time->format('H:i:s');
            }
        }
        
        return null;
    }
    
    /**
     * Send message to Telegram chat
     */
    private function sendMessage($chat_id, $text, $parse_mode = 'HTML', $reply_markup = null) {
        $url = "https://api.telegram.org/bot{$this->bot_token}/sendMessage";
        
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => $parse_mode
        ];
        
        if ($reply_markup) {
            $data['reply_markup'] = json_encode($reply_markup);
        }
        
        return $this->makeApiRequest($url, $data);
    }
    
    /**
     * Edit message
     */
    private function editMessage($chat_id, $message_id, $text, $parse_mode = 'HTML') {
        $url = "https://api.telegram.org/bot{$this->bot_token}/editMessageText";
        
        $data = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text,
            'parse_mode' => $parse_mode
        ];
        
        return $this->makeApiRequest($url, $data);
    }
    
    /**
     * Make API request to Telegram
     */
    private function makeApiRequest($url, $data) {
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
                'timeout' => 30
            ],
        ];
        
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            $this->logError("Failed to make API request to: $url");
            return null;
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !$result['ok']) {
            $this->logError("Telegram API error: " . ($result['description'] ?? 'Unknown error'));
            return null;
        }
        
        return $result['result'];
    }
    
    /**
     * Send welcome message
     */
    private function sendWelcomeMessage($chat_id, $first_name) {
        $message = "🏫 <b>Welcome to Makalanegama School Content Management Bot!</b>\n\n";
        $message .= "Hello <b>$first_name</b>! 👋\n\n";
        $message .= "This bot allows you to easily add content to the school website by sending photos with captions.\n\n";
        $message .= "<b>📋 Available Commands:</b>\n";
        $message .= "📚 /achievement - Add school achievements\n";
        $message .= "📅 /event - Add upcoming events\n";
        $message .= "📰 /news - Add news updates\n";
        $message .= "👨‍🏫 /teacher - Add teacher profiles\n";
        $message .= "🖼 /gallery - Add gallery images\n";
        $message .= "📊 /status - Check system status\n";
        $message .= "📋 /pending - View pending approvals\n";
        $message .= "ℹ️ /help - Show detailed help\n\n";
        $message .= "<b>🚀 Quick Start:</b>\n";
        $message .= "1. Take a photo\n";
        $message .= "2. Add a caption with the format\n";
        $message .= "3. Send it to this bot\n";
        $message .= "4. Content appears on website!\n\n";
        $message .= "Type /help for detailed format instructions.";
        
        $this->sendMessage($chat_id, $message);
    }
    
    /**
     * Send comprehensive help message
     */
    private function sendHelpMessage($chat_id) {
        $message = "📖 <b>Complete Content Format Guide</b>\n\n";
        
        $message .= "📚 <b>ACHIEVEMENTS</b>\n";
        $message .= "📸 Send photo + caption:\n";
        $message .= "<code>/achievement Title - Description - Category</code>\n\n";
        $message .= "<b>Categories:</b> Academic, Sports, Cultural, Environmental, Technology, Arts, Science\n\n";
        $message .= "<b>Example:</b>\n<code>/achievement Math Competition Victory - Our Grade 10 students won first place in the provincial mathematics competition - Academic</code>\n\n";
        
        $message .= "📅 <b>EVENTS</b>\n";
        $message .= "📸 Send photo + caption:\n";
        $message .= "<code>/event Title - Date - Description - Location - Time</code>\n\n";
        $message .= "<b>Date formats:</b> YYYY-MM-DD, DD/MM/YYYY, DD-MM-YYYY\n";
        $message .= "<b>Time formats:</b> HH:MM, HH:MM AM/PM\n\n";
        $message .= "<b>Example:</b>\n<code>/event Annual Sports Day - 2024-03-15 - Athletic competitions and cultural performances - School Grounds - 08:00</code>\n\n";
        
        $message .= "📰 <b>NEWS</b>\n";
        $message .= "📸 Send photo + caption:\n";
        $message .= "<code>/news Title - Description - Category - Author</code>\n\n";
        $message .= "<b>Categories:</b> General, Academic, Sports, Events, Facilities, Announcements, Achievements, Admissions\n\n";
        $message .= "<b>Example:</b>\n<code>/news New Computer Lab Opens - State-of-the-art facility with 25 computers - Facilities - Principal</code>\n\n";
        
        $this->sendMessage($chat_id, $message);
        
        // Send second part of help
        $message2 = "👨‍🏫 <b>TEACHERS</b>\n";
        $message2 .= "📸 Send photo + caption:\n";
        $message2 .= "<code>/teacher Name - Qualification - Subject - Department</code>\n\n";
        $message2 .= "<b>Departments:</b> Science & Mathematics, Languages, Social Sciences, Arts, Physical Education, Technology\n\n";
        $message2 .= "<b>Example:</b>\n<code>/teacher Mr. John Silva - B.Sc Mathematics, B.Ed - Mathematics - Science & Mathematics</code>\n\n";
        
        $message2 .= "🖼 <b>GALLERY</b>\n";
        $message2 .= "📸 Send photo + caption:\n";
        $message2 .= "<code>/gallery Description - Category</code>\n\n";
        $message2 .= "<b>Categories:</b> Academic, Sports, Cultural, Events, Facilities, Environment, Technology\n\n";
        $message2 .= "<b>Example:</b>\n<code>/gallery Students conducting science experiment - Academic</code>\n\n";
        
        $message2 .= "⚠️ <b>Important Notes:</b>\n";
        $message2 .= "• Always include a photo with your caption\n";
        $message2 .= "• Use ' - ' (space-dash-space) to separate fields\n";
        $message2 .= "• Some fields are optional (Location, Time, Category, etc.)\n";
        $message2 .= "• Images are automatically optimized\n";
        $message2 .= "• Maximum file size: 10MB\n";
        $message2 .= "• Supported formats: JPG, PNG, WebP\n\n";
        
        $message2 .= "📞 <b>Need Help?</b>\n";
        $message2 .= "Contact the IT administrator if you have issues.";
        
        $this->sendMessage($chat_id, $message2);
    }
    
    /**
     * Send specific format help for a command
     */
    private function sendFormatHelp($chat_id, $type) {
        $messages = [
            'achievement' => [
                'title' => '📚 Achievement Format',
                'format' => '/achievement Title - Description - Category',
                'example' => '/achievement District Science Fair Winner - Our students won first place in district science competition - Academic',
                'categories' => 'Academic, Sports, Cultural, Environmental, Technology, Arts, Science'
            ],
            'event' => [
                'title' => '📅 Event Format',
                'format' => '/event Title - Date - Description - Location - Time',
                'example' => '/event Parent Teacher Meeting - 2024-03-20 - Discuss student progress - School Hall - 14:00',
                'note' => 'Date: YYYY-MM-DD format recommended\nTime: 24-hour format (HH:MM) or 12-hour with AM/PM'
            ],
            'news' => [
                'title' => '📰 News Format',
                'format' => '/news Title - Description - Category - Author',
                'example' => '/news Library Renovation Complete - Modern library with digital resources now open - Facilities - Principal',
                'categories' => 'General, Academic, Sports, Events, Facilities, Announcements, Achievements, Admissions'
            ],
            'teacher' => [
                'title' => '👨‍🏫 Teacher Format',
                'format' => '/teacher Name - Qualification - Subject - Department',
                'example' => '/teacher Mrs. Kumari Perera - M.Sc Chemistry, B.Ed - Chemistry - Science & Mathematics',
                'departments' => 'Science & Mathematics, Languages, Social Sciences, Arts, Physical Education, Technology'
            ],
            'gallery' => [
                'title' => '🖼 Gallery Format',
                'format' => '/gallery Description - Category',
                'example' => '/gallery Annual cultural festival dance performance - Cultural',
                'categories' => 'Academic, Sports, Cultural, Events, Facilities, Environment, Technology'
            ]
        ];
        
        if (!isset($messages[$type])) {
            $this->sendMessage($chat_id, "❓ Unknown content type. Type /help for all formats.");
            return;
        }
        
        $info = $messages[$type];
        $message = "<b>{$info['title']}</b>\n\n";
        $message .= "📝 <b>Format:</b>\n<code>{$info['format']}</code>\n\n";
        $message .= "📄 <b>Example:</b>\n<code>{$info['example']}</code>\n\n";
        
        if (isset($info['categories'])) {
            $message .= "🏷 <b>Available Categories:</b>\n{$info['categories']}\n\n";
        }
        
        if (isset($info['departments'])) {
            $message .= "🏢 <b>Available Departments:</b>\n{$info['departments']}\n\n";
        }
        
        if (isset($info['note'])) {
            $message .= "ℹ️ <b>Note:</b>\n{$info['note']}\n\n";
        }
        
        $message .= "📸 <b>Remember:</b> Always attach a photo with your caption!";
        
        $this->sendMessage($chat_id, $message);
    }
    
    /**
     * Send status message with statistics
     */
    private function sendStatusMessage($chat_id) {
        try {
            $stats = $this->db->getContentStats();
            
            $message = "📊 <b>Makalanegama School Website Statistics</b>\n\n";
            $message .= "📚 <b>Achievements:</b> {$stats['achievements']}\n";
            $message .= "📅 <b>Events:</b> {$stats['events']}\n";
            $message .= "📰 <b>News Articles:</b> {$stats['news']}\n";
            $message .= "👨‍🏫 <b>Teachers:</b> {$stats['teachers']}\n";
            $message .= "🖼 <b>Gallery Images:</b> {$stats['gallery']}\n\n";
            
            // Get pending content count
            $pending = $this->db->getPendingContent();
            $pending_count = count($pending);
            $message .= "⏳ <b>Pending Approval:</b> $pending_count items\n\n";
            
            $message .= "🕒 <b>Last Updated:</b> " . date('Y-m-d H:i:s') . "\n";
            $message .= "✅ <b>System Status:</b> Online and Running\n";
            $message .= "🌐 <b>Website:</b> " . SITE_URL;
            
            $this->sendMessage($chat_id, $message);
            
        } catch (Exception $e) {
            $this->logError("Error getting statistics: " . $e->getMessage());
            $this->sendMessage($chat_id, "❌ <b>Error</b>\n\nCould not retrieve system statistics. Please try again later.");
        }
    }
    
    /**
     * Show pending content for approval
     */
    private function showPendingContent($chat_id) {
        try {
            $pending = $this->db->getPendingContent();
            
            if (empty($pending)) {
                $this->sendMessage($chat_id, "✅ <b>No Pending Content</b>\n\nAll content has been approved!");
                return;
            }
            
            $message = "⏳ <b>Pending Content Approval</b>\n\n";
            $message .= "📋 <b>Total Items:</b> " . count($pending) . "\n\n";
            
            foreach (array_slice($pending, 0, 10) as $item) { // Show max 10 items
                $type = ucfirst($item['content_type']);
                $title = $item['title'] ?? $item['name'] ?? 'Untitled';
                $date = date('M j, Y', strtotime($item['created_at']));
                
                $message .= "📄 <b>$type:</b> " . substr($title, 0, 50);
                if (strlen($title) > 50) $message .= "...";
                $message .= "\n📅 $date\n";
                $message .= "🆔 ID: {$item['content_type']}-{$item['id']}\n\n";
            }
            
            if (count($pending) > 10) {
                $remaining = count($pending) - 10;
                $message .= "... and $remaining more items\n\n";
            }
            
            $message .= "<b>📝 To approve/reject:</b>\n";
            $message .= "<code>/approve {type}-{id}</code>\n";
            $message .= "<code>/reject {type}-{id}</code>\n\n";
            $message .= "<b>Example:</b>\n<code>/approve achievements-15</code>";
            
            $this->sendMessage($chat_id, $message);
            
        } catch (Exception $e) {
            $this->logError("Error getting pending content: " . $e->getMessage());
            $this->sendMessage($chat_id, "❌ <b>Error</b>\n\nCould not retrieve pending content.");
        }
    }
    
    /**
     * Handle approval command
     */
    private function handleApprovalCommand($chat_id, $args) {
        if (empty($args)) {
            $this->sendMessage($chat_id, 
                "❓ <b>Approval Format</b>\n\n" .
                "Use: <code>/approve {type}-{id}</code>\n\n" .
                "<b>Example:</b>\n<code>/approve achievements-15</code>\n\n" .
                "Type /pending to see items waiting for approval.");
            return;
        }
        
        $parts = explode('-', $args, 2);
        if (count($parts) !== 2) {
            $this->sendMessage($chat_id, "❌ Invalid format. Use: <code>/approve {type}-{id}</code>");
            return;
        }
        
        $type = trim($parts[0]);
        $id = (int)trim($parts[1]);
        
        if ($id <= 0) {
            $this->sendMessage($chat_id, "❌ Invalid ID. Please use a valid numeric ID.");
            return;
        }
        
        try {
            $result = $this->db->approveContent($type, $id);
            
            if ($result) {
                $this->sendMessage($chat_id, 
                    "✅ <b>Content Approved!</b>\n\n" .
                    "📄 <b>Type:</b> " . ucfirst($type) . "\n" .
                    "🆔 <b>ID:</b> $id\n\n" .
                    "The content is now live on the website!");
                    
                $this->logMessage("Content approved: $type-$id by user from chat $chat_id");
            } else {
                $this->sendMessage($chat_id, 
                    "❌ <b>Approval Failed</b>\n\n" .
                    "Could not approve $type-$id. Please check the ID and try again.");
            }
            
        } catch (Exception $e) {
            $this->logError("Error approving content: " . $e->getMessage());
            $this->sendMessage($chat_id, "❌ <b>Database Error</b>\n\nCould not approve content. Please try again.");
        }
    }
    
    /**
     * Handle rejection command
     */
    private function handleRejectionCommand($chat_id, $args) {
        if (empty($args)) {
            $this->sendMessage($chat_id, 
                "❓ <b>Rejection Format</b>\n\n" .
                "Use: <code>/reject {type}-{id}</code>\n\n" .
                "<b>Example:</b>\n<code>/reject news-23</code>");
            return;
        }
        
        $parts = explode('-', $args, 2);
        if (count($parts) !== 2) {
            $this->sendMessage($chat_id, "❌ Invalid format. Use: <code>/reject {type}-{id}</code>");
            return;
        }
        
        $type = trim($parts[0]);
        $id = (int)trim($parts[1]);
        
        if ($id <= 0) {
            $this->sendMessage($chat_id, "❌ Invalid ID. Please use a valid numeric ID.");
            return;
        }
        
        try {
            $result = $this->db->rejectContent($type, $id);
            
            if ($result) {
                $this->sendMessage($chat_id, 
                    "🗑 <b>Content Rejected</b>\n\n" .
                    "📄 <b>Type:</b> " . ucfirst($type) . "\n" .
                    "🆔 <b>ID:</b> $id\n\n" .
                    "The content has been deleted from the system.");
                    
                $this->logMessage("Content rejected: $type-$id by user from chat $chat_id");
            } else {
                $this->sendMessage($chat_id, 
                    "❌ <b>Rejection Failed</b>\n\n" .
                    "Could not reject $type-$id. Please check the ID and try again.");
            }
            
        } catch (Exception $e) {
            $this->logError("Error rejecting content: " . $e->getMessage());
            $this->sendMessage($chat_id, "❌ <b>Database Error</b>\n\nCould not reject content. Please try again.");
        }
    }
    
    /**
     * Create database backup
     */
    private function createBackup($chat_id) {
        try {
            $this->sendMessage($chat_id, "⏳ <b>Creating Backup...</b>\n\nPlease wait while we backup the database.");
            
            $backup_file = $this->db->createBackup();
            
            if ($backup_file) {
                $file_size = formatFileSize(filesize($backup_file));
                $this->sendMessage($chat_id, 
                    "✅ <b>Backup Created Successfully!</b>\n\n" .
                    "📁 <b>File:</b> " . basename($backup_file) . "\n" .
                    "📊 <b>Size:</b> $file_size\n" .
                    "🕒 <b>Created:</b> " . date('Y-m-d H:i:s') . "\n\n" .
                    "The backup has been saved on the server.");
            } else {
                $this->sendMessage($chat_id, 
                    "❌ <b>Backup Failed</b>\n\n" .
                    "Could not create database backup. Please check server configuration.");
            }
            
        } catch (Exception $e) {
            $this->logError("Error creating backup: " . $e->getMessage());
            $this->sendMessage($chat_id, "❌ <b>Backup Error</b>\n\nCould not create backup. Please try again.");
        }
    }
    
    /**
     * Handle text messages without media
     */
    private function handleTextMessage($message) {
        $chat_id = $message['chat']['id'];
        $text = $message['text'];
        
        $this->sendMessage($chat_id, 
            "📝 <b>Text Message Received</b>\n\n" .
            "To add content to the website, you need to:\n\n" .
            "1. 📸 <b>Attach a photo</b>\n" .
            "2. 📝 <b>Add a caption</b> with the correct format\n" .
            "3. 📤 <b>Send the message</b>\n\n" .
            "Type /help for format examples.");
    }
    
    /**
     * Handle edited messages
     */
    private function handleEditedMessage($message) {
        // For now, treat edited messages the same as new messages
        $this->handleMessage($message);
    }
    
    /**
     * Handle channel posts
     */
    private function handleChannelPost($post) {
        // Handle posts from channels if needed
        if (isset($post['photo']) && isset($post['caption'])) {
            $this->handleMediaMessage($post);
        }
    }
    
    /**
     * Handle callback queries (inline keyboard buttons)
     */
    private function handleCallbackQuery($query) {
        $chat_id = $query['message']['chat']['id'];
        $message_id = $query['message']['message_id'];
        $data = $query['data'];
        
        // Handle different callback actions
        $parts = explode(':', $data, 2);
        $action = $parts[0];
        $param = $parts[1] ?? '';
        
        switch ($action) {
            case 'approve':
                $this->handleApprovalCommand($chat_id, $param);
                break;
                
            case 'reject':
                $this->handleRejectionCommand($chat_id, $param);
                break;
                
            default:
                $this->answerCallbackQuery($query['id'], "Unknown action");
        }
        
        // Answer the callback query
        $this->answerCallbackQuery($query['id']);
    }
    
    /**
     * Answer callback query
     */
    private function answerCallbackQuery($query_id, $text = null) {
        $url = "https://api.telegram.org/bot{$this->bot_token}/answerCallbackQuery";
        
        $data = ['callback_query_id' => $query_id];
        if ($text) {
            $data['text'] = $text;
        }
        
        return $this->makeApiRequest($url, $data);
    }
    
    /**
     * Notify admins about new content
     */
    private function notifyAdmins($content_type, $parsed_data, $content_id) {
        $admin_users = array_slice($this->authorized_users, 0, 3); // Notify first 3 admins
        
        $message = "🔔 <b>New Content Awaiting Approval</b>\n\n";
        $message .= "📄 <b>Type:</b> " . ucfirst($content_type) . "\n";
        $message .= "📝 <b>Title:</b> " . $parsed_data['title'] . "\n";
        $message .= "🆔 <b>ID:</b> {$content_type}-{$content_id}\n\n";
        $message .= "📋 <b>Actions:</b>\n";
        $message .= "<code>/approve {$content_type}-{$content_id}</code>\n";
        $message .= "<code>/reject {$content_type}-{$content_id}</code>";
        
        foreach ($admin_users as $admin_id) {
            $this->sendMessage($admin_id, $message);
        }
    }
    
    /**
     * Check if user is authorized
     */
    private function isAuthorizedUser($user_id) {
        return in_array($user_id, $this->authorized_users);
    }
    
    /**
     * Log webhook data for debugging
     */
    private function logWebhook($update) {
        if (ENVIRONMENT === 'development') {
            $log_entry = date('Y-m-d H:i:s') . " - WEBHOOK: " . json_encode($update, JSON_PRETTY_PRINT) . "\n\n";
            file_put_contents(LOGS_PATH . 'telegram_webhook.log', $log_entry, FILE_APPEND | LOCK_EX);
        }
    }
    
    /**
     * Log messages
     */
    private function logMessage($message) {
        $log_entry = date('Y-m-d H:i:s') . " - INFO: $message\n";
        file_put_contents(LOGS_PATH . 'telegram.log', $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log errors
     */
    private function logError($error) {
        $log_entry = date('Y-m-d H:i:s') . " - ERROR: $error\n";
        file_put_contents(LOGS_PATH . 'telegram_errors.log', $log_entry, FILE_APPEND | LOCK_EX);
        
        // Also log to main error log
        if (function_exists('logMessage')) {
            logMessage('ERROR', "Telegram: $error");
        }
    }
    
    /**
     * Get bot info for debugging
     */
    public function getBotInfo() {
        $url = "https://api.telegram.org/bot{$this->bot_token}/getMe";
        
        $response = file_get_contents($url);
        if ($response === false) {
            return null;
        }
        
        $data = json_decode($response, true);
        return $data['ok'] ? $data['result'] : null;
    }
    
    /**
     * Set webhook URL
     */
    public function setWebhook($webhook_url, $secret_token = null) {
        $url = "https://api.telegram.org/bot{$this->bot_token}/setWebhook";
        
        $data = ['url' => $webhook_url];
        if ($secret_token) {
            $data['secret_token'] = $secret_token;
        }
        
        return $this->makeApiRequest($url, $data);
    }
    
    /**
     * Get webhook info
     */
    public function getWebhookInfo() {
        $url = "https://api.telegram.org/bot{$this->bot_token}/getWebhookInfo";
        
        $response = file_get_contents($url);
        if ($response === false) {
            return null;
        }
        
        $data = json_decode($response, true);
        return $data['ok'] ? $data['result'] : null;
    }
}

// Main execution
try {
    // Verify required configuration
    if (empty(TELEGRAM_BOT_TOKEN)) {
        http_response_code(500);
        die(json_encode(['error' => 'Telegram bot token not configured']));
    }
    
    if (empty(TELEGRAM_AUTHORIZED_USERS) || !is_array(TELEGRAM_AUTHORIZED_USERS)) {
        http_response_code(500);
        die(json_encode(['error' => 'No authorized users configured']));
    }
    
    // Create and process webhook
    $webhook = new TelegramWebhook();
    $webhook->processWebhook();
    
} catch (Exception $e) {
    // Log critical errors
    $error_log = LOGS_PATH . 'critical_errors.log';
    $log_entry = date('Y-m-d H:i:s') . " - CRITICAL: " . $e->getMessage() . "\n";
    $log_entry .= "Stack trace: " . $e->getTraceAsString() . "\n\n";
    file_put_contents($error_log, $log_entry, FILE_APPEND | LOCK_EX);
    
    http_response_code(500);
    echo json_encode(['error' => 'Critical server error']);
}
?>