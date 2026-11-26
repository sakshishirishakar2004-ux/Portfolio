<?php
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit;
}

$name    = isset($_POST['name']) ? strip_tags(trim($_POST['name'])) : '';
$email   = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$type    = isset($_POST['type']) ? strip_tags(trim($_POST['type'])) : 'General Collaboration';
$message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';

if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid email address format."]);
    exit;
}

$to = "sakshishirishakar2004@gmail.com";
$subject = "New Portfolio Inquiry: " . $type . " from " . $name;

$email_content = "You have received a new message from your portfolio contact form.\n\n";
$email_content .= "Name: " . $name . "\n";
$email_content .= "Email: " . $email . "\n";
$email_content .= "Inquiry Type: " . $type . "\n\n";
$email_content .= "Message:\n" . $message . "\n";

$headers = "From: " . $name . " <" . $email . ">\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

$db_host = "127.0.0.1"; 
$db_user = "root";      
$db_pass = "";          
$db_name = "portfolio_db";
$db_port = 3307;        

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$conn->connect_error) {
   
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, inquiry_type, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $type, $message);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
    $mail_sent = true; 
} else {
        $mail_sent = @mail($to, $subject, $email_content, $headers);
}

if ($mail_sent) {
    http_response_code(200); // OK
    echo json_encode([
        "status" => "success", 
        "message" => "Thank you! Your message has been received. Sakshi will be in touch shortly."
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Oops! Something went wrong while sending your message. Please try emailing directly."
    ]);
}
?>