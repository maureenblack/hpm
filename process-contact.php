<?php
/**
 * Contact Form Processing Script
 * Holistic Prosperity Ministry
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize response array
$response = [
    'success' => false,
    'message' => 'An error occurred while processing your request.'
];

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get form data and sanitize
    $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';
    $subject = isset($_POST['subject']) ? sanitize_input($_POST['subject']) : '';
    $message = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';
    
    // Validate required fields
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If no validation errors, proceed with email sending
    if (empty($errors)) {
        // Recipient email
        $to = "hello@holisticprosperityministry.org";
        
        // Email headers
        $headers = "From: " . $name . " <" . $email . ">\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        // Email content
        $email_content = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                h2 {
                    color: #4B0082;
                    border-bottom: 2px solid #FFD700;
                    padding-bottom: 10px;
                }
                .contact-info {
                    background-color: #f9f9f9;
                    padding: 15px;
                    border-radius: 5px;
                    margin-bottom: 20px;
                }
                .message-content {
                    background-color: #f9f9f9;
                    padding: 15px;
                    border-radius: 5px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>New Contact Form Submission</h2>
                <div class='contact-info'>
                    <p><strong>Name:</strong> $name</p>
                    <p><strong>Email:</strong> $email</p>
                    " . (!empty($phone) ? "<p><strong>Phone:</strong> $phone</p>" : "") . "
                    <p><strong>Subject:</strong> $subject</p>
                </div>
                <div class='message-content'>
                    <h3>Message:</h3>
                    <p>" . nl2br($message) . "</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Attempt to send email
        $mail_sent = mail($to, "Contact Form: $subject", $email_content, $headers);
        
        if ($mail_sent) {
            // Email sent successfully
            $response['success'] = true;
            $response['message'] = "Thank you for your message. We will get back to you soon!";
            
            // Log successful submission
            $log_message = date('Y-m-d H:i:s') . " - Contact form submitted by: $name ($email) - Subject: $subject\n";
            file_put_contents('logs/contact_submissions.log', $log_message, FILE_APPEND);
            
            // Set success flash message for redirect
            $_SESSION['contact_success'] = true;
            $_SESSION['contact_message'] = "Thank you for your message. We will get back to you soon!";
            
            // Redirect back to contact page
            header('Location: contact.html');
            exit;
        } else {
            // Email sending failed
            $response['message'] = "Sorry, there was an error sending your message. Please try again later.";
            
            // Log error
            $error_log_message = date('Y-m-d H:i:s') . " - ERROR: Failed to send contact form from: $name ($email) - Subject: $subject\n";
            file_put_contents('logs/contact_errors.log', $error_log_message, FILE_APPEND);
            
            // Set error flash message for redirect
            $_SESSION['contact_error'] = true;
            $_SESSION['contact_message'] = "Sorry, there was an error sending your message. Please try again later.";
            
            // Redirect back to contact page
            header('Location: contact.html');
            exit;
        }
    } else {
        // Validation errors
        $response['message'] = "Please fix the following errors: " . implode(", ", $errors);
        
        // Set error flash message for redirect
        $_SESSION['contact_error'] = true;
        $_SESSION['contact_message'] = "Please fix the following errors: " . implode(", ", $errors);
        
        // Redirect back to contact page
        header('Location: contact.html');
        exit;
    }
} else {
    // Not a POST request
    $response['message'] = "Invalid request method.";
    
    // Redirect to contact page
    header('Location: contact.html');
    exit;
}

// If we get here, return JSON response (for AJAX requests)
header('Content-Type: application/json');
echo json_encode($response);
?>
