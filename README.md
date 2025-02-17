# Project Overview
This project is a web application designed to provide secure user authentication, file uploading, and data management functionalities. The application is built using PHP and utilizes MySQL for database management. It also integrates PHPMailer for email communication. The main focus of this project is on security, ensuring that user data is protected and that common vulnerabilities are mitigated.

## Features
- User Registration and Login System
- Secure File Uploading and Downloading
- Dashboard for User Management
- Email Notification System using PHPMailer
- Robust Error Handling and Input Validation

## Security Measures Implemented
This project was developed with a strong emphasis on security. The following security measures and techniques were implemented to protect against common web vulnerabilities:

**1. Password Security**
- Password Hashing: User passwords are securely hashed using PHP's password_hash() function with the PASSWORD_BCRYPT algorithm. This ensures that even if the database is compromised, plain text passwords are not exposed.
- Password Verification: During login, passwords are verified using password_verify() to check the hash, enhancing security against brute-force attacks.

**2. SQL Injection Prevention**
- Prepared Statements: All database queries are executed using prepared statements with parameterized queries via mysqli_stmt_bind_param(). This protects against SQL injection attacks.

**3. Cross-Site Scripting (XSS) Protection**
- Output Encoding: User inputs are sanitized and escaped using htmlspecialchars() before being displayed on any page, preventing malicious scripts from being executed.

**4. Cross-Site Request Forgery (CSRF) Protection**
- CSRF Tokens: Forms are protected by generating unique CSRF tokens for each session. Tokens are validated upon form submission to ensure requests are legitimate.

**5. File Upload Security**
- MIME Type Checking: Uploaded files are validated for MIME type to ensure that only allowed file types are accepted.
- File Renaming and Path Sanitization: Uploaded files are renamed to avoid conflicts and sanitized to prevent directory traversal attacks.
- Directory Permissions: The uploads directory is secured with restricted write permissions to prevent unauthorized access.

**6. Email Security**
- PHPMailer Configuration: Email sending is configured with SMTP authentication and TLS encryption to secure communication between the application and the mail server.

**7. Session Security**
- Session Regeneration: Session IDs are regenerated upon login and logout to prevent session fixation attacks.
- Session Timeout: Inactive sessions are automatically expired to protect against unauthorized access.

## Technologies Used
- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Dependencies:** PHPMailer, Composer
- **Server:** XAMPP (Apache)

# Project Setup Guide

This guide provides detailed instructions on how to set up and run this project on any computer.

## Prerequisites

- XAMPP (to run Apache server and MySQL database)
- Composer (to manage PHP dependencies)

---

## Step 1: Install XAMPP
1. Download XAMPP from the official website: [https://www.apachefriends.org/index.html](https://www.apachefriends.org/index.html)
2. Install XAMPP and ensure Apache and MySQL components are selected.
3. Start XAMPP Control Panel and activate Apache and MySQL.

---

## Step 2: Install Composer
1. Download Composer from the official website: [https://getcomposer.org/download/](https://getcomposer.org/download/)
2. Follow installation instructions for your operating system.
3. Verify installation by running the following command in the terminal:
    ```
    composer -V
    ```

---

## Step 3: Setting Up the Project
1. Extract the project folder and rename it to `novel_app_project`.
2. Move the project folder to the XAMPP htdocs directory:
    ```
    C:\xampp\htdocs\novel_app_project
    ```
3. Open the terminal and navigate to the project directory:
    ```
    cd C:\xampp\htdocs\novel_app_project
    ```
4. Install PHP dependencies using Composer:
    ```
    composer install
    ```

---

## Step 4: Create 'uploads' Folder
1. In the project directory, create a new folder named `uploads` parallel to other project files:
    ```
    C:\xampp\htdocs\novel_app_project\uploads
    ```
2. Ensure the folder has write permissions.

---

## Step 5: Configure XAMPP
1. Open XAMPP Control Panel and click on 'Config' for Apache.
2. Select `php.ini` and enable necessary extensions if required (e.g., `extension=fileinfo`).
3. Save the file and restart Apache.

---

## Step 6: Import Database
1. Open phpMyAdmin from XAMPP Control Panel.
2. Create a new database named `novel_app_db`.
3. Import the SQL file provided with the project:
    ```
    novel_app_project/database/novel_app_db.sql
    ```

---

## Step 7: Run the Project
1. Open a web browser and navigate to:
    ```
    http://localhost/novel_app_project/
    ```
2. The application should now be accessible.

---

## Step 8: Install and Configure PHPMailer
PHPMailer is required for sending emails in this project. Follow these steps to install and set it up:

1. Install PHPMailer via Composer
Open the terminal and navigate to your project directory:
    ```
    cd C:\xampp\htdocs\novel_app_project
    ```
    
Install PHPMailer using Composer:
    ```
    composer require phpmailer/phpmailer
    ```
This will install PHPMailer in the vendor directory and update composer.json accordingly.

2. Configure PHPMailer
Open the email configuration file in the project (e.g., email/send_email.php) and update the following settings:
    ```
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'vendor/autoload.php';

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@example.com';
        $mail->Password = 'your_email_password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
    
        // Recipients
        $mail->setFrom('your_email@example.com', 'Your Name');
        $mail->addAddress('recipient@example.com');
    
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Test Email';
        $mail->Body    = 'This is a test email from PHPMailer.';
    
        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    ```

Make sure to update:

Host: SMTP server of your email provider (e.g., smtp.gmail.com for Gmail)
Username: Your email address
Password: Your email account's password or app-specific password
Port: 587 for TLS or 465 for SSL

3. Allow Less Secure Apps (for Gmail)
If using Gmail, you may need to enable less secure apps or set up an app-specific password if two-factor authentication is enabled:

- Go to https://myaccount.google.com/security
- Enable "Less secure app access" or generate an App Password if 2FA is on

4. Restart Apache Server
After making these changes, restart Apache from the XAMPP Control Panel for the changes to take effect.

---

## Troubleshooting
- If you encounter permission issues, ensure the `uploads` folder has the correct write permissions.
- Make sure all required PHP extensions are enabled in `php.ini`.

---


