<?php
// filepath: /Users/louisauthie/apache/rubixml/openai/speechToText.php

require '../vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client();
$apiKey = "sk-...";

//Function to send multipart request to OpenAI API
function sendRequestMultipart($requestBody, $client, $endpoint, $key) {
    try {
        $response = $client->post($endpoint, [
            'headers' => [
                'Authorization' => "Bearer $key",
            ],
            'multipart' => $requestBody,
        ]);
        return json_decode($response->getBody(), true);
        
    } catch (Exception $e) {
        echo "Error : " . $e->getMessage();
    }
}

//Function to send request to OpenAI API
function sendRequest($requestBody, $client, $endpoint, $key) {
    try {
        $response = $client->post($endpoint, [
            'headers' => [
                'Authorization' => "Bearer $key",
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($requestBody),
        ]);

        $result = json_decode($response->getBody(), true);
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    } catch (Exception $e) {
        echo "Error : " . $e->getMessage();
    }
}

$endpoint = 'https://api.openai.com/v1/audio/transcriptions';

$requestBody = [
    [
        'name' => 'model',
        'contents' => 'whisper-1'
    ],
    [
        'name' => 'file',
        'contents' => fopen('sound/atc_freq.m4a', 'r'),
        'filename' => 'atc_freq.m4a'
    ]
];

$result = sendRequestMultipart($requestBody, $client, $endpoint, $apiKey);

echo "<pre>";
print_r($result);
echo "</pre>";

//Récupération des données interessantes du vol
$endpoint = 'https://api.openai.com/v1/chat/completions';

$requestBody = [
    'model' => 'gpt-4o-mini', // Assurez-vous que le modèle est valide
    'messages' => [
        [
            'role' => 'system',
            'content' => 'Tu es un expert en controle aérien et aéronautique et tu réponds en JSON avec un champ pour les altitudes, un champ pour les caps magnétique, un champ pour l\'indicatif de l\'aeronef, pour le sens d\évolution verticale demandé à l\'aéronef, un champ pour les pressions'
        ],
        [
            'role' => 'user',
            'content' => 'A partir de cette clearance ATC donne moi les informations importantes données à l\aéronef :'.$result['text']
        ]
    ]
];

sendRequest($requestBody, $client, $endpoint, $apiKey);

