<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Php2plotly\basic\ScatterPlot;
use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Classifiers\RandomForest;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Serializers\RBX;

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Save model</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>

<body style="display:flex; flex-wrap:wrap; max-width:100vw;">
    <h1>Save model</h1>

<?php

$samples = [];

//Create a new dataset with data generated with the elements in the square limited by y between -2 and 2 and x between -2 and 2 with class 0
for ($i = 0; $i < 5000; $i++) {
    $x = rand(-700, 700) / 100;
    $y = rand(-700, 700) / 100;
    if ($x > -2 && $x < 2 && $y > -2 && $y < 2) {
        $samples[] = [$x, $y, 0];
    }else{
        $samples[] = [$x, $y, 1];
    }
}

//Group by class
$grouped = [];
foreach($samples as $sample){
    $grouped[$sample[2]][] = [$sample[0], $sample[1]];
}

$data = [];
foreach ($grouped as $key=>$value) {
    $data[] = ['x' => array_column($value, 0), 'y' => array_column($value, 1), 'name' => $key, 'mode' => 'markers', 'type' => 'scatter'];
}

echo "<div>";
echo "<div id='scatter'></div>";
$scatter = new ScatterPlot('scatter', $data);
echo "<script>" . $scatter->render() . "</script>";
echo "</div>";

//dataset samples creation
$samplesDataset = [];
$labels = [];
foreach($samples as $sample){
    $samplesDataset[] = [$sample[0], $sample[1]];
    $labels[] = "class ". $sample[2];
}

//Create dataset
$dataset = new Labeled($samplesDataset, $labels);

//Create random forest classifier
$estimator = new RandomForest(new ClassificationTree(10));
$estimator->train($dataset);

//Test the model
$test = [[0, 0], [1, 1], [2, 2], [3, 3], [-1, 0], [5, 5], [6, 6], [7, 7], [8, 8], [9, 9]];
$predictions = $estimator->predict(new Unlabeled($test));

echo "<pre>";
print_r($predictions);
echo "</pre>";

//Save the model

//Initialize the serializer RBX
$serializer = new RBX();
//Create the encoding of the model using the serializer
$encoded = $serializer->serialize($estimator);
//Save the encoding to a file by using the Filesystem persister
$encoded->saveTo(new Filesystem("randomForest.rbx"));