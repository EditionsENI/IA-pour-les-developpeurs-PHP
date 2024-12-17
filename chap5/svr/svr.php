<?php

use Php2plotly\stats\BoxPlot;
use Php2plotly\stats\Histogram;
use Rubix\ML\CrossValidation\HoldOut;
use Rubix\ML\CrossValidation\Metrics\RMSE;
use Rubix\ML\CrossValidation\Metrics\RSquared;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Extractors\ColumnPicker;
use Rubix\ML\Extractors\CSV;
use Rubix\ML\GridSearch;
use Rubix\ML\Kernels\Distance\Euclidean;
use Rubix\ML\Kernels\SVM\Linear;
use Rubix\ML\Kernels\SVM\Polynomial;
use Rubix\ML\Kernels\SVM\RBF;
use Rubix\ML\Loggers\Screen;
use Rubix\ML\Regressors\KNNRegressor;
use Rubix\ML\Regressors\SVR;
use Rubix\ML\Transformers\NumericStringConverter;
use Rubix\ML\Transformers\ZScaleStandardizer;

require_once __DIR__ . '../../../vendor/autoload.php';

?>
<html>
<head>
    <title>SVR</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>
<body>
<h1>SVR</h1>
<div style="display: flex; flex-wrap: wrap;">
<?php

//Extract Dataset
$admissionDataCSV = new CSV('admission_data.csv', true, ',', '"');

$columnNames = ['GRE Score', 'TOEFL Score', 'University Rating', 'SOP', 'LOR', 'CGPA', 'Research', 'Chance of Admit'];
$dataPicked = new ColumnPicker($admissionDataCSV, $columnNames);
//Process continuous data
$dataset = Labeled::fromIterator($dataPicked);

$numericTransformer = new NumericStringConverter();
$dataset->apply($numericTransformer);
$dataset->transformLabels("floatVal");

$dataset = $dataset->deduplicate();

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
echo "<center><strong>Admission Probability</strong></center>";
echo "</div>";

$standardizer = new ZScaleStandardizer();
$dataset->apply($standardizer);

//Grid search optimisation
//Set all params combinations to optimize
$params = [
    [1, 0.5], 
    [0.01, 0.1],
    [new Linear(), new RBF()],
];

//GridSearch creation and training
echo "
<div>
<h2>Grid Search</h2>
<pre>";   
$optimizedEstimator = new GridSearch(SVR::class, $params, new RSquared(), new HoldOut(0.2));
$optimizedEstimator->setLogger(new Screen('SVR'));
$optimizedEstimator->train($dataset);
echo "</pre>";

//Display metrics and optimized params
echo "<pre>";
print_r($optimizedEstimator->base()->params());
echo "</pre></div>";
?>
</div>

</body>
</html>

