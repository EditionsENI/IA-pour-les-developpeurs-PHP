<?php


require '../vendor/autoload.php';

use GuzzleHttp\Client;

$apiKey = 'sk-...';
$endpoint = 'https://api.openai.com/v1/embeddings';
$client = new Client();

//Familial vocabulary
$vocabulary = ["homme", "femme", "enfant", "bébé", "adulte", "adolescent", "personne âgée", "personne", "individu", "groupe", "famille", "couple", "mari", "femme", "époux", "épouse", "père", "mère","roi","reine"];

$requestBody = [
    'model' => 'text-embedding-3-small',
    'input' => $vocabulary
];

try {
    $response = $client->post($endpoint, [
        'headers' => [
            'Authorization' => "Bearer $apiKey",
            'Content-Type' => 'application/json',
        ],
        'body' => json_encode($requestBody),
    ]);

    $result = json_decode($response->getBody(), true);

    $embeddings = $result['data'];

    //Put into vectors/embedding_vectors.csv
    $fp = fopen('vectors/embedding_vectors.csv', 'w');
    foreach ($embeddings as $key=>$embedding) {
        $line = [$vocabulary[$key]]+$embedding['embedding']; 
        fputcsv($fp, $line);
    }
    fclose($fp);
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>