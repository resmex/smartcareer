<?php
// Start output buffering to capture any stray output
ob_start();

// Turn off HTML error reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/error_log.txt');

// Load dependencies at the top
require __DIR__ . '/../../vendor/autoload.php';
use OpenAI\Factory;
use Dotenv\Dotenv;

// Always set JSON content type
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('X-Content-Type-Options: nosniff');

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    ob_end_clean();
    exit;
}

// Main logic wrapped in try-catch
try {
    // Check for required files
    $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
    $envPath = __DIR__ . '/../../.env';
    
    if (!file_exists($autoloadPath)) {
        throw new Exception('Vendor autoload file not found at ' . $autoloadPath);
    }
    
    if (!file_exists($envPath)) {
        throw new Exception('.env file not found at ' . $envPath);
    }
    
    // Start session with secure settings
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized: Please login first', 401);
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    // Load environment variables
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
    
    if (!isset($_ENV['OPENAI_API_KEY']) || empty($_ENV['OPENAI_API_KEY'])) {
        throw new Exception('OPENAI_API_KEY is missing in .env file');
    }
    
    // Get and validate input
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON format: ' . json_last_error_msg(), 400);
    }
    
    $message = $input['message'] ?? '';
    if (empty(trim($message))) {
        throw new Exception('Message cannot be empty', 400);
    }
    
    // Generate response
    $response = generateCareerBotResponse($message);
    $output = json_encode(['status' => 'success', 'response' => $response]);
    
    ob_end_clean();
    exit($output);

} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
    http_response_code($e->getCode() ?: 500);
    $output = json_encode(['error' => $e->getMessage()]);
    ob_end_clean();
    exit($output);
}

function generateCareerBotResponse(string $message): string {
    try {
        $client = (new Factory())->withApiKey($_ENV['OPENAI_API_KEY'])->make();
        
        $response = $client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are CareerGPT. Provide career advice.'],
                ['role' => 'user', 'content' => $message]
            ],
            'temperature' => 0.7,
            'max_tokens' => 300,
        ]);
        
        return sanitizeResponse($response->choices[0]->message->content);
    } catch (Exception $e) {
        error_log("OpenAI Error: " . $e->getMessage());
        return "I'm experiencing technical difficulties. Please try again later.";
    }
}

function sanitizeResponse(string $response): string {
    $allowedTags = '<b><strong><i><em><ul><ol><li><p><br>';
    return htmlspecialchars(strip_tags($response, $allowedTags), ENT_QUOTES, 'UTF-8');
}