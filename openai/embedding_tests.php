<?php

require '../vendor/autoload.php';

use Rubix\ML\Kernels\Distance\Cosine;
use Tensor\Vector;

//Read vectors/embedding_vectors.csv
$csv = file_get_contents('vectors/embedding_vectors.csv');
$csv = array_map('str_getcsv', explode("\n", $csv));
array_pop($csv);

$items = [];
foreach ($csv as $key=>$line) {
    //Converte a string array to a float array
    $items[$line[0]] = array_slice($line, 1);
}

foreach ($items as $word=>&$vector) {
    $vectorNumerical = array_map(function($item) {
        return (float)$item;
    }, $vector);
    $vector = $vectorNumerical;
}

$cosineDistance = new Cosine();

$words = ['homme', 'bébé', 'enfant'];
//Compute the distance between the words 'homme' and 'bébé'
$distance = $cosineDistance->compute($items[$words[0]], $items[$words[1]]);
echo "Distance between ".$words[0]." and ".$words[1]." : ".$distance. "<br/>";

//Compute the distance between the words 'homme' and 'enfant'
$distance = $cosineDistance->compute($items[$words[0]], $items[$words[2]]);
echo "Distance between ".$words[0]." and ".$words[2]." : ".$distance. "<br/>";

//Compute the distance between the words 'bébé' and 'enfant'
$distance = $cosineDistance->compute($items[$words[1]], $items[$words[2]]);
echo "Distance between ".$words[1]." and ".$words[2]." : ".$distance. "<br/>";


$sumKeys = ['femme', 'roi'];
//Sum the vectors
$sumArray = Vector::quick($items[$sumKeys[0]])->add(Vector::quick($items[$sumKeys[1]]))->asArray();


$distances = [];
foreach ($items as $word=>$vector) {
    if(!in_array($word, $sumKeys)) {
        $distance = $cosineDistance->compute($sumArray, $vector);
        $distances[$word] = $distance;
    }
}

//Sort by distance
asort($distances);

//Display key of first element
echo array_key_first($distances)."<br/>";

$sumKeys = ['', 'roi'];








