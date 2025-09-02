<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

require_once "../models/model.php";
require_once "../helpers/Utility.helper.php";

/**
 * Authentication Controller class
 */
class AuthController {
    private static $data;
    private $model;
    private $utility;

    /**
     * Class Constructor
     * 
     * @return void
     */
    public function __construct($data = []) {
        self::$data = $data;
        $this->model = new Models();
        $this->utility = new UtilityHelper();
    }
  
    /**
     * Register a new user
     * 
     * @return string|array
     * This method validates the registration data and creates a new user if valid.
     */
    public function register():string|array
    {
      
        $require = ["email", "password", "confirm_password"];
        $validate = $this->utility->validateFields(self::$data, $require);
        if ($validate["error"]) {
            return ['statuscode' => 401, 'status' => $validate['error_msg'], 'data' => []];
        } else {
            $data = $validate["data"];

            // Check password confirmation
if ($data["password"] !== $data["confirm_password"]) {
    return $this->utility->jsonResponse(
        400,
        "Password and confirm password do not match.",
        []
    );
}

// Remove confirm_password before saving
unset($data["confirm_password"]);

            if ($this->model->createUser($data)) {
                $respons = $this->utility->jsonResponse(
                    200,
                    "User registered successfully.",
                    $data
                );
            } else {
                $respons = $this->utility->jsonResponse(
                    404,
                    "Failed to register user.",
                    []
                );
            }
        }
        return $respons;
    }

    /**
     * Log in a user
     * 
     * @return string|array
     * This method validates the login credentials and starts a session if successful.
     */
    public function login():string|array
    {
        $require = ["email", "password"];
        $validate = $this->utility->validateFields(self::$data, $require);
        if ($validate["error"]) {
            $response = ['statuscode' => 401, 'status' => $validate['error_msg'], 'data' => []];
        } else {
            $data = $validate["data"];
            $result = $this->model->loginUser($data);
            if ($result["statuscode"] === 200) {
                $user = $result["data"];
                $session = $this->startSession(['user_id' => $user["id"]]);
                if (!$session["status"]) {
                    $response = $this->utility->jsonResponse(
                        500,
                        "Failed to login.",
                        []
                    );
                } else {
                    $session_data = [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'session_id' => $session["data"]['session_id'],
                        'session_start_time' => $session["data"]["session_start_time"],
                        'session_token' => $session["data"]["session_token"]
                    ];
                    $response = $this->utility->jsonResponse(
                        200,
                        "Login successful.",
                        $session_data
                    );
                }
            } else {
                $response = $this->utility->jsonResponse(
                    404,
                    "Invalid email or password.",
                    []
                );
            }
        }
        return $response;
    }

    /**
     * Start session
     * @return string|array
     */
    public function startSession($session): string|array
    {
        session_start();
        $session["session_id"] = session_id(); // Store session ID
        $session["user_agent"] = $_SERVER["HTTP_USER_AGENT"];
        $session["ip_address"] = $_SERVER["REMOTE_ADDR"];
        $session["session_token"] = bin2hex(random_bytes(32)); // Generate a secure session token
        $session["session_status"] = "active"; // Set session status to active
        $session["session_start_time"] = date("Y-m-d H:i:s", time());

        $result = $this->model->startSession($session);
        if (!$result) {
            return ['status' => false];
        } else {
            $_SESSION = [
                // 'id' => $session['user_id'],
                // 'first_name' => $session['first_name'],
                // 'last_name' => $session['last_name'],
                // 'email' => $session['email'],
                // 'phone' => $session['phone'],
                'session_id' => session_id(),
                'session_start_time' => $session["session_start_time"],
                'session_token' => $session["session_token"]
            ];
            return ['status' =>true, 'data' => $_SESSION];
        }
    }

    /**
     * verify if user is logged in
     * 
     * @return string|array
     * This method checks if a user is logged in by verifying the session.
     * It returns true if the session is valid, otherwise false.
     */

   public function isLoggedIn(): string|array
    {
        $this->data;
        if (isset(self::$data['id']) || isset(self::$data['session_id'])) {
            $result = $this->model->getSessionById(self::$data['id']);
            if (!$result) {
                session_destroy(); // Session is invalid, destroy it
                $_SESSION = []; // Clear session data
                $response = ['statuscode' => 404, 'status' => 'Session expired or invalid.']; // Session expired or invalid
            } else {
                $response = ['statuscode' => 200, 'status' => 'User is logged in.', 'data' => $result];
            }
        } else {
            session_destroy(); // No session data, destroy it   
            $_SESSION = []; // Clear session data
            $response = ['statuscode' => 404, 'status' => 'No session']; // No session data, user is not logged in
        }
        return $response;
    }





    // public function isLoggedIn(): string|array
    // {
    //     $this->data;
    //     if (isset($data['id']) || isset($data['session_id'])) {
    //         $result = $this->model->getSessionById("sessions", $data['id']);
    //         if (!$result) {
    //             session_destroy(); // Session is invalid, destroy it
    //             $_SESSION = []; // Clear session data
    //             $response = ['statuscode' => 404, 'status' => 'Session expired or invalid.']; // Session expired or invalid
    //         } else {
    //             $response = ['statuscode' => 200, 'status' => 'User is logged in.', 'data' => $result];
    //         }
    //     } else {
    //         session_destroy(); // No session data, destroy it   
    //         $_SESSION = []; // Clear session data
    //         $response = ['statuscode' => 404, 'status' => 'No session']; // No session data, user is not logged in
    //     }
    //     return $response;
    // }
}