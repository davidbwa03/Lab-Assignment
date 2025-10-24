<?php
require_once __DIR__ . '/../interfaces/UserInterface.php';
require_once __DIR__ . '/../includes/validation.php'; 

class User implements UserInterface {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $phone;
    public $two_factor_secret;
    public $two_factor_code;
    public $two_factor_expiry;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($data) {
        try {
            // Check if username or email already exists
            $check_query = "SELECT id FROM " . $this->table_name . " WHERE username = :username OR email = :email LIMIT 1";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(":username", $data['username']);
            $check_stmt->bindParam(":email", $data['email']);
            $check_stmt->execute();

            if($check_stmt->rowCount() > 0) {
                throw new Exception("Username or email already exists");
            }

            $query = "INSERT INTO " . $this->table_name . " SET username=:username, email=:email, password=:password, phone=:phone";
            $stmt = $this->conn->prepare($query);

            $this->username = htmlspecialchars(strip_tags(trim($data['username'])));
            $this->email = htmlspecialchars(strip_tags(trim($data['email'])));
            $this->phone = htmlspecialchars(strip_tags(trim($data['phone'])));
            $this->password = password_hash($data['password'], PASSWORD_DEFAULT);

            $stmt->bindParam(":username", $this->username);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":password", $this->password);
            $stmt->bindParam(":phone", $this->phone);

            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return $this->id; // Return user ID for 2FA setup
            }
            return false;

        } catch(PDOException $exception) {
            throw new Exception("Registration failed: " . $exception->getMessage());
        }
    }

    /**
     * Set 2FA code for user registration verification
     */
    public function set2FACode($userId, $code, $expiry) {
        try {
            $query = "UPDATE " . $this->table_name . " SET two_factor_code = :code, two_factor_expiry = :expiry WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":code", $code);
            $stmt->bindParam(":expiry", $expiry);
            $stmt->bindParam(":id", $userId);
            
            $result = $stmt->execute();
            
            if ($result) {
                error_log("2FA code saved for user ID: $userId");
                return true;
            } else {
                error_log("FAILED to save 2FA code for user ID: $userId");
                return false;
            }
        } catch (PDOException $e) {
            error_log("Database error in set2FACode: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify 2FA code for registration
     */
    public function verify2FACode($userId, $code) {
        try {
            $query = "SELECT two_factor_code, two_factor_expiry FROM " . $this->table_name . " 
                     WHERE id = :id AND two_factor_code = :code";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $userId);
            $stmt->bindParam(":code", $code);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Check if code is expired
                $currentTime = date('Y-m-d H:i:s');
                if ($row['two_factor_expiry'] > $currentTime) {
                    // Clear the 2FA code after successful verification
                    $this->clear2FACode($userId);
                    return true;
                } else {
                    error_log("2FA code expired for user ID: $userId");
                    return false;
                }
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Database error in verify2FACode: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear 2FA code after successful verification
     */
    private function clear2FACode($userId) {
        try {
            $query = "UPDATE " . $this->table_name . " SET two_factor_code = NULL, two_factor_expiry = NULL WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $userId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error in clear2FACode: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user has pending 2FA verification
     */
    public function hasPending2FA($userId) {
        try {
            $query = "SELECT two_factor_code, two_factor_expiry FROM " . $this->table_name . " 
                     WHERE id = :id AND two_factor_code IS NOT NULL";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $userId);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                // Check if code is not expired
                if ($row['two_factor_expiry'] > date('Y-m-d H:i:s')) {
                    return true;
                }
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Database error in hasPending2FA: " . $e->getMessage());
            return false;
        }
    }

    public function login($username, $password) {
        $username = htmlspecialchars(strip_tags(trim($username)));
        
        $query = "SELECT id, username, password, email, phone, two_factor_secret, two_factor_code, two_factor_expiry, created_at 
                 FROM " . $this->table_name . " 
                 WHERE username = :username OR email = :username 
                 LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->phone = $row['phone'];
                $this->two_factor_secret = $row['two_factor_secret'];
                $this->two_factor_code = $row['two_factor_code'];
                $this->two_factor_expiry = $row['two_factor_expiry'];
                $this->created_at = $row['created_at'];
                return true;
            }
        }
        return false;
    }

    public function enable2FA() {
        // Generate a 6-digit code
        $code = sprintf("%06d", mt_rand(1, 999999));
        
        // Store code in database
        $query = "UPDATE " . $this->table_name . " SET two_factor_secret = :secret WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":secret", $code);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            $this->two_factor_secret = $code;
            
            // Send 2FA code via email
            if ($this->send2FAEmail($code)) {
                return $code;
            }
        }
        return false;
    }

    private function send2FAEmail($code) {
        try {
            require_once __DIR__ . '/EmailService.php';
            $emailService = new EmailService();
            return $emailService->send2FACode($this->email, $this->username, $code);
        } catch (Exception $e) {
            error_log("2FA email failed: " . $e->getMessage());
            // Don't fail login if email fails
            return true;
        }
    }

    public function verify2FA($code) {
        if(empty($this->two_factor_secret)) {
            return true; // 2FA not enabled
        }
        
        // Check if code matches
        $query = "SELECT two_factor_secret FROM " . $this->table_name . " 
                 WHERE id = :id AND two_factor_secret = :code";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":code", $code);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function updateProfile($data) {
        $query = "UPDATE " . $this->table_name . " SET email = :email, phone = :phone WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->email = htmlspecialchars(strip_tags(trim($data['email'])));
        $this->phone = htmlspecialchars(strip_tags(trim($data['phone'])));
        
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    public function readAll() {
        $query = "SELECT id, username, email, phone, created_at FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getUserById($id) {
        $query = "SELECT id, username, email, phone, created_at FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    /**
     * Get user by ID with all fields including 2FA
     */
    public function getUserByIdWith2FA($id) {
        $query = "SELECT id, username, email, phone, two_factor_secret, two_factor_code, two_factor_expiry, created_at 
                 FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
}
?>