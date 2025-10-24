<?php
class Validation {
    public static function validateUsername($username) {
        if(empty($username)) return "Username is required";
        if(strlen($username) < 3) return "Username must be at least 3 characters";
        if(strlen($username) > 50) return "Username must be less than 50 characters";
        if(!preg_match('/^[a-zA-Z0-9_]+$/', $username)) return "Username can only contain letters, numbers and underscores";
        return true;
    }

    public static function validateEmail($email) {
        if(empty($email)) return "Email is required";
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Invalid email format";
        return true;
    }

    public static function validatePassword($password) {
        if(empty($password)) return "Password is required";
        if(strlen($password) < 8) return "Password must be at least 8 characters";
        if(!preg_match('/[A-Z]/', $password)) return "Password must contain at least one uppercase letter";
        if(!preg_match('/[a-z]/', $password)) return "Password must contain at least one lowercase letter";
        if(!preg_match('/[0-9]/', $password)) return "Password must contain at least one number";
        return true;
    }

    public static function validatePrice($price) {
        if(empty($price)) return "Price is required";
        if(!is_numeric($price) || $price <= 0) return "Price must be a positive number";
        if($price > 9999999.99) return "Price is too large";
        return true;
    }

    public static function validatePhone($phone) {
        if(!empty($phone) && !preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $phone)) {
            return "Invalid phone number format";
        }
        return true;
    }

    public static function sanitizeInput($data) {
        if(is_array($data)) {
            return array_map('self::sanitizeInput', $data);
        }
        return htmlspecialchars(strip_tags(trim($data)));
    }
}
?>