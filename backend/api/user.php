<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and object files
include_once '../config/database.php';
include_once '../class/User.php';
include_once '../utils/session.php';
include_once '../utils/validator.php';

// Start session
Session::start();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user
$user = new User($db);

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Process based on method
switch ($method) {
    case 'GET':
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(array("message" => "Unauthorized"));
            exit;
        }

        // Get user ID from session
        $user->id = Session::getUserId();
        
        // Read user data
        if ($user->readOne()) {
            // Create user array
            $user_arr = array(
                "id" => $user->id,
                "username" => $user->username,
                "email" => $user->email,
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "address" => $user->address,
                "city" => $user->city,
                "zip_code" => $user->zip_code,
                "phone" => $user->phone,
                "is_admin" => $user->is_admin,
                "created_at" => $user->created_at
            );

            // Set response code - 200 OK
            http_response_code(200);
            
            // Output JSON
            echo json_encode($user_arr);
        } else {
            // Set response code - 404 Not found
            http_response_code(404);
            
            // Tell the user no products found
            echo json_encode(array("message" => "User not found."));
        }
        break;

    case 'POST':
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
        // Check if user is registering or logging in
        if (isset($data->action)) {
            if ($data->action === "register") {
                // Validate input
                $validator = new Validator();
                $validator->required("username", $data->username ?? "");
                $validator->required("email", $data->email ?? "");
                $validator->required("password", $data->password ?? "");
                $validator->email("email", $data->email ?? "");
                $validator->minLength("password", $data->password ?? "", 6);
                
                if ($validator->hasErrors()) {
                    http_response_code(400);
                    echo json_encode(array("errors" => $validator->getErrors()));
                    exit;
                }
                
                // Check if username or email already exists
                $user->username = $data->username;
                $user->email = $data->email;
                
                if ($user->usernameExists()) {
                    http_response_code(400);
                    echo json_encode(array("message" => "Username already exists."));
                    exit;
                }
                
                if ($user->emailExists()) {
                    http_response_code(400);
                    echo json_encode(array("message" => "Email already exists."));
                    exit;
                }
                
                // Set user properties
                $user->password = $data->password;
                $user->first_name = $data->first_name ?? "";
                $user->last_name = $data->last_name ?? "";
                $user->address = $data->address ?? "";
                $user->city = $data->city ?? "";
                $user->zip_code = $data->zip_code ?? "";
                $user->phone = $data->phone ?? "";
                
                // Create user
                if ($user->create()) {
                    // Set session
                    Session::setUser($user->id, $user->username, $user->email);
                    
                    // Set response code - 201 created
                    http_response_code(201);
                    
                    // Tell the user
                    echo json_encode(array("message" => "User was created.", "user_id" => $user->id));
                } else {
                    // Set response code - 503 service unavailable
                    http_response_code(503);
                    
                    // Tell the user
                    echo json_encode(array("message" => "Unable to create user."));
                }
            } 
            elseif ($data->action === "login") {
                // Validate input
                $validator = new Validator();
                $validator->required("username", $data->username ?? "");
                $validator->required("password", $data->password ?? "");
                
                if ($validator->hasErrors()) {
                    http_response_code(400);
                    echo json_encode(array("errors" => $validator->getErrors()));
                    exit;
                }
                
                // Set user properties
                $user->username = $data->username;
                $user->password = $data->password;
                
                // Attempt login
                if ($user->login()) {
                    // Set session
                    Session::setUser($user->id, $user->username, $user->email, $user->is_admin);
                    
                    // Set response code - 200 OK
                    http_response_code(200);
                    
                    // Tell the user
                    echo json_encode(array(
                        "message" => "Login successful.",
                        "user_id" => $user->id,
                        "username" => $user->username,
                        "is_admin" => $user->is_admin
                    ));
                } else {
                    // Set response code - 401 Unauthorized
                    http_response_code(401);
                    
                    // Tell the user
                    echo json_encode(array("message" => "Invalid username or password."));
                }
            }
            elseif ($data->action === "logout") {
                // Destroy session
                Session::destroy();
                
                // Set response code - 200 OK
                http_response_code(200);
                
                // Tell the user
                echo json_encode(array("message" => "Logout successful."));
            }
        } else {
            // Set response code - 400 bad request
            http_response_code(400);
            
            // Tell the user
            echo json_encode(array("message" => "Action not specified."));
        }
        break;

    case 'PUT':
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(array("message" => "Unauthorized"));
            exit;
        }

        // Get user ID from session
        $user->id = Session::getUserId();
        
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
        // Validate input
        $validator = new Validator();
        $validator->required("username", $data->username ?? "");
        $validator->required("email", $data->email ?? "");
        $validator->email("email", $data->email ?? "");
        
        if ($validator->hasErrors()) {
            http_response_code(400);
            echo json_encode(array("errors" => $validator->getErrors()));
            exit;
        }
        
        // Set user properties
        $user->username = $data->username;
        $user->email = $data->email;
        $user->password = $data->password ?? "";
        $user->first_name = $data->first_name ?? "";
        $user->last_name = $data->last_name ?? "";
        $user->address = $data->address ?? "";
        $user->city = $data->city ?? "";
        $user->zip_code = $data->zip_code ?? "";
        $user->phone = $data->phone ?? "";
        
        // Update user
        if ($user->update()) {
            // Update session info
            Session::setUser($user->id, $user->username, $user->email, Session::isAdmin());
            
            // Set response code - 200 OK
            http_response_code(200);
            
            // Tell the user
            echo json_encode(array("message" => "User was updated."));
        } else {
            // Set response code - 503 service unavailable
            http_response_code(503);
            
            // Tell the user
            echo json_encode(array("message" => "Unable to update user."));
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
