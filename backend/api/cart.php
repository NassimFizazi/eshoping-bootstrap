<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and object files
include_once '../config/database.php';
include_once '../class/CartItem.php';
include_once '../class/Product.php';
include_once '../utils/session.php';

// Start session
Session::start();

// Check if user is logged in
if (!Session::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(array("message" => "Unauthorized"));
    exit;
}

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize cart item
$cart_item = new CartItem($db);
$cart_item->user_id = Session::getUserId();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Process based on method
switch ($method) {
    case 'GET':
        // Get user's cart
        $stmt = $cart_item->getUserCart();
        $num = $stmt->rowCount();
        
        // Get cart total
        $cart_total = $cart_item->getCartTotal();
        
        // Check if cart has items
        if ($num > 0) {
            // Cart array
            $cart_arr = array();
            $cart_arr["items"] = array();
            $cart_arr["total"] = $cart_total;
            
            // Retrieve data
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $item = array(
                    "id" => $row['id'],
                    "product_id" => $row['product_id'],
                    "quantity" => $row['quantity'],
                    "product_name" => $row['product_name'],
                    "product_price" => $row['product_price'],
                    "product_image" => $row['product_image'],
                    "subtotal" => $row['product_price'] * $row['quantity']
                );
                
                array_push($cart_arr["items"], $item);
            }
            
            // Set response code - 200 OK
            http_response_code(200);
            
            // Output JSON
            echo json_encode($cart_arr);
        } else {
            // Set response code - 200 OK (but empty cart)
            http_response_code(200);
            
            // Tell the user cart is empty
            echo json_encode(array(
                "items" => array(),
                "total" => 0,
                "message" => "Cart is empty."
            ));
        }
        break;

    case 'POST':
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
        // Check if data is complete
        if (!empty($data->product_id) && isset($data->quantity) && $data->quantity > 0) {
            // Set cart item properties
            $cart_item->product_id = $data->product_id;
            $cart_item->quantity = $data->quantity;
            
            // First check if the product exists and has sufficient stock
            $product = new Product($db);
            $product->id = $cart_item->product_id;
            
            if (!$product->readOne()) {
                // Set response code - 404 Not found
                http_response_code(404);
                
                // Tell the user
                echo json_encode(array("message" => "Product not found."));
                break;
            }
            
            if (!$product->checkStock($cart_item->quantity)) {
                // Set response code - 400 Bad request
                http_response_code(400);
                
                // Tell the user
                echo json_encode(array("message" => "Insufficient stock. Available: " . $product->stock_quantity));
                break;
            }
            
            // Add to cart
            if ($cart_item->addToCart()) {
                // Get updated cart count
                $cart_count = $cart_item->countItems();
                
                // Set response code - 201 created
                http_response_code(201);
                
                // Tell the user
                echo json_encode(array(
                    "message" => "Product added to cart.",
                    "cart_count" => $cart_count
                ));
            } else {
                // Set response code - 503 service unavailable
                http_response_code(503);
                
                // Tell the user
                echo json_encode(array("message" => "Unable to add item to cart."));
            }
        } else {
            // Set response code - 400 bad request
            http_response_code(400);
            
            // Tell the user
            echo json_encode(array("message" => "Unable to add item to cart. Data is incomplete."));
        }
        break;

    case 'PUT':
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
        // Check if data is complete
        if (!empty($data->id) && isset($data->quantity) && $data->quantity > 0) {
            // Set cart item properties
            $cart_item->id = $data->id;
            $cart_item->quantity = $data->quantity;
            
            // Update cart item
            if ($cart_item->updateQuantity()) {
                // Set response code - 200 OK
                http_response_code(200);
                
                // Tell the user
                echo json_encode(array("message" => "Cart item was updated."));
            } else {
                // Set response code - 503 service unavailable
                http_response_code(503);
                
                // Tell the user
                echo json_encode(array("message" => "Unable to update cart item."));
            }
        } else {
            // Set response code - 400 bad request
            http_response_code(400);
            
            // Tell the user
            echo json_encode(array("message" => "Unable to update cart item. Data is incomplete."));
        }
        break;

    case 'DELETE':
        // Check if cart item ID is provided
        if (isset($_GET['id'])) {
            // Set cart item ID
            $cart_item->id = $_GET['id'];
            
            // Remove from cart
            if ($cart_item->removeFromCart()) {
                // Set response code - 200 OK
                http_response_code(200);
                
                // Tell the user
                echo json_encode(array("message" => "Item was removed from cart."));
            } else {
                // Set response code - 503 service unavailable
                http_response_code(503);
                
                // Tell the user
                echo json_encode(array("message" => "Unable to remove item from cart."));
            }
        } 
        // Check if clear cart action
        elseif (isset($_GET['action']) && $_GET['action'] === 'clear') {
            // Clear cart
            if ($cart_item->clearCart()) {
                // Set response code - 200 OK
                http_response_code(200);
                
                // Tell the user
                echo json_encode(array("message" => "Cart was cleared."));
            } else {
                // Set response code - 503 service unavailable
                http_response_code(503);
                
                // Tell the user
                echo json_encode(array("message" => "Unable to clear cart."));
            }
        } else {
            // Set response code - 400 bad request
            http_response_code(400);
            
            // Tell the user
            echo json_encode(array("message" => "Unable to remove item. No ID specified."));
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
