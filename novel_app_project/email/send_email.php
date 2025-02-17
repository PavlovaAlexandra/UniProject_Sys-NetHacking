<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include Composer's autoloader
require 'vendor/autoload.php';

function sendVerificationEmail($email, $verification_token) {
    $subject = "Please confirm your registration";
    $message = "Please confirm your registration by clicking on the following link:\n";
    $message .= "https://novelapp/verify.php?token=$verification_token";

    // Create PHPMailer object
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                          // Use SMTP
        $mail->Host = 'smtp.gmail.com';                            // Specify SMTP server (for example, Gmail)
        $mail->SMTPAuth = true;                                    // Enable authentication
        $mail->Username = '!!!!!!!!!!!!!!!!!!!@gmail.com';      // Your email
        $mail->Password = '!!!!!!!!!!!!!!!!!!!';                   // Your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        // TLS encryption
        $mail->Port = 587;                                         // Port

        // Sender and recipient
        $mail->setFrom('!!!!!!!!!!!!!!!!!!!@gmail.com', 'Novel App'); // Sender's address
        $mail->addAddress($email);                                  // Recipient's address

        // Email content
        $mail->isHTML(false);                                       // Plain text email
        $mail->Subject = $subject;
        $mail->Body    = $message;

        // Send the email
        $mail->send();
        echo 'The confirmation email has been sent.';
    } catch (Exception $e) {
        echo "Error sending the email: {$mail->ErrorInfo}";
    }
}

function sendResetPasswordEmail($email, $reset_token) {
    $subject = "Password reset";
    $message = "Please click on the following link to reset your password:\n";
    $message .= "http://novelapp/reset_password.php?token=$reset_token";

    // Create PHPMailer object
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                          // Use SMTP
        $mail->Host = 'smtp.gmail.com';                            // Specify SMTP server (for example, Gmail)
        $mail->SMTPAuth = true;                                    // Enable authentication
        $mail->Username = '!!!!!!!!!!!!!!!!!!!@gmail.com';      // Your email
        $mail->Password = '!!!!!!!!!!!!!!!!!!!';                   // Your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        // TLS encryption
        $mail->Port = 587;                                         // Port

        // Sender and recipient
        $mail->setFrom('!!!!!!!!!!!!!!!!!!!@gmail.com', 'Novel App'); // Sender's address
        $mail->addAddress($email);                                  // Recipient's address

        // Email content
        $mail->isHTML(false);                                       // Plain text email
        $mail->Subject = $subject;
        $mail->Body    = $message;

        // Send the email
        $mail->send();
        echo 'The password reset instructions email has been sent.';
    } catch (Exception $e) {
        echo "Error sending the email: {$mail->ErrorInfo}";
    }
}

function sendPremiumRequestEmail($username, $user_email, $user_id) {
    $subject = "Request for Premium Status";
    $message = "
    Hello! \n
    The user with the username $username (ID: $user_id) wants to become a premium user. \n
    The user's email address is: $user_email. \n
    Please consider their request and grant premium status if possible. \n
    Thank you!
    ";


    // Create PHPMailer object
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                          // Use SMTP
        $mail->Host = 'smtp.gmail.com';                            // Specify SMTP server (for example, Gmail)
        $mail->SMTPAuth = true;                                    // Enable authentication
        $mail->Username = '!!!!!!!!!!!!!!!!!!!@gmail.com';      // Your email
        $mail->Password = '!!!!!!!!!!!!!!!!!!!';                   // Your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        // TLS encryption
        $mail->Port = 587;                                         // Port

        // Sender and recipient
        $mail->setFrom('!!!!!!!!!!!!!!!!!!!@gmail.com', 'Novel App'); // Sender's address
        $mail->addAddress('!!!!!!!!!!!!!!!!!!!@gmail.com');           // Recipient's address (administrator)

        // Email content
        $mail->isHTML(false);                                       // Plain text email
        $mail->Subject = $subject;
        $mail->Body    = $message;

        // Send the email
        $mail->send();
        echo 'Your request for premium status has been sent to the administrator. Please wait for a response.';
    } catch (Exception $e) {
        echo "Error sending the email: {$mail->ErrorInfo}";
    }
}

?>
