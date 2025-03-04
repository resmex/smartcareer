<?php
use Dotenv\Dotenv;
use OpenAI\Client as OpenAIClient;


error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/error_log.txt');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost'); // Restrict to localhost
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}


session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized: Please login first']));
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON format');
        }
        
        $message = filter_var($input['message'] ?? '', FILTER_SANITIZE_STRING);
        
        if (empty(trim($message))) {
            http_response_code(400);
            exit(json_encode(['error' => 'Message cannot be empty']));
        }

        $response = generateCareerBotResponse($message);
        exit(json_encode(['status' => 'success', 'response' => $response]));

    } catch (Exception $e) {
        error_log("API Error: " . $e->getMessage());
        http_response_code(500);
        exit(json_encode(['error' => 'Internal server error']));
    }
}

http_response_code(405);
exit(json_encode(['error' => 'Method not allowed']));

function generateCareerBotResponse(string $message): string {
    require_once __DIR__ . '/../../vendor/autoload.php';
    
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
    $dotenv->required('OPENAI_API_KEY')->notEmpty();

    try {
        $client = new OpenAIClient($_ENV['OPENAI_API_KEY']);
        
        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "As CareerGPT, provide professional career guidance. Focus on:
                        - Actionable advice
                        - Industry trends
                        - Skill development
                        - Resume/Interview tips
                        Use clear structure with bullet points when appropriate."
                ],
                ['role' => 'user', 'content' => $message]
            ],
            'temperature' => 0.7,
            'max_tokens' => 300,
            'frequency_penalty' => 0.5,
            'presence_penalty' => 0.3
        ]);

        $content = $response->choices[0]->message->content;
        return sanitizeResponse($content);

    } catch (Exception $e) {
        error_log("OpenAI Error: " . $e->getMessage());
        return "I'm experiencing technical difficulties. Please try again later.";
    }
}


function sanitizeResponse(string $response): string {
    $allowedTags = '<b><strong><i><em><ul><ol><li><p><br>';
    $sanitized = strip_tags($response, $allowedTags);
    return htmlspecialchars($sanitized, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


