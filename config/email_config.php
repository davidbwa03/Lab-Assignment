<?php
// config/email_config.php
class EmailConfig {
    // SMTP Configuration
    public static $SMTP_HOST = 'smtp.gmail.com'; // or your SMTP
    public static $SMTP_PORT = 465;
    public static $SMTP_USERNAME = 'davidbwashi@gmail.com';
    public static $SMTP_PASSWORD = 'hssm iwvq nimx otty'; // Use App Password for Gmail
    public static $SMTP_FROM_EMAIL = 'noreply@BManager.com';
    public static $SMTP_FROM_NAME = 'Business Manager';
    public static $SMTP_DEBUG = 0; // Set to 2 for debugging
    
    // Set to true for testing (saves emails to file instead of sending)
    public static $USE_FILE_LOGGING = false;
}