<?php
require_once 'config.php';
require_once 'db.php';

// Sanitize input
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Generate secure random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Format price
function formatPrice($price) {
    return number_format($price, 2, ',', ' ') . ' €';
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Redirect with message
function redirect($location, $message = '', $type = 'info') {
    if (!empty($message)) {
        $_SESSION['flash'] = [
            'message' => $message,
            'type' => $type
        ];
    }
    header("Location: $location");
    exit();
}

// Display flash message
function displayFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $message = $_SESSION['flash']['message'];
        $type = $_SESSION['flash']['type'];
        unset($_SESSION['flash']);
        
        $bgColor = $type === 'error' ? 'bg-red-500' : ($type === 'success' ? 'bg-green-500' : 'bg-blue-500');
        
        return "<div class='flash-message {$bgColor} text-white p-4 rounded mb-4'>{$message}</div>";
    }
    return '';
}

// Send email
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . SITE_NAME . ' <' . ADMIN_EMAIL . '>' . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Generate reservation confirmation email
function generateReservationEmail($reservation) {
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { text-align: center; padding: 20px; background-color: #f8f9fa; }
            .content { padding: 20px; }
            .footer { text-align: center; padding: 20px; font-size: 12px; color: #6c757d; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . SITE_NAME . "</h1>
            </div>
            <div class='content'>
                <h2>Confirmation de demande de réservation</h2>
                <p>Bonjour {$reservation['client_name']},</p>
                <p>Nous avons bien reçu votre demande de réservation. Notre équipe va l'examiner dans les plus brefs délais.</p>
                <p>Détails de la réservation :</p>
                <ul>
                    <li>Véhicule : {$reservation['vehicle_name']}</li>
                    <li>Date de début : {$reservation['start_date']}</li>
                    <li>Date de fin : {$reservation['end_date']}</li>
                </ul>
                <p>Nous vous contacterons rapidement pour confirmer votre réservation.</p>
            </div>
            <div class='footer'>
                <p>© " . date('Y') . " " . SITE_NAME . ". Tous droits réservés.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return $message;
}

// Validate date range
function validateDateRange($startDate, $endDate) {
    $start = strtotime($startDate);
    $end = strtotime($endDate);
    $today = strtotime('today');
    
    if ($start < $today) {
        return ['valid' => false, 'message' => 'La date de début doit être ultérieure à aujourd\'hui'];
    }
    
    if ($end <= $start) {
        return ['valid' => false, 'message' => 'La date de fin doit être ultérieure à la date de début'];
    }
    
    return ['valid' => true];
}

// Check vehicle availability
function isVehicleAvailable($vehicleId, $startDate, $endDate) {
    $db = Database::getInstance();
    
    $sql = "SELECT COUNT(*) as count FROM reservations 
            WHERE vehicle_id = ? 
            AND status = 'confirmed'
            AND ((start_date BETWEEN ? AND ?) 
            OR (end_date BETWEEN ? AND ?)
            OR (start_date <= ? AND end_date >= ?))";
            
    $result = $db->single($sql, [
        $vehicleId, 
        $startDate, 
        $endDate,
        $startDate,
        $endDate,
        $startDate,
        $endDate
    ]);
    
    return $result['count'] === 0;
}

// Upload and process image
function uploadImage($file, $targetDir = 'uploads/') {
    $targetDir = __DIR__ . '/../public/' . $targetDir;
    
    // Create directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
    
    // Check if image file is actual image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        throw new Exception('Le fichier n\'est pas une image.');
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5000000) {
        throw new Exception('Le fichier est trop volumineux (max 5MB).');
    }
    
    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        throw new Exception('Seuls les fichiers JPG, JPEG, PNG & GIF sont autorisés.');
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }
    
    throw new Exception('Une erreur est survenue lors du téléchargement du fichier.');
}
