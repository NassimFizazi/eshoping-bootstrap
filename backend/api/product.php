<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and object files
include_once '../config/database.php';
include_once '../class/Product.php';
include_once '../utils/session.php';

// Start session
Session::start();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize product
$product = new Product($db);

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Process based on method
switch ($method) {
    case 'GET':
        // Check if ID is set for single product
        if (isset($_GET['id'])) {
            $product->id = $_GET['id'];
            
            // Read the details of the product
            if ($product->readOne()) {
                // Create product array
                $product_arr = array(
                    "id" => $product->id,
                    "name" => $product->name,
                    "description" => $product->description,
                    "price" => $product->price,
                    "image_url" => $product->image_url,
                    "category_id" => $product->category_id,
                    "category_name" => $product->category_name,
                    "stock_quantity" => $product->stock_quantity,
                    "created_at" => $product->created_at
                );
                
                // Set response code - 200 OK
                http_response_code(200);
                
                // Output JSON
                echo json_encode($product_arr);
            } else {
                // Set response code - 404 Not found
                http_response_code(404);
                
                // Tell the user product does not exist
                echo json_encode(array("message" => "Product not found."));
            }
        } 
        // Read all products with possible category filter
        else {
            // Set pagination parameters
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 8;
            $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
            
            // Search functionality
            if (isset($_GET['search'])) {
                $keywords = $_GET['search'];
                $stmt = $product->search($keywords, $page, $per_page);
                $total_rows = $product->getSearchTotal($keywords);
            } else {
                // Read products
                $stmt = $product->readAll($page, $per_page, $category_id);
                $total_rows = $product->getTotal($category_id);
            }
            
            // Get number of records
            $num = $stmt->rowCount();
            
            // Total pages
            $total_pages = ceil($total_rows / $per_page);
            
            // If products found
            if ($num > 0) {
                // Products array
                $products_arr = array();
                $products_arr["products"] = array();
                $products_arr["pagination"] = array(
                    "total_rows" => $total_rows,
                    "total_pages" => $total_pages,
                    "current_page" => $page,
                    "per_page" => $per_page
                );
                
                // Retrieve data
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $product_item = array(
                        "id" => $row['id'],
                        "name" => $row['name'],
                        "description" => $row['description'],
                        "price" => $row['price'],
                        "image_url" => $row['image_url'],
                        "category_id" => $row['category_id'],
                        "category_name" => $row['category_name'],
                        "stock_quantity" => $row['stock_quantity']
                    );
                    
                    array_push($products_arr["products"], $product_item);
                }
                
                // Set response code - 200 OK
                http_response_code(200);
                
                // Output JSON
                echo json_encode($products_arr);
            } else {
                // Set response code - 200 OK (but no results)
                http_response_code(200);
                
                // Tell the user no products found
                echo json_encode(array(
                    "message" => "No products found.",
                    "products" => array(),
                    "pagination" => array(
                        "total_rows" => 0,
                        "total_pages" => 0,
                        "current_page" => $page,
                        "per_page" => $per_page
                    )
                ));
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
        if (
            !empty($data->name) &&
            !empty($data->description) &&
            !empty($data->price) &&
            !empty($data->category_id) &&
            isset($data->stock_quantity)
        ) {
            // Set product property values
            $product->name = $data->name;
            $product->description = $data->description;
            $product->price = $data->price;
            $product->image_url = $data->image_url ?? "";
            $product->category_id = $data->category_id;
            $product->stock_quantity = $data->stock_quantity;
            
            // Create the product
            if ($product->create()) {
                // Set response code - 201 created
                http_response_code(201);
                
                // Tell the user
                echo json_encode(array("message" => "Product was created."));
            } else {
                // Set response code - 503 service unavailable
                http_response_code(503);
                
                // Tell the user
                echo json_encode(array("message" => "Unable to create product."));
            }
        } else {
            // Set response code - 400 bad request
            http_response_code(400);
            
            // Tell the user
            echo json_encode(array("message" => "Unable to create product. Data is incomplete."));
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
        $product->id = isset($_GET['id']) ? $_GET['id'] : die();
        
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
        // Check if data is complete
        if (
            !empty($data->name) &&
            !empty($data->description) &&
            !empty($data->price) &&
            !empty($data->category_id) &&
            isset($data->stock_quantity)
        ) {
            // Set product property values
            $product->name = $data->name;
            $product->description = $data->description;
            $product->price = $data->price;
            $product->image_url = $data->image_url ?? "";
            $product->category_id = $data->category_id;
            $product->stock_quantity = $data->stock_quantity;
            
            // Update the product
            if ($product->update()) {
                // Set response code - 200 OK
                http_response_code(200);
                
                // Tell the user
                echo json_encode(array("message" => "Product was updated."));
            } else {
                // Set response code - 503 service unavailable
                http_response_code(503);
                
                // Tell the user
                echo json_encode(array("message" => "Unable to update product."));
            }
        } else {
            // Set response code - 400 bad request
            http_response_code(400);
            
            // Tell the user
            echo json_encode(array("message" => "Unable to update product. Data is incomplete."));
        }
        break;

    case 'DELETE':
        // Check if user is admin
        if (!Session::isAdmin()) {
            http_response_code(403);
            echo json_encode(array("message" => "Permission denied."));
            exit;
        }
        
        // Get product ID
        $product->id = isset($_GET['id']) ? $_GET['id'] : die();
        
        // Delete the product
        if ($product->delete()) {
            // Set response code - 200 OK
            http_response_code(200);
            
            // Tell the user
            echo json_encode(array("message" => "Product was deleted."));
        } else {
            // Set response code - 503 service unavailable
            http_response_code(503);
            
            // Tell the user
            echo json_encode(array("message" => "Unable to delete product."));
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
