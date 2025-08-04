<?php
// models/user.php

class User {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function signup($email, $password, $confirmPassword) {
        if (empty($email) || empty($password) || empty($confirmPassword)) {
            return ["error" => "All fields are required"];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["error" => "Invalid email format"];
        }

        if ($password !== $confirmPassword) {
            return ["error" => "Passwords do not match"];
        }

        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            return ["error" => "Email already registered"];
        }
        $stmt->close();

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $otp = rand(100000, 999999);

        $stmt = $this->conn->prepare("INSERT INTO users (email, password, otp) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $email, $hashedPassword, $otp);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return [
                "statuscode" => 200,
                "message" => "User registered successfully. OTP generated.",
                "otp" => $otp  // Show OTP directly for now (consider removing this in production)
            ];
        }

        return ["error" => "Registration failed"];
    }

    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ["error" => "Email and password are required"];
        }

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            if (!password_verify($password, $user['password'])) {
                return ["error" => "Incorrect password"];
            }

            if (!$user['is_verified']) {
                return ["error" => "Please verify your email with OTP"];
            }

            return [
                "message" => "Login successful",
                "email" => $user['email']
            ];
        }

        return ["error" => "Invalid email"];
    }

    public function verifyOtp($email, $otp) {
        $stmt = $this->conn->prepare("SELECT otp FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && $user['otp'] == $otp) {
            $stmt = $this->conn->prepare("UPDATE users SET is_verified = 1, otp = NULL WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->close();
            return ["message" => "OTP verified. Account activated."];
        }

        return ["error" => "Invalid OTP"];
    }

    public function updateProfile($email, $fullname, $description) {
        if (empty($fullname) || empty($email) || empty($description)) {
            return ["error" => "All fields are required"];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["error" => "Invalid email format"];
        }

        $stmt = $this->conn->prepare("UPDATE users SET fullname = ?, description = ? WHERE email = ?");
        $stmt->bind_param("sss", $fullname, $description, $email);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return ["statuscode" => 200, "message" => "Profile updated successfully"];
        } else {
            return ["error" => "Failed to update profile"];
        }
    }
}
?>
