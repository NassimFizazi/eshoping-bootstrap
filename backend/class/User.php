<?php
class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";

    // Object properties
    public $id;
    public $username;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $address;
    public $city;
    public $zip_code;
    public $phone;
    public $is_admin;
    public $created_at;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create user
    public function create() {
        // Hash the password
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                (username, email, password, first_name, last_name, address, city, zip_code, phone)
                VALUES
                (:username, :email, :password, :first_name, :last_name, :address, :city, :zip_code, :phone)";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->city = htmlspecialchars(strip_tags($this->city));
        $this->zip_code = htmlspecialchars(strip_tags($this->zip_code));
        $this->phone = htmlspecialchars(strip_tags($this->phone));

        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":zip_code", $this->zip_code);
        $stmt->bindParam(":phone", $this->phone);

        // Execute query
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Login user
    public function login() {
        // Query to read single record
        $query = "SELECT id, username, email, password, is_admin 
                  FROM " . $this->table_name . " 
                  WHERE username = :username OR email = :email";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);

        // Execute query
        $stmt->execute();

        // Get row count
        $num = $stmt->rowCount();

        // If user exists
        if($num > 0) {
            // Get record details
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password
            if(password_verify($this->password, $row['password'])) {
                // Set properties
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->is_admin = $row['is_admin'];
                return true;
            }
        }

        return false;
    }

    // Read single user
    public function readOne() {
        // Query to read single record
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Bind ID of record to retrieve
        $stmt->bindParam(1, $this->id);

        // Execute query
        $stmt->execute();

        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Set properties
        if($row) {
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->address = $row['address'];
            $this->city = $row['city'];
            $this->zip_code = $row['zip_code'];
            $this->phone = $row['phone'];
            $this->is_admin = $row['is_admin'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    // Update user
    public function update() {
        // If password is provided, update with new password
        if(!empty($this->password)) {
            // Hash the password
            $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

            // Update query with password
            $query = "UPDATE " . $this->table_name . "
                    SET username = :username, email = :email, password = :password,
                        first_name = :first_name, last_name = :last_name,
                        address = :address, city = :city, zip_code = :zip_code, phone = :phone
                    WHERE id = :id";

            // Prepare query
            $stmt = $this->conn->prepare($query);

            // Bind new password
            $stmt->bindParam(":password", $password_hash);
        } else {
            // Update query without password
            $query = "UPDATE " . $this->table_name . "
                    SET username = :username, email = :email,
                        first_name = :first_name, last_name = :last_name,
                        address = :address, city = :city, zip_code = :zip_code, phone = :phone
                    WHERE id = :id";

            // Prepare query
            $stmt = $this->conn->prepare($query);
        }

        // Sanitize input
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->city = htmlspecialchars(strip_tags($this->city));
        $this->zip_code = htmlspecialchars(strip_tags($this->zip_code));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":zip_code", $this->zip_code);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":id", $this->id);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete user
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

    // Read all users (admin functionality)
    public function readAll() {
        // Query
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Check if username exists
    public function usernameExists() {
        // Query to check if username exists
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = ? LIMIT 0,1";

        // Prepare the query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->username = htmlspecialchars(strip_tags($this->username));

        // Bind values
        $stmt->bindParam(1, $this->username);

        // Execute query
        $stmt->execute();

        // Get number of rows
        $num = $stmt->rowCount();

        return $num > 0;
    }

    // Check if email exists
    public function emailExists() {
        // Query to check if email exists
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";

        // Prepare the query
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind values
        $stmt->bindParam(1, $this->email);

        // Execute query
        $stmt->execute();

        // Get number of rows
        $num = $stmt->rowCount();

        return $num > 0;
    }
}
?>
