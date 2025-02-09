<?php
namespace App\Models;

use Core\Database;
use PDO;

class UserModel {
    /**
     * Verify user credentials for login
     */
    public function verifyUser($username, $password) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, password FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user; // Return user info if password is correct
        }

        return false; // Return false if user is not found or password is incorrect
    }

    /**
     * Change password for a user
     */
    public function changePassword($userId, $newPassword) {
        $db = Database::getInstance();
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = :newPassword WHERE id = :userId");
        if (!$stmt->execute([
            'newPassword' => $hashedPassword,
            'userId' => $userId
        ])) {
            error_log('Password update failed: ' . implode('; ', $stmt->errorInfo()));
        }
        return $stmt->rowCount();
    }

}
