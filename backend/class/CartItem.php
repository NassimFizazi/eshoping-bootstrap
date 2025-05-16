<?php
class CartItem {
    // Database connection and table name
    private $conn;
    private $table_name = "cart_items";

    // Object properties
    public $id;
    public $user_id;
    public $product_id;
    public $quantity;
    public $created_at;

    // Product info
    public $product_name;
    public $product_price;
    public $product_image;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Get cart items for a user
    public function getUserCart() {
        // Query to get cart items with product details
        $query = "SELECT c.id, c.user_id, c.product_id, c.quantity, c.created_at, 
                        p.name as product_name, p.price as product_price, p.image_url as product_image
                  FROM " . $this->table_name . " c
                  JOIN products p ON c.product_id = p.id
                  WHERE c.user_id = :user_id
                  ORDER BY c.created_at DESC";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Add item to cart
    public function addToCart() {
        // First check if the item is already in the cart
        $check_query = "SELECT id, quantity FROM " . $this->table_name . " 
                        WHERE user_id = :user_id AND product_id = :product_id";
        
        // Prepare query
        $check_stmt = $this->conn->prepare($check_query);
        
        // Bind values
        $check_stmt->bindParam(":user_id", $this->user_id);
        $check_stmt->bindParam(":product_id", $this->product_id);
        
        // Execute query
        $check_stmt->execute();
        
        // If item exists, update quantity
        if($check_stmt->rowCount() > 0) {
            $row = $check_stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $new_quantity = $row['quantity'] + $this->quantity;
            
            // Update query
            $query = "UPDATE " . $this->table_name . "
                      SET quantity = :quantity
                      WHERE id = :id";
            
            // Prepare query
            $stmt = $this->conn->prepare($query);
            
            // Sanitize and bind values
            $stmt->bindParam(":quantity", $new_quantity);
            $stmt->bindParam(":id", $this->id);
            
            // Execute query
            if($stmt->execute()) {
                return true;
            }
            return false;
        } 
        
        // If item doesn't exist, add new cart item
        $query = "INSERT INTO " . $this->table_name . " 
                 (user_id, product_id, quantity) 
                 VALUES
                 (:user_id, :product_id, :quantity)";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":quantity", $this->quantity);

        // Execute query
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Update cart item quantity
    public function updateQuantity() {
        // Update query
        $query = "UPDATE " . $this->table_name . "
                  SET quantity = :quantity
                  WHERE id = :id AND user_id = :user_id";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Bind values
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":user_id", $this->user_id);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Remove item from cart
    public function removeFromCart() {
        // Delete query
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE id = :id AND user_id = :user_id";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":user_id", $this->user_id);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Clear user's cart
    public function clearCart() {
        // Delete query
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Get cart total
    public function getCartTotal() {
        // Query
        $query = "SELECT SUM(p.price * c.quantity) as total
                  FROM " . $this->table_name . " c
                  JOIN products p ON c.product_id = p.id
                  WHERE c.user_id = :user_id";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);

        // Execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'] ? $row['total'] : 0;
    }

    // Count items in cart
    public function countItems() {
        // Query
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE user_id = :user_id";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);

        // Execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['count'];
    }
}
?>
