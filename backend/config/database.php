<?php
class Database {
    private $conn;

    // Get the database connection
    public function getConnection() {
        $this->conn = null;

        try {
            // Get database connection parameters from environment variables
            $database_url = getenv('DATABASE_URL');
            
            if ($database_url) {
                // Parse the DATABASE_URL to get connection info
                $db_parts = parse_url($database_url);
                $host = $db_parts['host'];
                $port = isset($db_parts['port']) ? $db_parts['port'] : '5432';
                $username = $db_parts['user'];
                $password = $db_parts['pass'];
                $db_name = ltrim($db_parts['path'], '/');
                $sslmode = strpos($database_url, 'sslmode=require') !== false ? 'require' : 'prefer';
                
                // Connect to PostgreSQL database
                $dsn = "pgsql:host=$host;port=$port;dbname=$db_name;sslmode=$sslmode";
                $this->conn = new PDO($dsn, $username, $password);
            } else {
                // Fallback to individual environment variables
                $host = getenv('PGHOST') ?: 'localhost';
                $port = getenv('PGPORT') ?: '5432';
                $username = getenv('PGUSER') ?: 'postgres';
                $password = getenv('PGPASSWORD') ?: '';
                $db_name = getenv('PGDATABASE') ?: 'postgres';
                
                // Connect to PostgreSQL database
                $dsn = "pgsql:host=$host;port=$port;dbname=$db_name";
                $this->conn = new PDO($dsn, $username, $password);
            }
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
