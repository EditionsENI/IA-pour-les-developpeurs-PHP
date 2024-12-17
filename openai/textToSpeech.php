<?php

require '../vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client();
$endpoint = 'https://api.openai.com/v1/audio/speech';
$apiKey = "sk-...";
$outputFile = 'sound/output.mp3';

$requestBody = [
    'model' => 'tts-1',
    'input' => 'Mes enfants Tom et Noé sont mes rayons de soleil et je les aime très fort',
    'voice' => 'nova',
];

//Function to send request to OpenAI API
function sendRequestWithOutput($requestBody, $client, $endpoint, $key, $outputFile) {
    try {
        $response = $client->post($endpoint, [
            'headers' => [
                'Authorization' => "Bearer $key",
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($requestBody),
            'sink' => $outputFile,
        ]);

        $result = json_decode($response->getBody(), true);
        
    } catch (Exception $e) {
        echo "Error : " . $e->getMessage();
    }
}

sendRequestWithOutput($requestBody, $client, $endpoint, $apiKey, $outputFile);