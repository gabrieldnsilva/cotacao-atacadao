<?php

use PHPUnit\Framework\TestCase;
use App\Services\AuthService;
use App\Core\Database;

class AuthServiceTest extends TestCase {
    private $db;
    private $authService;

    protected function setUp(): void {
        $this->db = Database::getInstance();
        $this->authService = new AuthService($this->db);
        
        // Ensure we are in a clean state (optional for this simple test)
        $this->db->exec("DELETE FROM admin_users WHERE username = 'testuser'");
        $hash = password_hash('password123', PASSWORD_DEFAULT);
        $this->db->exec("INSERT INTO admin_users (username, password) VALUES ('testuser', '$hash')");
    }

    public function testAuthenticationSuccess() {
        $user = $this->authService->authenticate('testuser', 'password123');
        $this->assertNotNull($user);
        $this->assertEquals('testuser', $user['username']);
    }

    public function testAuthenticationFailure() {
        $user = $this->authService->authenticate('testuser', 'wrongpassword');
        $this->assertNull($user);
    }

    public function testIsAuthenticatedReturnsFalseWhenSessionNotSet() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['admin_id']);
        $this->assertFalse($this->authService->isAuthenticated());
    }

    public function testIsAuthenticatedReturnsTrueWhenSessionSet() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['admin_id'] = 1;
        $this->assertTrue($this->authService->isAuthenticated());
    }
}
