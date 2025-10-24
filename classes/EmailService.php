<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        if (!EmailConfig::$USE_FILE_LOGGING) {
            $this->setupSMTP();
        }
    }
    
    private function setupSMTP() {
        try {
            $this->mail->SMTPDebug = EmailConfig::$SMTP_DEBUG;
            $this->mail->isSMTP();
            $this->mail->Host = EmailConfig::$SMTP_HOST;
            $this->mail->SMTPAuth = true;
            $this->mail->Username = EmailConfig::$SMTP_USERNAME;
            $this->mail->Password = EmailConfig::$SMTP_PASSWORD;
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = EmailConfig::$SMTP_PORT;
            $this->mail->setFrom(EmailConfig::$SMTP_FROM_EMAIL, EmailConfig::$SMTP_FROM_NAME);
        } catch (Exception $e) {
            error_log("Email setup failed: " . $e->getMessage());
            throw new Exception("Email configuration error");
        }
    }
    
    public function send2FACode($toEmail, $toName, $code) {
        if (EmailConfig::$USE_FILE_LOGGING) {
            // Save email to file for testing
            return $this->logEmailToFile($toEmail, "2FA Code: $code", "Your verification code is: $code");
        }
        
        try {
            $this->mail->addAddress($toEmail, $toName);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Your Two-Factor Authentication Code - Business Manager';
            
            $htmlBody = $this->get2FAEmailTemplate($code, $toName);
            $textBody = "Your verification code is: $code\n\nThis code will expire in 10 minutes.";
            
            $this->mail->Body = $htmlBody;
            $this->mail->AltBody = $textBody;
            
            $this->mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("2FA email failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function get2FAEmailTemplate($code, $name) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { text-align: center; margin-bottom: 30px; }
                .code { font-size: 32px; font-weight: bold; text-align: center; color: #007bff; margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px; letter-spacing: 5px; }
                .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>🔒 Two-Factor Authentication</h2>
                </div>
                <p>Hello <strong>$name</strong>,</p>
                <p>Your verification code for Business Manager is:</p>
                <div class='code'>$code</div>
                <p>Enter this code on the verification page to complete your login.</p>
                <p><strong>⚠️ This code will expire in 10 minutes.</strong></p>
                <p>If you didn't request this code, please ignore this email.</p>
                <div class='footer'>
                    <p>Business Manager System</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function logEmailToFile($toEmail, $subject, $content) {
        $logDir = __DIR__ . '/../email_logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $filename = $logDir . '/email_' . date('Y-m-d_H-i-s') . '.txt';
        $logContent = "To: $toEmail\nSubject: $subject\n\n$content\n\n" . str_repeat('-', 50) . "\n";
        
        file_put_contents($filename, $logContent, FILE_APPEND);
        return true;
    }
}
?>