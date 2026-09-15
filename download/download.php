<?php
require_once 'config.php';
require_login();

// Handle file download
if (isset($_GET['file'])) {
    $filename = $_GET['file'];
    $filepath = UPLOAD_PATH . '/' . $filename;
    
    // Security check - ensure file is in documents directory
    $realPath = realpath($filepath);
    $allowedPath = realpath(UPLOAD_PATH);
    
    if (!$realPath || strpos($realPath, $allowedPath) !== 0) {
        http_response_code(403);
        die('Access denied');
    }
    
    if (file_exists($filepath)) {
        // Get file info
        $fileInfo = pathinfo($filename);
        $extension = strtolower($fileInfo['extension']);
        
        // Set appropriate content type
        switch ($extension) {
            case 'txt':
                $contentType = 'text/plain';
                break;
            case 'pdf':
                $contentType = 'application/pdf';
                break;
            case 'doc':
                $contentType = 'application/msword';
                break;
            case 'docx':
                $contentType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                break;
            default:
                $contentType = 'application/octet-stream';
        }
        
        // Set headers for download
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        
        // Clear output buffer
        ob_clean();
        flush();
        
        // Read file and output
        readfile($filepath);
        exit();
    } else {
        http_response_code(404);
        die('File not found');
    }
} else {
    http_response_code(400);
    die('No file specified');
}
?>
