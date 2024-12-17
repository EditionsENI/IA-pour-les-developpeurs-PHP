<?php

use function Rubix\ML\iterator_contains_nan;
use Php2plotly\stats\BoxPlot;
use Php2plotly\stats\Histogram;
use Rubix\ML\CrossValidation\HoldOut;
use Rubix\ML\CrossValidation\Metrics\RSquared;
use Rubix\ML\CrossValidation\Reports\ErrorAnalysis;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Extractors\CSV;
use Rubix\ML\GridSearch;
use Rubix\ML\Helpers\Graphviz;

use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Regressors\GradientBoost;
use Rubix\ML\Regressors\RegressionTree;
use Rubix\ML\Transformers\NumericStringConverter;

require_once __DIR__ . '../../../vendor/autoload.php';

?>
<html>
<head>
    <title>Regression Tree</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>
<body>
<h1>Regression Tree</h1>
<div style="display: flex; flex-wrap: wrap;">
<?php

// Loading DataSet
$dataset = Labeled::fromIterator(new CSV('housing.csv', true))
    ->apply(new NumericStringConverter())->transformLabels('floatVal');

$columnNames = ['CRIM', 'ZN', 'INDUS', 'CHAS', 'NOX', 'RM', 'AGE', 'DIS', 'RAD', 'TAX', 'PTRATIO', 'B', 'LSTAT'];
$labelName = "MEDV";

$cleanRecord = function ($record){
    $missesContinuousValue = iterator_contains_nan($record);
    return !$missesContinuousValue;
};
$dataset = $dataset->filter($cleanRecord);

//Plot the dataset features with PHP2Plotly
foreach($dataset->features() as $key=>$feature){
    echo "<div>";
    $data = ['x'=> $feature];
    echo "<div id='histo_$key'></div>";
    $histogram = new Histogram('histo_'.$key, $data);
    echo "<script>".$histogram->render()."</script>";
    echo "<center><strong>".$columnNames[$key]."</strong></center>";
    echo "</div>";
}

//Display box plots to see if there are outliers
echo '<div style="text-align:center;"><div id="bplot" style="width:600px;height:400px;"></div>Box plots</div>';
$listsBoxPlot = [];
foreach($dataset->features() as $key=>$feature){
    $listsBoxPlot[] = ['y' => $feature];
}
$boxplot = new BoxPlot('bplot', $listsBoxPlot);
echo '<script>'.$boxplot->render().'</script>';

//Plot the labels distributions
echo "<div>";
$data = ['x'=> $dataset->labels()];
echo "<div id='labels'></div>";
$histogram = new Histogram('labels', $data);
echo "<script>".$histogram->render()."</script>";
echo "<center><strong>MDEV</strong></center>";
echo "</div>";

//Drop columns
$dataset->dropFeature(11);
$dataset->dropFeature(1);
$dataset->dropFeature(0);

//Split the dataset into training and testing sets
[$training, $testing] = $dataset->randomize()->split(0.8);

//Apply regression tree
$regressionTree = new RegressionTree();
$regressionTree->train($training);

$report = new ErrorAnalysis();
$results = $report->generate($regressionTree->predict(new Unlabeled($testing->samples())), $testing->labels());
echo "<pre>";
print_r($results);
echo "</pre>";

//Grid search optimisation

//Set all params combinations to optimize
$params = [
    [PHP_INT_MAX, 3, 4, 5], [3, 5, 10, 20]
];

//GridSearch creation and training
$optimizedEstimator = new GridSearch(RegressionTree::class, $params, new RSquared(), new HoldOut(0.2));
$optimizedEstimator->train($training);

//Reports creation on the final testing sub dataset
$reportOptimized = new ErrorAnalysis();
$resultsOptimized = $reportOptimized->generate($optimizedEstimator->predict(new Unlabeled($testing->samples())), $testing->labels());

//Display metrics and optimized params
echo "<pre>";
print_r($resultsOptimized);
print_r($optimizedEstimator->base()->params());
echo "</pre>";


$dot = $regressionTree->exportGraphviz();
Graphviz::dotToImage($dot)->saveTo(new Filesystem('tree.png'));

//Create and Apply Gradient Boost from Decision Trees
$estimatorGradientBoost = new GradientBoost(new RegressionTree(PHP_INT_MAX, 10));
$estimatorGradientBoost->train($training);

$reportGradientBoost = new ErrorAnalysis();
$resultsGradientBoost = $reportGradientBoost->generate($estimatorGradientBoost->predict(new Unlabeled($testing->samples())), $testing->labels());

echo "<pre>";
print_r($resultsGradientBoost);
echo "</pre>";


?>
</div>

</body>
</html>

