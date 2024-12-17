<?php

use Php2plotly\basic\ScatterPlot;
use Rubix\ML\AnomalyDetectors\IsolationForest;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Extractors\CSV;
use Rubix\ML\Transformers\NumericStringConverter;

require_once __DIR__ . '../../../vendor/autoload.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Isolation forest</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>

<body style="display:flex; flex-wrap:wrap; max-width:100vw;">
    <h1>Isolation forest</h1>
<?php

$ordersCSV = new CSV('orders.csv', true);
$datasetOrders = Unlabeled::fromIterator($ordersCSV);
$numericTransformer = new NumericStringConverter();
$datasetOrders->apply($numericTransformer);

//Create array full of 0 to represent the y axis
$y = array_fill(0, $datasetOrders->numSamples(), 0);


//Plot the dataset
echo "<div>";
$data =[
    ['x' => $datasetOrders->feature(0), 'y' => $y , 'name'=>'points', 'mode' => 'markers', 'type' => 'scatter']
];
echo "<div id='scatter'></div>";
$scatter = new ScatterPlot('scatter', $data);
echo "<script>".$scatter->render()."</script>";
echo "</div>";

//Create the isolation forest
$forest = new IsolationForest(100, null, 0.01);
$forest->train($datasetOrders);

$datasetToAnalyse = new Unlabeled([[30],[160],[10000]]);
$detections = $forest->predict($datasetToAnalyse);

echo "<pre>";
print_r($detections);
echo "</pre>";

//Create samples from 50 to 250 with a 1 step
$samples = [];
for($x = 50; $x <= 250; $x++){
    $samples[] = [$x];
}
$datasetVisualisation = new Unlabeled($samples);
$detections = $forest->predict($datasetVisualisation);

//Group by cluster
$numberGroups = count(array_unique($detections));
$grouped = [];
foreach($detections as $i => $detection){
    $grouped[$detection][] = $datasetVisualisation->sample($i);
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