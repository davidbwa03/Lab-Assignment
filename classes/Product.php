<?php
require_once __DIR__ . '/../interfaces/ProductInterface.php';

require_once __DIR__ . '/../includes/validation.php';

class Product implements ProductInterface {
    private $conn;
    private $table_name = "goods_services";

    public $id;
    public $name;
    public $description;
    public $price;
    public $category_id;
    public $type;
    public $created_by;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                     SET name=:name, description=:description, price=:price, 
                         category_id=:category_id, type=:type, created_by=:created_by";
            
            $stmt = $this->conn->prepare($query);

            $this->name = htmlspecialchars(strip_tags(trim($data['name'])));
            $this->description = htmlspecialchars(strip_tags(trim($data['description'])));
            $this->price = htmlspecialchars(strip_tags(trim($data['price'])));
            $this->category_id = htmlspecialchars(strip_tags(trim($data['category_id'])));
            $this->type = htmlspecialchars(strip_tags(trim($data['type'])));
            $this->created_by = htmlspecialchars(strip_tags(trim($data['created_by'])));

            // Validate price
            if(empty($this->price) || !is_numeric($this->price) || $this->price <= 0) {
                throw new Exception("Price must be a positive number");
            }

            $stmt->bindParam(":name", $this->name);
            $stmt->bindParam(":description", $this->description);
            $stmt->bindParam(":price", $this->price);
            $stmt->bindParam(":category_id", $this->category_id);
            $stmt->bindParam(":type", $this->type);
            $stmt->bindParam(":created_by", $this->created_by);

            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;

        } catch(PDOException $exception) {
            throw new Exception("Product creation failed: " . $exception->getMessage());
        }
    }

    public function read() {
        $query = "SELECT gs.*, c.name as category_name, u.username as created_by_name
                 FROM " . $this->table_name . " gs
                 LEFT JOIN categories c ON gs.category_id = c.id
                 LEFT JOIN users u ON gs.created_by = u.id
                 ORDER BY gs.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                 SET name=:name, description=:description, price=:price, 
                     category_id=:category_id, type=:type 
                 WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);

        $name = htmlspecialchars(strip_tags(trim($data['name'])));
        $description = htmlspecialchars(strip_tags(trim($data['description'])));
        $price = htmlspecialchars(strip_tags(trim($data['price'])));
        $category_id = htmlspecialchars(strip_tags(trim($data['category_id'])));
        $type = htmlspecialchars(strip_tags(trim($data['type'])));

        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":type", $type);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function search($keyword) {
        $query = "SELECT gs.*, c.name as category_name, u.username as created_by_name
                 FROM " . $this->table_name . " gs
                 LEFT JOIN categories c ON gs.category_id = c.id
                 LEFT JOIN users u ON gs.created_by = u.id
                 WHERE gs.name LIKE :keyword OR gs.description LIKE :keyword
                 ORDER BY gs.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $search_keyword = "%" . htmlspecialchars(strip_tags(trim($keyword))) . "%";
        $stmt->bindParam(":keyword", $search_keyword);
        $stmt->execute();
        
        return $stmt;
    }

    public function getCategories() {
        $query = "SELECT id, name, description FROM categories ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getProductById($id) {
        $query = "SELECT gs.*, c.name as category_name, u.username as created_by_name
                 FROM " . $this->table_name . " gs
                 LEFT JOIN categories c ON gs.category_id = c.id
                 LEFT JOIN users u ON gs.created_by = u.id
                 WHERE gs.id = :id LIMIT 1";
        
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

