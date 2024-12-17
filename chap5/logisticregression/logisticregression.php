<?php

use Php2plotly\stats\BoxPlot;
use Php2plotly\stats\Histogram;
use Rubix\ML\Classifiers\LogisticRegression;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Extractors\ColumnPicker;
use Rubix\ML\Extractors\CSV;

use Rubix\ML\Transformers\NumericStringConverter;
use Rubix\ML\Transformers\ZScaleStandardizer;

require_once __DIR__ . '../../../vendor/autoload.php';

?>
<html>
<head>
    <title>Logistic Regression</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>
<body>
<h1>Logistic Regression</h1>
<div style="display: flex; flex-wrap: wrap;">
<?php

// Loading DataSet
$cancerDataCSV = new CSV('data_cancer.csv', true, ',', '"');

$columnNames = ["radius_mean","texture_mean","perimeter_mean","area_mean","smoothness_mean","compactness_mean","concavity_mean","concave points_mean","symmetry_mean","fractal_dimension_mean","radius_se","texture_se","perimeter_se","area_se","smoothness_se","compactness_se","concavity_se","concave points_se","symmetry_se","fractal_dimension_se","radius_worst","texture_worst","perimeter_worst","area_worst","smoothness_worst","compactness_worst","concavity_worst","concave points_worst","symmetry_worst","fractal_dimension_worst","diagnosis"];
$dataPicked = new ColumnPicker($cancerDataCSV, $columnNames);
$dataset = Labeled::fromIterator($dataPicked);

$numericTransformer = new NumericStringConverter();
$dataset->apply($numericTransformer);

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
echo "<center><strong>Diagnosis</strong></center>";
echo "</div>";

//Apply ZScaleStandardizer
$dataset->apply(new ZScaleStandardizer());

//Split the dataset into training and testing sets
[$training, $testing] = $dataset->stratifiedSplit(0.8);

//Apply Logistic regression
$logisticRegressor = new LogisticRegression();
$logisticRegressor->train($training);

$report = new MulticlassBreakdown();
$results = $report->generate($logisticRegressor->predict(new Unlabeled($testing->samples())), $testing->labels());
echo "<pre>";
print_r($results);
echo "</pre>";

?>
</div>

</body>
</html>

