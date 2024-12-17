<?php


use Php2plotly\stats\Histogram;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Extractors\ColumnPicker;
use Rubix\ML\Extractors\CSV;
use Rubix\ML\Kernels\Distance\Cosine;
use Rubix\ML\Transformers\NumericStringConverter;

require_once __DIR__ . '../../../vendor/autoload.php';

?>
<html>
<head>
    <title>KNN Classifier</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>
<body>
<h1>KNN Classifier</h1>
<div style="display: flex; flex-wrap: wrap;">
<?php

//Extract Dataset
$zooDataCSV = new CSV('zoo.data', true);
$columnNames = [
    'hair', 'feathers', 'eggs', 'milk', 'airborne', 'aquatic', 'predator', 
    'toothed', 'backbone', 'breathes', 'venomous', 'fins', 'legs', 'tail', 
    'domestic', 'catsize', 'type'
];
$dataPicked = new ColumnPicker($zooDataCSV, $columnNames);
//Process continuous data
$dataset = Labeled::fromIterator($dataPicked);

$dataset->transformLabels(function($label) {
    switch ($label) {
        case 1: return 'Mammal';
        case 2: return 'Bird';
        case 3: return 'Reptile';
        case 4: return 'Fish';
        case 5: return 'Amphibian';
        case 6: return 'Bug';
        case 7: return 'Invertebrate';
    }
});

$dataset = $dataset->deduplicate();

$dataset->apply(new NumericStringConverter());


//Plot the labels distributions
echo "<div>";
$data = ['x'=> $dataset->labels()];
echo "<div id='labels'></div>";
$histogram = new Histogram('labels', $data);
echo "<script>".$histogram->render()."</script>";
echo "<center><strong>Animal types</strong></center>";
echo "</div>";

[$training, $testing] = $dataset->randomize()->stratifiedSplit(0.8);

$estimatorKnn = new KNearestNeighbors(3, false, new Cosine());
$estimatorKnn->train($training);

$report = new MulticlassBreakdown();
$results = $report->generate($estimatorKnn->predict(new Unlabeled($testing->samples())), $testing->labels());
echo "<pre>";
print_r($results);
echo "</pre>";

echo "<pre>";
print_r($estimatorKnn->predict(
    new Unlabeled([
        [
            1,1, 1, 0, 1, 0, 1, 0, 1, 1, 0, 1, 2, 0, 0, 1
        ]
        ])
    )
);
echo "</pre>";

?>
</div>

</body>
</html>

