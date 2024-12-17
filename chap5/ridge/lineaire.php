<?php

use Php2plotly\basic\ScatterPlot;
use Rubix\ML\CrossValidation\Metrics\RMSE;
use Rubix\ML\CrossValidation\Metrics\RSquared;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
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
<h1>Linear Regression</h1>

<?php

// Loading DataSet
$dataset = Labeled::fromIterator(new CSV('dataset_taxi.csv', true))
    ->apply(new NumericStringConverter())->transformLabels('floatVal');

//Plot the dataset with PHP2Plotly
$data = [['x' => $dataset->feature(0), 'y' => $dataset->labels(), 'type' => 'scatter', 'mode' => 'markers', 'name' => 'Taxi fares']];
echo "<div id='chart'></div>";
$scatterplot = new ScatterPlot('chart', $data);
echo '<script>' . $scatterplot->render() . '</script>';

//Split the dataset into training and testing sets
[$training, $testing] = $dataset->randomize()->split(0.8);

//Apply ridge regression
$lambda = 0.0;
$regressorRidge = new Ridge($lambda);
$regressorRidge->train($training);

$coefficients = $regressorRidge->coefficients();
$bias = $regressorRidge->bias();

//Predictions
$predictions = $regressorRidge->predict(new Unlabeled($testing->samples()));

//Compute RMSE between the predictions and the actual values
$metricRmse = new RMSE();
$rmse = round($metricRmse->score($predictions, $testing->labels()));
//Compute the R squared metric
$metricRSquared = new RSquared();
$rSquared = round($metricRSquared->score($predictions, $testing->labels()), 2);

echo "<p>RMSE: $rmse</p>";
echo "<p>R squared: $rSquared</p>";

echo "<p>Equation of the line: y = $coefficients[0] * x + $bias</p>";


?>

</body>
</html>

