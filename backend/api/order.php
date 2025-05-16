<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and object files
include_once '../config/database.php';
include_once '../class/Order.php';
include_once '../class/OrderItem.php';
include_once '../class/CartItem.php';
include_once '../utils/session.php';
include_once '../utils/validator.php';

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

// Initialize order
$order = new Order($db);
$order->user_id = Session::getUserId();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Process based on method
switch ($method) {
    case 'GET':
        // Check if ID is set for single order
        if (isset($_GET['id'])) {
            $order->id = $_GET['id'];
            
            // Read the details of the order
            if ($order->readOne()) {
                // Create order array
                $order_arr = array(
                    "id" => $order->id,
                    "total_amount" => $order->total_amount,
                    "status" => $order->status,
                    "shipping_address" => $order->shipping_address,
                    "shipping_city" => $order->shipping_city,
                    "shipping_zip_code" => $order->shipping_zip_code,
                    "shipping_phone" => $order->shipping_phone,
                    "payment_method" => $order->payment_method,
                    "created_at" => $order->created_at,
                    "items" => array()
                );
                
                // Get order items
                $order_item = new OrderItem($db);
                $order_item->order_id = $order->id;
                $stmt = $order_item->readOrderItems();
                
                // Add items to order array
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $item = array(
                        "id" => $row['id'],
                        "product_id" => $row['product_id'],
                        "quantity" => $row['quantity'],
                        "price" => $row['price'],
                        "product_name" => $row['product_name'],
                        "product_image" => $row['product_image'],
                        "subtotal" => $row['price'] * $row['quantity']
                    );
                    
                    array_push($order_arr["items"], $item);
                }
                
                // Set response code - 200 OK
                http_response_code(200);
                
                // Output JSON
                echo json_encode($order_arr);
            } else {
                // Set response code - 404 Not found
                http_response_code(404);
                
                // Tell the user order does not exist or doesn't belong to the user
                echo json_encode(array("message" => "Order not found."));
            }
        } 
        // Read all orders for the user
        else {
            // Get all orders for the user
            $stmt = $order->readUserOrders();
            $num = $stmt->rowCount();
            
            // Check if more than 0 record found
            if ($num > 0) {
                // Orders array
                $orders_arr = array();
                $orders_arr["orders"] = array();
                
                // Retrieve table contents
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $order_item = array(
                        "id" => $row['id'],
                        "total_amount" => $row['total_amount'],
                        "status" => $row['status'],
                        "created_at" => $row['created_at']
                    );
                    
                    array_push($orders_arr["orders"], $order_item);
                }
                
                // Set response code - 200 OK
                http_response_code(200);
                
                // Output JSON
                echo json_encode($orders_arr);
            } else {
                // Set response code - 200 OK (but no orders)
                http_response_code(200);
                
                // Tell the user no orders found
                echo json_encode(array(
                    "message" => "No orders found.",
                    "orders" => array()
                ));
            }
        }
        break;

    case 'POST':
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
        // Validate input
        $validator = new Validator();
        $validator->required("shipping_address", $data->shipping_address ?? "");
        $validator->required("shipping_city", $data->shipping_city ?? "");
        $validator->required("shipping_zip_code", $data->shipping_zip_code ?? "");
        $validator->required("shipping_phone", $data->shipping_phone ?? "");
        $validator->required("payment_method", $data->payment_method ?? "");
        
        if ($validator->hasErrors()) {
            http_response_code(400);
            echo json_encode(array("errors" => $validator->getErrors()));
            exit;
        }
        
        // First check if cart has items
        $cart_item = new CartItem($db);
        $cart_item->user_id = $order->user_id;
        $cart_count = $cart_item->countItems();
        
        if ($cart_count <= 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Cart is empty."));
            exit;
        }
        
        // Get cart total
        $cart_total = $cart_item->getCartTotal();
        
        // Set order properties
        $order->total_amount = $cart_total;
        $order->status = "pending";
        $order->shipping_address = $data->shipping_address;
        $order->shipping_city = $data->shipping_city;
        $order->shipping_zip_code = $data->shipping_zip_code;
        $order->shipping_phone = $data->shipping_phone;
        $order->payment_method = $data->payment_method;
        
        // Create the order
        if ($order->create()) {
            // Create order items from cart
            $order_item = new OrderItem($db);
            if ($order_item->createFromCart($order->user_id, $order->id)) {
                // Set response code - 201 created
                http_response_code(201);
                
                // Tell the user
                echo json_encode(array(
                    "message" => "Order was created.",
                    "order_id" => $order->id
                ));
            } else {
                // Set response code - 503 service unavailable
                http_response_code(503);
                
                // Tell the user
                echo json_encode(array("message" => "Unable to create order items."));
            }
        } else {
            // Set response code - 503 service unavailable
            http_response_code(503);
            
            // Tell the user
            echo json_encode(array("message" => "Unable to create order."));
        }
        break;

    case 'PUT':
        // Only admin can update order status
        if (!Session::isAdmin()) {
            http_response_code(403);
            echo json_encode(array("message" => "Permission denied."));
            exit;
        }
        
        // Get ID from URL
        $order->id = isset($_GET['id']) ? $_GET['id'] : die();
        
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
        // Check if status is provided
        if (!empty($data->status)) {
            // Set order property values
            $order->status = $data->status;
            
            // Update the order
            if ($order->updateStatus()) {
                // Set response code - 200 OK
                http_response_code(200);
                
                // Tell the user
                echo json_encode(array("message" => "Order status was updated."));
            } else {
                // Set response code - 503 service unavailable
                http_response_code(503);
                
                // Tell the user
                echo json_encode(array("message" => "Unable to update order status."));
            }
        } else {
            // Set response code - 400 bad request
            http_response_code(400);
            
            // Tell the user
            echo json_encode(array("message" => "Unable to update order. Status is required."));
        }
        break;

    case 'DELETE':
        // Only admin can delete orders
        if (!Session::isAdmin()) {
            http_response_code(403);
            echo json_encode(array("message" => "Permission denied."));
            exit;
        }
        
        // Get order ID
        $order->id = isset($_GET['id']) ? $_GET['id'] : die();
        
        // Delete the order
        if ($order->delete()) {
            // Set response code - 200 OK
            http_response_code(200);
            
            // Tell the user
            echo json_encode(array("message" => "Order was deleted."));
        } else {
            // Set response code - 503 service unavailable
            http_response_code(503);
            
            // Tell the user
            echo json_encode(array("message" => "Unable to delete order."));
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
