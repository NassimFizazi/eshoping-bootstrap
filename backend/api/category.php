<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and object files
include_once '../config/database.php';
include_once '../class/Category.php';
include_once '../utils/session.php';

// Start session
Session::start();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize category
$category = new Category($db);

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Process based on method
switch ($method) {
    case 'GET':
        // Check if ID is set for single category
        if (isset($_GET['id'])) {
            $category->id = $_GET['id'];
            
            // Read the details of the category
            if ($category->readOne()) {
                // Create category array
                $category_arr = array(
                    "id" => $category->id,
                    "name" => $category->name,
                    "description" => $category->description,
                    "created_at" => $category->created_at
                );
                
                // Set response code - 200 OK
                http_response_code(200);
                
                // Make it json format
                echo json_encode($category_arr);
            } else {
                // Set response code - 404 Not found
                http_response_code(404);
                
                // Tell the user category does not exist
                echo json_encode(array("message" => "Category not found."));
            }
        } 
        // Read all categories
        else {
            // Get all categories
            $stmt = $category->readAll();
            $num = $stmt->rowCount();
            
            // Check if more than 0 record found
            if ($num > 0) {
                // Categories array
                $categories_arr = array();
                $categories_arr["categories"] = array();
                
                // Retrieve table contents
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $category_item = array(
                        "id" => $id,
                        "name" => $name,
                        "description" => $description,
                        "created_at" => $created_at
                    );
                    
                    array_push($categories_arr["categories"], $category_item);
                }
                
                // Set response code - 200 OK
                http_response_code(200);
                
                // Output JSON
                echo json_encode($categories_arr);
            } else {
                // Set response code - 404 Not found
                http_response_code(404);
                
                // Tell the user no categories found
                echo json_encode(array("message" => "No categories found."));
            }
        }
        break;

    case 'POST':
        // Check if user is admin
        if (!Session::isAdmin()) {
            http_response_code(403);
            echo json_encode(array("message" => "Permission denied."));
            exit;
        }
        
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
        // Check if data is complete
        if (!empty($data->name)) {
            // Set category property values
            $category->name = $data->name;
            $category->description = $data->description ?? "";
            
            // Create the category
            if ($category->create()) {
                // Set response code - 201 created
                http_response_code(201);
                
                // Tell the user
                echo json_encode(array("message" => "Category was created."));
            } else {
                // Set response code - 503 service unavailable
                http_response_code(503);
                
                // Tell the user
                echo json_encode(array("message" => "Unable to create category."));
            }
        } else {
            // Set response code - 400 bad request
            http_response_code(400);
            
            // Tell the user
            echo json_encode(array("message" => "Unable to create category. Data is incomplete."));
        }
        break;

    case 'PUT':
        // Check if user is admin
        if (!Session::isAdmin()) {
            http_response_code(403);
            echo json_encode(array("message" => "Permission denied."));
            exit;
        }
        
        // Get ID from URL
        $category->id = isset($_GET['id']) ? $_GET['id'] : die();
        
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
        // Check if data is complete
        if (!empty($data->name)) {
            // Set category property values
            $category->name = $data->name;
            $category->description = $data->description ?? "";
            
            // Update the category
            if ($category->update()) {
                // Set response code - 200 OK
                http_response_code(200);
                
                // Tell the user
                echo json_encode(array("message" => "Category was updated."));
            } else {
                // Set response code - 503 service unavailable
                http_response_code(503);
                
                // Tell the user
                echo json_encode(array("message" => "Unable to update category."));
            }
        } else {
            // Set response code - 400 bad request
            http_response_code(400);
            
            // Tell the user
            echo json_encode(array("message" => "Unable to update category. Data is incomplete."));
        }
        break;

    case 'DELETE':
        // Check if user is admin
        if (!Session::isAdmin()) {
            http_response_code(403);
            echo json_encode(array("message" => "Permission denied."));
            exit;
        }
        
        // Get category ID
        $category->id = isset($_GET['id']) ? $_GET['id'] : die();
        
        // Delete the category
        if ($category->delete()) {
            // Set response code - 200 OK
            http_response_code(200);
            
            // Tell the user
            echo json_encode(array("message" => "Category was deleted."));
        } else {
            // Set response code - 503 service unavailable
            http_response_code(503);
            
            // Tell the user
            echo json_encode(array("message" => "Unable to delete category."));
        }
        break;

    default:
        // Set response code - 405 Method not allowed
        http_response_code(405);
        
        // Tell the user
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>
