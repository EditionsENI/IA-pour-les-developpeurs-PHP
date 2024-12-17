<?php

use Php2plotly\basic\ScatterPlot;
use Php2plotly\stats\Histogram;
use Rubix\ML\Classifiers\MultilayerPerceptron;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Extractors\CSV;
use Rubix\ML\NeuralNet\ActivationFunctions\Relu;
use Rubix\ML\NeuralNet\Layers\Activation;
use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Layers\Dropout;
use Rubix\ML\NeuralNet\Optimizers\Adam;

require_once __DIR__ . '../../../vendor/autoload.php';

?>
<html>
<head>
    <title>Neural classifier</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>
<body>
<h1>Neural classifier</h1>
<div style="display: flex; flex-wrap: wrap;">
<?php

$features = [];
$labels = [];

//Generate the dataset
for($i=0; $i<20000; $i++){
    //Get random $x and $y values in the range of -1.5 to 1.5
    $x = rand(-150, 150)/100;
    $y = rand(-150, 150)/100;
    //Compute if the point is a disk centered at (0,0) with radius 1
    if(($x**2 + $y**2) < 1 && ($x**2 + $y**2)>0.5){
        $label = 1;
    }else if (($x**2 + $y**2) < 0.5){
        $label = 2;
    }else{
        $label = 0;
    }
    $labels[] = "Classe ".$label;
    $features[] = [$x, $y];
}

$dataset = new Labeled($features, $labels);


//Display the dataset features in a scatter plot
//Create an array with 3 datasets, one for each class
$stratified = $dataset->stratifyByLabel();

//Plot the dataset
echo "<div>";
$data =[
    ['x' => $stratified["Classe 0"]->feature(0), 'y' => $stratified["Classe 0"]->feature(1), 'name'=>'class 0', 'mode' => 'markers', 'type' => 'scatter'],
    ['x' => $stratified["Classe 1"]->feature(0), 'y' => $stratified["Classe 1"]->feature(1), 'name'=>'class 1', 'mode' => 'markers', 'type' => 'scatter'],
    ['x' => $stratified["Classe 2"]->feature(0), 'y' => $stratified["Classe 2"]->feature(1), 'name'=>'class 2', 'mode' => 'markers', 'type' => 'scatter'],
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

//Split the dataset into training and testing sets
[$training, $testing] = $dataset->randomize()->stratifiedSplit(0.8);

$estimatorNeuralClassifier = new MultilayerPerceptron([
    new Dense(50),
    new Activation(new Relu()),
    new Dropout(0.3),
    new Dense(50),
    new Activation(new Relu()),
    new Dropout(0.3),
    new Dense(50),
    new Activation(new Relu()),
], 128, new Adam(0.001));


$estimatorNeuralClassifier->train($training);

//iterable to array
$dataTraining = iterator_to_array($estimatorNeuralClassifier->steps());

//plot the training progress
echo "<div>";
$data =[
    ['x' => array_column($dataTraining, "epoch"), 'y' => array_column($dataTraining, "score"), 'name'=>'score', 'mode' => 'lines', 'type' => 'scatter'],
    ['x' => array_column($dataTraining, "epoch"), 'y' => array_column($dataTraining, "loss"), 'name'=>'loss', 'mode' => 'lines', 'type' => 'scatter'],
];
echo "<div id='progress'></div>";
$scatter = new ScatterPlot('progress', $data);
echo "<script>".$scatter->render()."</script>";
echo "<center><strong>Training progress</strong></center>";
echo "</div>";

$report = new MulticlassBreakdown();
$results = $report->generate($estimatorNeuralClassifier->predict(new Unlabeled($testing->samples())), $testing->labels());

echo "<div>";
echo "<pre>";
print_r($results);
echo "</pre>";
echo "</div>";

//Plot the testing set
$predictions = $estimatorNeuralClassifier->predict(new Unlabeled($testing->samples()));
$predictionLabelsClasse0 = [];
$predictionLabelsClasse1 = [];
$predictionLabelsClasse2 = [];

foreach($predictions as $key=>$prediction){
    if($prediction == "Classe 0"){
        $predictionLabelsClasse0[] = $testing->sample($key);
    }
    else if($prediction == "Classe 1"){
        $predictionLabelsClasse1[] = $testing->sample($key);
    }
    else{
        $predictionLabelsClasse2[] = $testing->sample($key);
    }
}


echo "<div>";
$data =[
    ["x"=>array_column($predictionLabelsClasse0, 0), "y"=>array_column($predictionLabelsClasse0, 1), 'name'=>'class 0', 'mode' => 'markers', 'type' => 'scatter'],
    ["x"=>array_column($predictionLabelsClasse1, 0), "y"=>array_column($predictionLabelsClasse1, 1), 'name'=>'class 1', 'mode' => 'markers', 'type' => 'scatter'],
    ["x"=>array_column($predictionLabelsClasse2, 0), "y"=>array_column($predictionLabelsClasse2, 1), 'name'=>'class 2', 'mode' => 'markers', 'type' => 'scatter'],
];
echo "<div id='scatter_predict'></div>";
$scatter = new ScatterPlot('scatter_predict', $data);
echo "<script>".$scatter->render()."</script>";
echo "</div>";