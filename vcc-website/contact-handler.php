<?php
/**
 * VCC Contact Form Handler
 * Processes contact form submissions and stores in database
 */

require_once 'config.php';
define('VCC_INSTALLED', true);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validate required fields
if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Name, email, and message are required']);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $subject, $message]);
    
    // Prepare WhatsApp message
    $whatsappNumber = '+18095866653';
    $whatsappMessage = urlencode("Nuevo mensaje de contacto:\nNombre: $name\nEmail: $email\nAsunto: $subject\nMensaje: $message");
    $whatsappUrl = "https://wa.me/$whatsappNumber?text=$whatsappMessage";
    
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully!',
        'whatsapp_url' => $whatsappUrl
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
