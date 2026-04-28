<?php

namespace App\Controllers;

use App\Services\AuthService;

class AuthController {
    private AuthService $authService;

    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    /**
     * Handle login request.
     */
    public function login() {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        $user = $this->authService->authenticate($username, $password);

        if ($user) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_user'] = $user['username'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Credenciais inválidas'
            ]);
        }
    }

    /**
     * Handle logout request.
     */
    public function logout() {
        session_unset();
        session_destroy();
        
        echo json_encode([
            'success' => true,
            'message' => 'Logout realizado com sucesso'
        ]);
    }

    /**
     * Check authentication status.
     */
    public function status() {
        if ($this->authService->isAuthenticated()) {
            echo json_encode([
                'logged_in' => true,
                'user' => $_SESSION['admin_user']
            ]);
        } else {
            echo json_encode([
                'logged_in' => false
            ]);
        }
    }
}
