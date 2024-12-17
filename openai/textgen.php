<?php

require '../vendor/autoload.php';
use GuzzleHttp\Client;

$apiKey = 'sk-...';

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

//Endpoint for text generation
$endpoint = 'https://api.openai.com/v1/chat/completions';

//Init Guzzle client
$client = new Client();

$requestBodyWithoutContext = [
    'model' => 'gpt-4o-mini',
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Quel est le sens de la vie ?'
        ]
    ]
];

sendRequest($requestBodyWithoutContext, $client, $endpoint, $apiKey);

$requestBodyWithContext = [
    'model' => 'gpt-4o-mini',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'Tu es un personnage très rigolo qui apporte toujours une blague dans tes réponses.'
        ],
        [
            'role' => 'user',
            'content' => 'Quel est le sens de la vie ?'
        ]
    ]
];

sendRequest($requestBodyWithContext, $client, $endpoint, $apiKey);

$requestBodyWithContext = [
    'model' => 'gpt-4o-mini',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'Tu es un personnage très rigolo qui apporte toujours une blague dans tes réponses.'
        ],
        [
            'role' => 'user',
            'content' => 'Je veux que tu choisisses une couleur et je souhaite que tu ajoutes cette couleur dans le texte de toutes tes réponses à suivre.'
        ],
        [
            'role' => 'assistant',
            'content' => 'Je vais choisir la couleur indigo.'
        ],
        [
            'role' => 'user',
            'content' => 'Quel est le sens de la vie ?'
        ]
    ]
];

sendRequest($requestBodyWithContext, $client, $endpoint, $apiKey);