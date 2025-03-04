<?php
// Ensure Composer autoload is included
require_once 'vendor/autoload.php';

// Use the OpenAI library
use OpenAI\Client;

// Set your OpenAI API key (use your actual API key here)
$apiKey = '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
';

// Initialize OpenAI client
$client = OpenAI::client($apiKey);

// Make a test API call to OpenAI
$response = $client->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'user', 'content' => 'How do I improve my resume?'],
    ],
]);

// Output the response
echo '<pre>';
print_r($response);
echo '</pre>';
?>
