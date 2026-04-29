<?php
/**
 * Front Controller - Cotação Online Atacadão
 */

// Production settings: hide notices and warnings from output
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 0);

// Start output buffering to prevent header issues and clean output
ob_start();

// Start native PHP session
session_start();

// Autoloader for App namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/backend/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Database;
use App\Services\AuthService;
use App\Services\CsvImportService;
use App\Models\CatalogModel;
use App\Controllers\AuthController;
use App\Controllers\CatalogController;

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

// Initialize Core Dependencies
$db = Database::getInstance();
$authService = new AuthService($db);
$csvService = new CsvImportService($db);
$catalogModel = new CatalogModel($db);

// --- API Routing ---

// Health Check
if ($method === 'GET' && $uri === '/api/health') {
    echo json_encode([
        "status" => "ok",
        "timestamp" => date("Y-m-d H:i:s")
    ]);
    exit();
}

// Authentication Routes
$authController = new AuthController($authService);

if ($method === 'POST' && $uri === '/api/login') {
    $authController->login();
    exit();
}

if ($method === 'POST' && $uri === '/api/logout') {
    $authController->logout();
    exit();
}

if ($method === 'GET' && $uri === '/api/auth/status') {
    $authController->status();
    exit();
}

// Catalog Routes
$catalogController = new CatalogController($csvService, $authService, $catalogModel);

if ($method === 'GET' && $uri === '/api/catalog') {
    $catalogController->search();
    exit();
}

if ($method === 'GET' && $uri === '/api/catalog/stats') {
    $catalogController->stats();
    exit();
}

if ($method === 'POST' && $uri === '/api/catalog/upload') {
    $catalogController->upload();
    exit();
}

// --- Frontend Routing ---

// Serve frontend/index.html for root or handle 404
if ($uri === '/' || $uri === '/index.php') {
    header("Content-Type: text/html; charset=UTF-8");
    readfile(__DIR__ . '/frontend/index.html');
    exit();
}

// 404 Not Found
http_response_code(404);
echo json_encode(["error" => "Endpoint not found"]);
