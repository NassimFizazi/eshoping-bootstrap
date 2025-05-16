<?php
class Order {
    // Database connection and table name
    private $conn;
    private $table_name = "orders";

    // Object properties
    public $id;
    public $user_id;
    public $total_amount;
    public $status;
    public $shipping_address;
    public $shipping_city;
    public $shipping_zip_code;
    public $shipping_phone;
    public $payment_method;
    public $created_at;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create order
    public function create() {
        // Insert query
        $query = "INSERT INTO " . $this->table_name . " 
                (user_id, total_amount, status, shipping_address, shipping_city, 
                 shipping_zip_code, shipping_phone, payment_method) 
                VALUES
                (:user_id, :total_amount, :status, :shipping_address, :shipping_city, 
                 :shipping_zip_code, :shipping_phone, :payment_method)";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->shipping_address = htmlspecialchars(strip_tags($this->shipping_address));
        $this->shipping_city = htmlspecialchars(strip_tags($this->shipping_city));
        $this->shipping_zip_code = htmlspecialchars(strip_tags($this->shipping_zip_code));
        $this->shipping_phone = htmlspecialchars(strip_tags($this->shipping_phone));
        $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":shipping_address", $this->shipping_address);
        $stmt->bindParam(":shipping_city", $this->shipping_city);
        $stmt->bindParam(":shipping_zip_code", $this->shipping_zip_code);
        $stmt->bindParam(":shipping_phone", $this->shipping_phone);
        $stmt->bindParam(":payment_method", $this->payment_method);

        // Execute query
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Read all orders for a user
    public function readUserOrders() {
        // Query
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id
                  ORDER BY created_at DESC";

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

    // Read single order with details
    public function readOne() {
        // Query
        $query = "SELECT * FROM " . $this->table_name . " 
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
        $stmt->execute();

        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Set properties
        if($row) {
            $this->total_amount = $row['total_amount'];
            $this->status = $row['status'];
            $this->shipping_address = $row['shipping_address'];
            $this->shipping_city = $row['shipping_city'];
            $this->shipping_zip_code = $row['shipping_zip_code'];
            $this->shipping_phone = $row['shipping_phone'];
            $this->payment_method = $row['payment_method'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    // Read all orders (admin functionality)
    public function readAll() {
        // Query
        $query = "SELECT o.*, u.username, u.email  
                  FROM " . $this->table_name . " o
                  JOIN users u ON o.user_id = u.id
                  ORDER BY o.created_at DESC";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Update order status (admin functionality)
    public function updateStatus() {
        // Query
        $query = "UPDATE " . $this->table_name . "
                  SET status = :status
                  WHERE id = :id";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete order (admin functionality)
    public function delete() {
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
}
?>
