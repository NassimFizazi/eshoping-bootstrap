<?php
class Product {
    // Database connection and table name
    private $conn;
    private $table_name = "products";

    // Object properties
    public $id;
    public $name;
    public $description;
    public $price;
    public $image_url;
    public $category_id;
    public $stock_quantity;
    public $created_at;
    public $category_name;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all products
    public function readAll($page = 1, $per_page = 8, $category_id = null) {
        // Make sure we have a valid connection
        if (!$this->conn) {
            // Return an empty result set if no connection
            return new PDOStatement();
        }
        
        // Calculate the starting point for the LIMIT clause
        $start = ($page - 1) * $per_page;

        // Base query
        $query = "SELECT p.id, p.name, p.description, p.price, p.image_url, p.category_id, 
                        p.stock_quantity, p.created_at, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id";

        // Add category filter if specified
        if($category_id) {
            $query .= " WHERE p.category_id = :category_id";
        }
                        
        $query .= " ORDER BY p.created_at DESC
                  LIMIT :per_page OFFSET :start";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Bind values
        if($category_id) {
            $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
        }
        $stmt->bindParam(":start", $start, PDO::PARAM_INT);
        $stmt->bindParam(":per_page", $per_page, PDO::PARAM_INT);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get total product count (for pagination)
    public function getTotal($category_id = null) {
        // Make sure we have a valid connection
        if (!$this->conn) {
            return 0;
        }
        
        // Base query
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        // Add category filter if specified
        if($category_id) {
            $query .= " WHERE category_id = :category_id";
        }

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Bind values if needed
        if($category_id) {
            $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
        }

        // Execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ?? 0;
    }

    // Read single product
    public function readOne() {
        // Make sure we have a valid connection
        if (!$this->conn) {
            return false;
        }
        
        // Query to read single record
        $query = "SELECT p.id, p.name, p.description, p.price, p.image_url, 
                        p.category_id, p.stock_quantity, p.created_at, 
                        c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = ?
                  LIMIT 1";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Bind ID of product to be read
        $stmt->bindParam(1, $this->id);

        // Execute query
        $stmt->execute();

        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Set properties
        if($row) {
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->price = $row['price'];
            $this->image_url = $row['image_url'];
            $this->category_id = $row['category_id'];
            $this->stock_quantity = $row['stock_quantity'];
            $this->created_at = $row['created_at'];
            $this->category_name = $row['category_name'];
            return true;
        }

        return false;
    }

    // Create product (admin functionality)
    public function create() {
        // Make sure we have a valid connection
        if (!$this->conn) {
            return false;
        }
        
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . " 
                (name, description, price, image_url, category_id, stock_quantity) 
                VALUES
                (:name, :description, :price, :image_url, :category_id, :stock_quantity)";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->stock_quantity = htmlspecialchars(strip_tags($this->stock_quantity));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":stock_quantity", $this->stock_quantity);

        // Execute query
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Update product (admin functionality)
    public function update() {
        // Make sure we have a valid connection
        if (!$this->conn) {
            return false;
        }
        
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                SET name = :name, 
                    description = :description, 
                    price = :price, 
                    image_url = :image_url, 
                    category_id = :category_id, 
                    stock_quantity = :stock_quantity
                WHERE id = :id";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->stock_quantity = htmlspecialchars(strip_tags($this->stock_quantity));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":stock_quantity", $this->stock_quantity);
        $stmt->bindParam(":id", $this->id);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete product (admin functionality)
    public function delete() {
        // Make sure we have a valid connection
        if (!$this->conn) {
            return false;
        }
        
        // Query
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind id of record to delete
        $stmt->bindParam(1, $this->id);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Search products by name or description
    public function search($keywords, $page = 1, $per_page = 8) {
        // Make sure we have a valid connection
        if (!$this->conn) {
            // Return an empty result set if no connection
            return new PDOStatement();
        }
        
        // Calculate the starting point for the LIMIT clause
        $start = ($page - 1) * $per_page;

        // Query
        $query = "SELECT p.id, p.name, p.description, p.price, p.image_url, 
                        p.category_id, p.stock_quantity, p.created_at, 
                        c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.name LIKE :keywords OR p.description LIKE :keywords
                  ORDER BY p.created_at DESC
                  LIMIT :per_page OFFSET :start";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";

        // Bind values
        $stmt->bindParam(":keywords", $keywords);
        $stmt->bindParam(":start", $start, PDO::PARAM_INT);
        $stmt->bindParam(":per_page", $per_page, PDO::PARAM_INT);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get search result count (for pagination)
    public function getSearchTotal($keywords) {
        // Make sure we have a valid connection
        if (!$this->conn) {
            return 0;
        }
        
        // Query
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . " 
                  WHERE name LIKE :keywords OR description LIKE :keywords";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";

        // Bind values
        $stmt->bindParam(":keywords", $keywords);

        // Execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ?? 0;
    }

    // Check if product is in stock and has sufficient quantity
    public function checkStock($quantity) {
        if ($this->stock_quantity >= $quantity) {
            return true;
        }
        return false;
    }

    // Update stock quantity (usually after order)
    public function updateStock($quantity) {
        // Make sure we have a valid connection
        if (!$this->conn) {
            return false;
        }
        
        // Query to update stock
        $query = "UPDATE " . $this->table_name . "
                  SET stock_quantity = stock_quantity - :quantity
                  WHERE id = :id AND stock_quantity >= :quantity";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        $this->id = htmlspecialchars(strip_tags($this->id));
        $quantity = htmlspecialchars(strip_tags($quantity));

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":quantity", $quantity);

        // Execute query
        if($stmt->execute() && $stmt->rowCount() > 0) {
            return true;
        }

        return false;
    }
}
?>
