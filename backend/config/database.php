<?php
class Database {
    private $conn;

    // Get the database connection
    public function getConnection() {
        $this->conn = null;

        try {
            // Get environment variables for database connection
            $host = getenv('PGHOST');
            $port = getenv('PGPORT');
            $username = getenv('PGUSER');
            $password = getenv('PGPASSWORD');
            $db_name = getenv('PGDATABASE');
            
            if ($host && $port && $username && $password && $db_name) {
                // Connect using individual env vars
                $dsn = "pgsql:host=$host;port=$port;dbname=$db_name";
                $this->conn = new PDO($dsn, $username, $password);
                error_log("Connected to PostgreSQL database via individual env vars");
            } else {
                // Try using DATABASE_URL as fallback
                $database_url = getenv('DATABASE_URL');
                
                if ($database_url) {
                    // Parse the DATABASE_URL to get connection info
                    $db_parts = parse_url($database_url);
                    $host = $db_parts['host'];
                    $port = isset($db_parts['port']) ? $db_parts['port'] : '5432';
                    $username = $db_parts['user'];
                    $password = $db_parts['pass'];
                    $db_name = ltrim($db_parts['path'], '/');
                    
                    // Connect to PostgreSQL database
                    $dsn = "pgsql:host=$host;port=$port;dbname=$db_name";
                    $this->conn = new PDO($dsn, $username, $password);
                    error_log("Connected to PostgreSQL database via DATABASE_URL");
                } else {
                    error_log("No database connection info available");
                }
            }
            
            if ($this->conn) {
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
