<?php

use Rubix\ML\Classifiers\NaiveBayes;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Extractors\CSV;

require_once __DIR__ . '../../../vendor/autoload.php';

?>
<html>
<head>
    <title>Naïve Bayes</title>
</head>
<body>
<h1>Naïve Bayes</h1>
<?php
$csv = new CSV("spam_nonspam_dataset.csv", true);
$dataset = Labeled::fromIterator($csv);

//Create the Naive Bayes model
$estimatorNB = new NaiveBayes();
$estimatorNB->train($dataset);
echo "<h2>Naive Bayes</h2>";
echo "<pre>";
print_r($estimatorNB->counts());
echo "</pre>";

$prediction = $estimatorNB->predict(new Unlabeled([[1,1,0,1]]));
echo "[1,1,0,1] est " .$prediction[0];

$prediction = $estimatorNB->predict(new Unlabeled([[0,1,1,0]]));
echo "<br/>[0,1,1,0] est " .$prediction[0];


