<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

require_once "../models/model.php";
require_once "../helpers/Utility.helper.php";
require_once "../helpers/jwt.helper.php";

class TasksController
{
    private static $data;
    private $model;
    private $utility;
    private $jwt;

    public function __construct($data = [])
    {
        self::$data = $data;
        $this->model = new Models();
        $this->utility = new UtilityHelper();
        $this->jwt = new JWTHelper("your_super_secret_key_change_me", 86400);
    }

    // Auth helper
    private function getAuthenticatedUserId()
    {
        $token = self::$data['jwt'] ?? '';
        if (!$token) { return null; }
        $payload = $this->jwt->verifyToken($token);
        if (!$payload) { return null; }
        return (int)($payload['sub'] ?? 0);
    }

    // Books
    public function upload($data)
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->utility->jsonResponse(401, 'Unauthorized', []);
        }
        $require = ["title"];
        $validate = $this->utility->validateFields($data, $require);
        if ($validate['error']) {
            return $this->utility->jsonResponse(400, 'Invalid input', $validate['error_msg']);
        }
        $title = $validate['data']['title'];
        $description = $data['description'] ?? null;
        $content = $data['content'] ?? null;
        $ok = $this->model->createBook($userId, $title, $description, $content);
        if ($ok) {
            return $this->utility->jsonResponse(200, 'Book uploaded', []);
        }
        return $this->utility->jsonResponse(500, 'Failed to upload book', []);
    }

    public function invite($data)
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) { return $this->utility->jsonResponse(401, 'Unauthorized', []); }
        $require = ["bookId", "email"];
        $validate = $this->utility->validateFields($data, $require);
        if ($validate['error']) {
            return $this->utility->jsonResponse(400, 'Invalid input', $validate['error_msg']);
        }
        $ok = $this->model->addInvitation((int)$validate['data']['bookId'], $validate['data']['email'], $userId);
        if ($ok) { return $this->utility->jsonResponse(200, 'Invitation sent', []); }
        return $this->utility->jsonResponse(500, 'Failed to invite', []);
    }

    public function bookReviews($data)
    {
        $require = ["bookId"];
        $validate = $this->utility->validateFields($data, $require);
        if ($validate['error']) {
            return $this->utility->jsonResponse(400, 'Invalid input', $validate['error_msg']);
        }
        $reviews = $this->model->getBookReviews((int)$validate['data']['bookId']);
        return $this->utility->jsonResponse(200, 'ok', $reviews ?: []);
    }

    // Reviews
    public function addReview($data)
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) { return $this->utility->jsonResponse(401, 'Unauthorized', []); }
        $require = ["bookId", "comment"];
        $validate = $this->utility->validateFields($data, $require);
        if ($validate['error']) {
            return $this->utility->jsonResponse(400, 'Invalid input', $validate['error_msg']);
        }
        $rating = isset($data['rating']) ? (int)$data['rating'] : null;
        $ok = $this->model->addReview((int)$validate['data']['bookId'], $userId, $validate['data']['comment'], $rating);
        if ($ok) { return $this->utility->jsonResponse(200, 'Review added', []); }
        return $this->utility->jsonResponse(500, 'Failed to add review', []);
    }

    public function booksReviewed($data)
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) { return $this->utility->jsonResponse(401, 'Unauthorized', []); }
        $books = $this->model->getBooksReviewedByUser($userId);
        return $this->utility->jsonResponse(200, 'ok', $books ?: []);
    }

    public function myReviews($data)
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) { return $this->utility->jsonResponse(401, 'Unauthorized', []); }
        $reviews = $this->model->getMyReviews($userId);
        return $this->utility->jsonResponse(200, 'ok', $reviews ?: []);
    }

    // Users
    public function updateProfile($data)
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) { return $this->utility->jsonResponse(401, 'Unauthorized', []); }
        $name = $data['name'] ?? null;
        $profilePic = $data['profile_pic'] ?? null;
        $ok = $this->model->updateUserProfile($userId, $name, $profilePic);
        if ($ok) { return $this->utility->jsonResponse(200, 'Profile updated', []); }
        return $this->utility->jsonResponse(400, 'Nothing to update', []);
    }
}


