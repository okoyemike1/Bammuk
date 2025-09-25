<?php
require_once "../data/Crud.data.php";

class Models
{
    public $table;
    private $conn;
    protected static $crud;

    public function __construct()
    {
        self::$crud = new Crud();
    }
    
    public function createUser($data) {
        $password = password_hash($data["password"], PASSWORD_DEFAULT);
        $result = self::$crud->create("users", [
            "email" => $data["email"],
            "password" => $password,
            "role" => isset($data["role"]) ? $data["role"] : 'Reviewer',
            "name" => isset($data["name"]) ? $data["name"] : null,
            "profile_pic" => isset($data["profile_pic"]) ? $data["profile_pic"] : null
        ]);
        return $result;
    }
    
    public function getUserByEmail($email) {
        $result = self::$crud->findByEmail("users", $email);
        if ($result) {
            return $result;
        } else {
            return null;
        }
    }
    

    /**
     * Log in a user with email and password
     * @param array $data Contains 'email' and 'password'
     * @return array Returns an array with status code and user data if login is successful, or an error message if not.
     * @throws Exception If the user is not found or the password is incorrect.
     */
    public function loginUser($data) {
        $result = self::$crud->findByEmail("users", $data["email"]);
        if (is_array($result) && isset($result['email'])){
            $hashedPassword = $result['password'];
            if (password_verify($data["password"], $hashedPassword)) {
                return ['statuscode' => 200, 'data'=>$result]; // Login successful
            } else {
                return ['statuscode' => 404, 'data' => []]; // Invalid password
            }
        } else {
            return ['statuscode' => 404, 'data' => []]; // User not found
        }
    }

    /**
     * Satrt a session for the user
     * @param array $userData Contains user data to be stored in the session
     * @return string|array Returns a JSON response with the session data or an error message.
     * @throws Exception If the session cannot be started.
     */
    public function startSession($userData) {
       $result = self::$crud->create("sessions", $userData);
       if ($result) {
            return [
                'statuscode' => 200,
                'status' => 'Session started successfully.',
                'data' => $userData
            ];
       } else {
            return [
                'statuscode' => 500,
                'status' => 'Failed to start session.',
                'data' => []
            ];
        }
    }

    /** 
     * Get session data by session ID
     * @param string $sessionId The session ID to search for
     * @return array Returns an array with session data if found, or an error message if not found.
     * @throws Exception If the session ID is not provided or if the session cannot be found.
     */
    public function getSessionById($sessionId)
    {
        $result = self::$crud->findOne("sessions", ['session_id' => $sessionId], ['*']);
        if (is_array($result)) {
            $get_user = self::$crud->findOne("users", ['id' => $result["user_id"]]);
            $user = (is_array($get_user)) ? $get_user : [];
            return [
                'statuscode' => 200,
                'status' => 'Session found.',
                'data' => $user,
                'session' => $result
            ];
        } else {
            return [
                'statuscode' => 404,
                'status' => 'Session not found.',
                'data' => []
            ];
        }
    }

    public function endSession($sessionId)
    {
        return self::$crud->update("sessions", [
            'session_status' => 'inactive',
            'session_end_time' => date('Y-m-d H:i:s')
        ], ['session_id' => $sessionId]);
    }

    // OTP helpers
    public function createOtp($userId, $code, $expiresAt) {
        return self::$crud->create("otp", [
            'user_id' => $userId,
            'code' => $code,
            'expires_at' => $expiresAt,
            'used' => 0
        ]);
    }

    public function verifyOtpCode($userId, $code) {
        $otp = self::$crud->findOne("otp", ['user_id' => $userId, 'code' => $code, 'used' => 0]);
        if (!$otp) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        if ($otp['expires_at'] < $now) {
            return false;
        }
        self::$crud->update("otp", ['used' => 1], ['id' => $otp['id']]);
        self::$crud->update("users", ['is_verified' => 1], ['id' => $userId]);
        return true;
    }

    // Books
    public function createBook($authorId, $title, $description = null, $content = null) {
        return self::$crud->create("books", [
            'author_id' => $authorId,
            'title' => $title,
            'description' => $description,
            'content' => $content
        ]);
    }

    public function addInvitation($bookId, $inviteeEmail, $invitedBy) {
        return self::$crud->create("invitations", [
            'book_id' => $bookId,
            'invitee_email' => $inviteeEmail,
            'invited_by' => $invitedBy,
            'status' => 'pending'
        ]);
    }

    public function getBookReviews($bookId) {
        $sql = "SELECT r.*, u.email AS reviewer_email FROM reviews r JOIN users u ON u.id = r.reviewer_id WHERE r.book_id = ? ORDER BY r.created_at DESC";
        $stmt = DBConfig::connect()->prepare($sql);
        $stmt->execute([$bookId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Reviews
    public function addReview($bookId, $reviewerId, $comment, $rating = null) {
        return self::$crud->create("reviews", [
            'book_id' => $bookId,
            'reviewer_id' => $reviewerId,
            'comment' => $comment,
            'rating' => $rating
        ]);
    }

    public function getBooksReviewedByUser($reviewerId) {
        $sql = "SELECT b.* FROM reviews r JOIN books b ON b.id = r.book_id WHERE r.reviewer_id = ? GROUP BY b.id ORDER BY MAX(r.created_at) DESC";
        $stmt = DBConfig::connect()->prepare($sql);
        $stmt->execute([$reviewerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMyReviews($reviewerId) {
        $sql = "SELECT r.*, b.title FROM reviews r JOIN books b ON b.id = r.book_id WHERE r.reviewer_id = ? ORDER BY r.created_at DESC";
        $stmt = DBConfig::connect()->prepare($sql);
        $stmt->execute([$reviewerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Users
    public function updateUserProfile($userId, $name = null, $profilePic = null) {
        $data = [];
        if (!is_null($name)) { $data['name'] = $name; }
        if (!is_null($profilePic)) { $data['profile_pic'] = $profilePic; }
        if (empty($data)) { return false; }
        return self::$crud->update("users", $data, ['id' => $userId]);
    }
}