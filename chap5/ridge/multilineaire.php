<?php

use Php2plotly\basic\ScatterPlot;
use Rubix\ML\CrossValidation\Metrics\RMSE;
use Rubix\ML\CrossValidation\Metrics\RSquared;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Extractors\CSV;
use Rubix\ML\Regressors\Ridge;
use Rubix\ML\Transformers\NumericStringConverter;

require_once __DIR__ . '../../../vendor/autoload.php';

?>
<html>
<head>
    <title>Linear Regression</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>
<body>
<h1>Multi Linear Regression</h1>

<?php

// Loading DataSet
$dataset = Labeled::fromIterator(new CSV("dataset_taxi_multi.csv", true))
    ->apply(new NumericStringConverter())->transformLabels('floatVal');

//Plot the dataset with PHP2Plotly
$dataDistance = [['x' => $dataset->feature(0), 'y' => $dataset->labels(), 'type' => 'scatter', 'mode' => 'markers', 'name' => 'Distance/Price']];
echo "<div id='chartDistance'></div>";
$scatterplotDistance = new ScatterPlot('chartDistance', $dataDistance);
echo '<script>' . $scatterplotDistance->render() . '</script>';

$dataTime = [['x' => $dataset->feature(1), 'y' => $dataset->labels(), 'type' => 'scatter', 'mode' => 'markers', 'name' => 'Time Length/Price']];
echo "<div id='chartTime'></div>";
$scatterplotTime = new ScatterPlot('chartTime', $dataTime);
echo '<script>' . $scatterplotTime->render() . '</script>';

//Split the dataset into training and testing sets
[$training, $testing] = $dataset->randomize()->split(0.8);

//Apply ridge regression
$lambda = 0;
$regressor = new Ridge($lambda);
$regressor->train($training);

$coefficients = $regressor->coefficients();
$bias = $regressor->bias();

//Predictions
$predictions = $regressor->predict($testing);

//Compute RMSE between the predictions and the actual values
$metricRmse = new RMSE();
$rmse = round($metricRmse->score($predictions, $testing->labels()));
//Compute the R squared metric
$metricRSquared = new RSquared();
$rSquared = round($metricRSquared->score($predictions, $testing->labels()), 2);

echo "<p>RMSE: $rmse</p>";
echo "<p>R squared: $rSquared</p>";

echo "<p>Equation of the line: y = $coefficients[0] * x1 + $coefficients[1] * x2 + $bias</p>";

?>
</body>
</html>


