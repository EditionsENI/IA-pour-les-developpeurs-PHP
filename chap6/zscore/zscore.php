<?php
use function Rubix\ML\iterator_contains_nan;

use Php2plotly\basic\ScatterPlot;
use Php2plotly\stats\Histogram;
use Rubix\ML\AnomalyDetectors\RobustZScore;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Extractors\ColumnPicker;
use Rubix\ML\Extractors\CSV;
use Rubix\ML\Transformers\NumericStringConverter;

require_once __DIR__ . '../../../vendor/autoload.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Robust ZScore</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>

<body style="display:flex; flex-wrap:wrap; max-width:100vw;">
    <h1>Robust ZScore</h1>
<?php

$housingCSV = new CSV('housing.csv', true);
$columnNames = ['DIS'];
$columnPicker = new ColumnPicker($housingCSV,$columnNames);
$datasetHousing = Unlabeled::fromIterator($columnPicker);
$numericTransformer = new NumericStringConverter();
$datasetHousing->apply($numericTransformer);

$cleanRecord = function ($record){
    $missesContinuousValue = iterator_contains_nan($record);
    return !$missesContinuousValue;
};
$datasetHousing = $datasetHousing->filter($cleanRecord);


//Plot the dataset features with PHP2Plotly
echo "<div>";
$data = ['x'=> $datasetHousing->features(0)[0], 'name'=>'points', 'type' => 'histogram'];
echo "<div id='histo'></div>";
$histogram = new Histogram('histo', $data);
echo "<script>".$histogram->render()."</script>";
echo "<center><strong>".$columnNames[0]."</strong></center>";
echo "</div>";

echo "<div>";
$data =[
    ['x' => $datasetHousing->feature(0), 'y' => array_fill(0, $datasetHousing->numSamples(), 0), 'name'=>'points', 'mode' => 'markers', 'type' => 'scatter']
];
echo "<div id='scatter'></div>";
$scatter = new ScatterPlot('scatter', $data);
echo "<script>".$scatter->render()."</script>";
echo "</div>";

//Create a Robust ZScore detector
$zscoreDetector = new RobustZScore();
$zscoreDetector->train($datasetHousing);
$detections = $zscoreDetector->predict($datasetHousing);


//Group by cluster
$numberGroups = count(array_unique($detections));
$grouped = [];
foreach($detections as $i => $detection){
    $grouped[$detection][] = $datasetHousing->sample($i);
}

//Plot the clusters
echo "<div>";
$data = [];

foreach($grouped as $i => $samples){
    if($i == 1){
        $data[] = ['x' => array_column($samples, 0), 'y' => array_fill(0, count($samples), 0), 'name'=>'anomaly', 'mode' => 'markers', 'type' => 'scatter'];
    }else{
        $data[] = ['x' => array_column($samples, 0), 'y' => array_fill(0, count($samples), 0), 'name'=>'normal', 'mode' => 'markers', 'type' => 'scatter'];
    }
}

echo "<div id='clusters'></div>";
$scatter = new ScatterPlot('clusters', $data);
echo "<script>".$scatter->render()."</script>";
echo "</div>";

?>
</body>