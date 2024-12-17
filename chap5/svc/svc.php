<?php

use Php2plotly\basic\ScatterPlot;
use Php2plotly\stats\Histogram;
use Rubix\ML\Classifiers\SVC;
use Rubix\ML\CrossValidation\KFold;
use Rubix\ML\CrossValidation\Metrics\FBeta;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;

require_once __DIR__ . '../../../vendor/autoload.php';

?>
<html>
<head>
    <title>SVC</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>
<body>
<h1>SVC</h1>
<div style="display: flex; flex-wrap: wrap;">
<?php

$features = [];
$labels = [];

//Generate the dataset
for($i=0; $i<10000; $i++){
    //Get random $x and $y values in the range of -1.5 to 1.5
    $x = rand(-150, 150)/100;
    $y = rand(-150, 150)/100;
    //Compute if the point is in the half moon shape
    $label = ($y < ($x**4)+0.5) && ($y>($x**4)-0.8)  ? 1 : 0;
    $labels[] = "Classe ".$label;
    $features[] = [$x, $y];
}

$dataset = new Labeled($features, $labels);


//Display the dataset features in a scatter plot
//Create an array with 2 datasets, one for each class
$stratified = $dataset->stratifyByLabel();

//Plot the dataset
echo "<div>";
$data =[
    ['x' => $stratified["Classe 0"]->feature(0), 'y' => $stratified["Classe 0"]->feature(1), 'name'=>'class 0', 'mode' => 'markers', 'type' => 'scatter'],
    ['x' => $stratified["Classe 1"]->feature(0), 'y' => $stratified["Classe 1"]->feature(1), 'name'=>'class 1', 'mode' => 'markers', 'type' => 'scatter']
];
echo "<div id='scatter'></div>";
$scatter = new ScatterPlot('scatter', $data);
echo "<script>".$scatter->render()."</script>";
echo "</div>";

//Display the labels distributions
echo "<div>";
$data = ['x'=> $dataset->labels()];
echo "<div id='labels'></div>";
$histogram = new Histogram('labels', $data);
echo "<script>".$histogram->render()."</script>";
echo "<center><strong>Labels</strong></center>";
echo "</div>";

$datasetClass0 = $stratified["Classe 0"];
$datasetClass1 = $stratified["Classe 1"];

[$datasetClass0, $datasetAuxClass0] = $datasetClass0->split(0.5);

$dataset = $datasetClass0->merge($datasetClass1);

//Split the dataset into training and testing sets
[$training, $testing] = $dataset->randomize()->stratifiedSplit(0.8);

//Train the SVC
$estimatorSVC = new SVC();

//Train the model with Kfold
$validator = new KFold(5);
$validator->test($estimatorSVC, $training, new FBeta());



$report = new MulticlassBreakdown();
$resultsSVC = $report->generate($estimatorSVC->predict(new Unlabeled($testing->samples())), $testing->labels());

echo "<div>";
echo "<pre>";
print_r($resultsSVC);
echo "</pre>";
echo "</div>";

//Plot the testing set
$predictions = $estimatorSVC->predict(new Unlabeled($testing->samples()));
$predictionLabelsClasse0 = [];
$predictionLabelsClasse1 = [];

foreach($predictions as $key=>$prediction){
    if($prediction == "Classe 0"){
        $predictionLabelsClasse0[] = $testing->sample($key);
    }else{
        $predictionLabelsClasse1[] = $testing->sample($key);
    }
}


echo "<div>";
$data =[
    ["x"=>array_column($predictionLabelsClasse0, 0), "y"=>array_column($predictionLabelsClasse0, 1), 'name'=>'class 0', 'mode' => 'markers', 'type' => 'scatter'],
    ["x"=>array_column($predictionLabelsClasse1, 0), "y"=>array_column($predictionLabelsClasse1, 1), 'name'=>'class 1', 'mode' => 'markers', 'type' => 'scatter']
];
echo "<div id='scatter_predict'></div>";
$scatter = new ScatterPlot('scatter_predict', $data);
echo "<script>".$scatter->render()."</script>";
echo "</div>";