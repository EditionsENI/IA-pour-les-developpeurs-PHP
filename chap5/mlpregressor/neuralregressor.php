<?php

use Php2plotly\basic\ScatterPlot;
use Rubix\ML\CrossValidation\Reports\ErrorAnalysis;
use Rubix\ML\Datasets\Labeled;

use Rubix\ML\Helpers\Graphviz;
use Rubix\ML\NeuralNet\ActivationFunctions\ReLU;
use Rubix\ML\NeuralNet\Layers\Activation;

use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Layers\Dropout;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Regressors\MLPRegressor;

require_once __DIR__ . '../../../vendor/autoload.php';

?>
<html>
<head>
    <title>Regression Neural Network</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>
<body>
<h1>Regression Neural Network</h1>
<div style="display: flex; flex-wrap: wrap;">
<?php

function dataToPredict($x){
    return  $x * \cos($x);
}

//Samples and labels arrays initialization
$X = $y = [];

// take 15000 random X values between -10 and 10 to build the dataset
for($i=0; $i<15000; $i++){
    $X[] = rand(-100, 100)/10;
}

// Generate y values
foreach($X as $x){
    $y[] = dataToPredict($x);
}

//Create the dataset
$samples = array_map(fn($x) => [$x], $X);
$dataset = new Labeled($samples, $y);

$scatterPlotInit = new ScatterPlot('scatter_init', [['x'=>$X, 'y'=>$y , 'mode'=>'markers', 'name'=>'Data']]);
echo "<div id='scatter_init'></div>";
echo "<script>".$scatterPlotInit->render()."</script>";

//Create the neural network
$estimator = new MLPRegressor([
    new Dense(30),
    new Activation(new ReLU()),
    new Dropout(0.1),
    new Dense(30),
    new Activation(new ReLU()),
    new Dense(30),
    new Activation(new ReLU()),
]);

[$training, $testing] = $dataset->split(0.8);

//Train the neural network
$estimator->train($training);


$predictions = $estimator->predict($testing);

$xTesting = array_map(fn($sample) => $sample[0], $testing->samples());
// Plot the data
$scatterPlot = new ScatterPlot('scatter', [['x'=>$X, 'y'=>$y , 'mode'=>'markers', 'name'=>'Data'], ['x'=>$xTesting, 'y'=>$predictions, 'mode'=>'markers', 'name'=>'Predictions']]);
echo "<div id='scatter'></div>";
echo "<script>".$scatterPlot->render()."</script>";

echo "<div>";
//Error Reporting
$errorAnalysis = new ErrorAnalysis();
echo "<pre>";
print_r($errorAnalysis->generate($predictions, $testing->labels()));
echo "</pre>";
echo "</div>";

echo "<div>";
echo "<pre>";
foreach($estimator->steps() as $step){
    print_r($step);
}
echo "</pre>";
echo "</div>";

$dot = $estimator->exportGraphviz();

Graphviz::dotToImage($dot)->saveTo(new Filesystem('network.png'));

?>
</div>