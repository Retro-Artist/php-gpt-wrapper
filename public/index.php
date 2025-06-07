<?php
// public/index.php - Main entry point for all requests

// Start output buffering to catch any accidental output
ob_start();

// Start session
session_start();

// For API requests, set JSON header early and disable error display
$isApiRequest = strpos($_SERVER['REQUEST_URI'], '/api/') === 0;
if ($isApiRequest) {
    header('Content-Type: application/json');
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
}

// Load configuration
require_once '../config/config.php';
require_once '../src/Core/Router.php';

// Clean any output buffer before proceeding
if ($isApiRequest) {
    ob_clean();
}

// Initialize router
$router = new Router();

// Web Routes (return HTML pages)
$router->addWebRoute('GET', '/', 'HomeController@index');
$router->addWebRoute('GET', '/chat', 'ChatController@index');
$router->addWebRoute('GET', '/dashboard', 'DashboardController@index');
$router->addWebRoute('GET', '/agents', 'AgentController@index');
$router->addWebRoute('GET', '/login', 'AuthController@showLogin');
$router->addWebRoute('POST', '/login', 'AuthController@processLogin');
$router->addWebRoute('GET', '/register', 'AuthController@showRegister');
$router->addWebRoute('POST', '/register', 'AuthController@processRegister');
$router->addWebRoute('GET', '/logout', 'AuthController@logout');

// API Routes (return JSON responses)
$router->addApiRoute('GET', '/api/threads', 'ThreadsAPI@getThreads');
$router->addApiRoute('POST', '/api/threads', 'ThreadsAPI@createThread');
$router->addApiRoute('GET', '/api/threads/{id}', 'ThreadsAPI@getThread');
$router->addApiRoute('PUT', '/api/threads/{id}', 'ThreadsAPI@updateThread');
$router->addApiRoute('DELETE', '/api/threads/{id}', 'ThreadsAPI@deleteThread');
$router->addApiRoute('GET', '/api/threads/{id}/messages', 'ThreadsAPI@getMessages');
$router->addApiRoute('POST', '/api/threads/{id}/messages', 'ThreadsAPI@sendMessage');

$router->addApiRoute('GET', '/api/agents', 'AgentsAPI@getAgents');
$router->addApiRoute('POST', '/api/agents', 'AgentsAPI@createAgent');
$router->addApiRoute('GET', '/api/agents/{id}', 'AgentsAPI@getAgent');
$router->addApiRoute('PUT', '/api/agents/{id}', 'AgentsAPI@updateAgent');
$router->addApiRoute('DELETE', '/api/agents/{id}', 'AgentsAPI@deleteAgent');
$router->addApiRoute('POST', '/api/agents/{id}/run', 'AgentsAPI@runAgent');

$router->addApiRoute('GET', '/api/tools', 'ToolsAPI@getTools');
$router->addApiRoute('POST', '/api/tools/{name}/execute', 'ToolsAPI@executeTool');

$router->addApiRoute('GET', '/api/system/status', 'SystemAPI@getStatus');
$router->addApiRoute('GET', '/api/system/config', 'SystemAPI@getConfig');

// Handle the request
try {
    $router->handleRequest();
} catch (Exception $e) {
    if ($isApiRequest) {
        // Clean any output and return clean JSON error
        ob_clean();
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    } else {
        echo "An error occurred: " . $e->getMessage();
    }
}


/**
 * Quick Debug Script - Check OpenAI Configuration
 * 
 * Add this to the bottom of your index.php temporarily to debug:
 */

// Add this debug block at the end of your main index.php file:
if (isset($_GET['debug']) && $_GET['debug'] === 'config') {
    echo "<h2>Configuration Debug</h2>";
    
    // Load config
    $config = require '../config/config.php';
    
    echo "<h3>Config File Values:</h3>";
    echo "API Key: " . (empty($config['api_key']) ? '❌ EMPTY' : '✅ ' . substr($config['api_key'], 0, 10) . '...') . "<br>";
    echo "Model: " . $config['model'] . "<br>";
    echo "Max Tokens: " . $config['max_tokens'] . "<br>";
    echo "Temperature: " . $config['temperature'] . "<br>";
    
    echo "<h3>Environment Variables:</h3>";
    echo "OPENAI_API_KEY: " . (getenv('OPENAI_API_KEY') ? '✅ SET (' . substr(getenv('OPENAI_API_KEY'), 0, 10) . '...)' : '❌ NOT SET') . "<br>";
    echo "OPENAI_MODEL: " . (getenv('OPENAI_MODEL') ?: '❌ NOT SET') . "<br>";
    
    echo "<h3>.env File Check:</h3>";
    if (file_exists('../.env')) {
        echo "✅ .env file exists<br>";
        $envContent = file_get_contents('../.env');
        if (strpos($envContent, 'OPENAI_API_KEY') !== false) {
            echo "✅ OPENAI_API_KEY found in .env<br>";
        } else {
            echo "❌ OPENAI_API_KEY NOT found in .env<br>";
        }
    } else {
        echo "❌ .env file not found<br>";
    }
    
    echo "<h3>SystemAPI Test:</h3>";
    try {
        require_once '../src/Api/SystemAPI.php';
        $systemAPI = new SystemAPI();
        echo "✅ SystemAPI created successfully<br>";
    } catch (Exception $e) {
        echo "❌ SystemAPI error: " . $e->getMessage() . "<br>";
    }
    
    exit;
}
?>