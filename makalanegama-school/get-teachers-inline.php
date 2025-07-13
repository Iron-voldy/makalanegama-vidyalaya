<?php
// Get teachers data to inject directly into JavaScript
try {
    require_once 'admin/config.php';
    require_once 'admin/database.php';
    
    $db = new Database();
    $teachers = $db->getTeachers(50); // Get up to 50 teachers
    
    // Format for JavaScript
    $teachersJson = json_encode($teachers);
    
    // Return JavaScript variable assignment
    echo "window.TEACHERS_DATA = " . $teachersJson . ";";
    echo "console.log('✅ Inline teachers data loaded:', window.TEACHERS_DATA.length, 'teachers');";
    
} catch (Exception $e) {
    echo "window.TEACHERS_DATA = [];";
    echo "console.error('❌ Failed to load inline teachers data:', '" . addslashes($e->getMessage()) . "');";
}
?>