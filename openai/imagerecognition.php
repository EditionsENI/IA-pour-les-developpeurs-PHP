<?php


require '../vendor/autoload.php';

use GuzzleHttp\Client;

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


$apiKey = 'sk-...';
$endpoint = 'https://api.openai.com/v1/chat/completions';

$base64Image1 = base64_encode(file_get_contents('img/prices_duvet.png'));
$base64Image2 = base64_encode(file_get_contents('img/product_page.png'));

$client = new Client();

$requestBody = [
    'model' => 'gpt-4o-mini', // Assurez-vous que le modèle est valide
    'messages' => [
        [
            'role' => 'system',
            'content' => 'Les réponses ne sont délivrées qu\'au format JSON avec un champ nommé price contenant la réponse en type float'
        ],
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'Quel est le prix de la couette mi-saison dans cette image ?',
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => 'data:image/png;base64,' . $base64Image1,
                    ]
                ]
            ]
        ],
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'Quel est le prix du produit présenté dans cette image en y soustrayant le prix de la demande précédente ?',
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => 'data:image/png;base64,' . $base64Image2,
                    ]
                ]
            ]
        ],
    ],
];

sendRequest($requestBody, $client, $endpoint, $apiKey);
