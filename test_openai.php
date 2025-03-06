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
    'model' => 'gpt-4o-mini',
    'messages' => [
        ['role' => 'user', 'content' => 'How do I improve my resume?'],
    ],
]);

// Output the response
echo '<pre>';
print_r($response);
echo '</pre>';
?>
