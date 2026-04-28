<?php

namespace App\Services;

use PDO;

class AuthService {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Authenticate a user by username and password.
     *
     * @param string $username
     * @param string $password
     * @return array|null User data or null on failure
     */
    public function authenticate(string $username, string $password): ?array {
        $stmt = $this->db->prepare("SELECT id, username, password FROM admin_users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if (!$user) {
            // echo "User not found: $username\n";
            return null;
        }

        if (password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }

        // echo "Password verify failed for user: $username\n";
        return null;
    }

    /**
     * Check if the admin user is authenticated via session.
     *
     * @return bool
     */
    public function isAuthenticated(): bool {
        return isset($_SESSION['admin_id']);
    }
}
