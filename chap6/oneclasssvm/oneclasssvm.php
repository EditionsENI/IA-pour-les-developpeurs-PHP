<?php

use Php2plotly\basic\ScatterPlot;
use Rubix\ML\AnomalyDetectors\OneClassSVM;
use Rubix\ML\Datasets\Generators\Blob;
use Rubix\ML\Datasets\Unlabeled;

require_once __DIR__ . '../../../vendor/autoload.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>One-Class SVM</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>

<body style="display:flex; flex-wrap:wrap; max-width:100vw;">
    <h1>One-Class SVM</h1>
<?php

$generator = new Blob([200,400], 2);
$dataset = $generator->generate(2000);

//Plot the dataset
echo "<div>";
$data =[
    ['x' => $dataset->feature(0), 'y' => $dataset->feature(1), 'name'=>'points', 'mode' => 'markers', 'type' => 'scatter']
];
echo "<div id='scatter'></div>";
$scatter = new ScatterPlot('scatter', $data);
echo "<script>".$scatter->render()."</script>";
echo "</div>";


//OCSVM
$ocsvm = new OneClassSVM();
$ocsvm->train($dataset);
$samples = [[0,30], [201, 402]];
$detections = $ocsvm->predict(new Unlabeled($samples));

echo "<pre>";
print_r($detections);
echo "</pre>";

//Create samples from [150, 350] to [250, 450] with a 0.5 step
$samples = [];
for($x = 150; $x <= 250; $x += 0.5){
    for($y = 350; $y <= 450; $y += 0.5){
        $samples[] = [$x, $y];
    }
}
$datasetVisualisation = new Unlabeled($samples);
$detections = $ocsvm->predict($datasetVisualisation);

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
        $data[] = ['x' => array_column($samples, 0), 'y' => array_column($samples, 1), 'name'=>'anomaly', 'mode' => 'markers', 'type' => 'scatter'];
    }else{
        $data[] = ['x' => array_column($samples, 0), 'y' => array_column($samples, 1), 'name'=>'normal', 'mode' => 'markers', 'type' => 'scatter'];
    }
}

echo "<div id='clusters'></div>";
$scatter = new ScatterPlot('clusters', $data);
echo "<script>".$scatter->render()."</script>";
echo "</div>";


?>
</body>