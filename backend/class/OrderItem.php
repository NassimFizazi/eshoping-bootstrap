<?php
class OrderItem {
    // Database connection and table name
    private $conn;
    private $table_name = "order_items";

    // Object properties
    public $id;
    public $order_id;
    public $product_id;
    public $quantity;
    public $price;

    // Product details
    public $product_name;
    public $product_image;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create order item
    public function create() {
        // Insert query
        $query = "INSERT INTO " . $this->table_name . " 
                (order_id, product_id, quantity, price) 
                VALUES
                (:order_id, :product_id, :quantity, :price)";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->order_id = htmlspecialchars(strip_tags($this->order_id));
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->price = htmlspecialchars(strip_tags($this->price));

        // Bind values
        $stmt->bindParam(":order_id", $this->order_id);
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":price", $this->price);

        // Execute query
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Read order items for a specific order
    public function readOrderItems() {
        // Query
        $query = "SELECT oi.*, p.name as product_name, p.image_url as product_image
                  FROM " . $this->table_name . " oi
                  JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = :order_id";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->order_id = htmlspecialchars(strip_tags($this->order_id));

        // Bind values
        $stmt->bindParam(":order_id", $this->order_id);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Create order items from cart
    public function createFromCart($user_id, $order_id) {
        // Query to get cart items
        $query = "SELECT c.product_id, c.quantity, p.price
                  FROM cart_items c
                  JOIN products p ON c.product_id = p.id
                  WHERE c.user_id = :user_id";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(":user_id", $user_id);

        // Execute query
        $stmt->execute();

        // Initialize success flag
        $success = true;

        // Begin transaction
        $this->conn->beginTransaction();

        try {
            // For each cart item, create an order item
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->order_id = $order_id;
                $this->product_id = $row['product_id'];
                $this->quantity = $row['quantity'];
                $this->price = $row['price'];

                // Create order item
                if (!$this->create()) {
                    $success = false;
                    break;
                }

                // Update product stock
                $product = new Product($this->conn);
                $product->id = $this->product_id;
                if (!$product->updateStock($this->quantity)) {
                    $success = false;
                    break;
                }
            }

            if ($success) {
                // If successful, commit transaction and clear cart
                $this->conn->commit();
                
                // Clear user's cart
                $cart = new CartItem($this->conn);
                $cart->user_id = $user_id;
                $cart->clearCart();
                
                return true;
            } else {
                // If there was an error, rollback transaction
                $this->conn->rollBack();
                return false;
            }
        } catch (Exception $e) {
            // Rollback transaction on exception
            $this->conn->rollBack();
            return false;
        }
    }
}
?>
