<?php
/**
 * Front Controller - Cotação Online Atacadão
 */

// Basic CORS and Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Simple Routing
if ($method === 'GET' && $uri === '/api/health') {
    echo json_encode([
        "status" => "ok",
        "timestamp" => date("Y-m-d H:i:s")
    ]);
    exit();
}

// Serve frontend/index.html for root or handle 404
if ($uri === '/' || $uri === '/index.php') {
    header("Content-Type: text/html; charset=UTF-8");
    readfile(__DIR__ . '/frontend/index.html');
    exit();
}

// 404 Not Found
http_response_code(404);
echo json_encode(["error" => "Endpoint not found"]);
