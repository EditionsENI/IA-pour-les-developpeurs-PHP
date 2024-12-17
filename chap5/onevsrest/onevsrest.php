<?php

use function Rubix\ML\iterator_contains_nan;

use Php2plotly\stats\BoxPlot;
use Php2plotly\stats\Histogram;
use Rubix\ML\Classifiers\LogisticRegression;
use Rubix\ML\Classifiers\OneVsRest;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Extractors\CSV;
use Rubix\ML\Transformers\LambdaFunction;
use Rubix\ML\Transformers\NumericStringConverter;
use Rubix\ML\Transformers\ZScaleStandardizer;


require_once __DIR__ . '../../../vendor/autoload.php';

?>
<html>
<head>
    <title>One VS Rest - Multiclass Logistic Regression</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>
<body>
<h1>One VS Rest - Multiclass Logistic Regression</h1>
<div style="display: flex; flex-wrap: wrap;">
<?php

// Loading DataSet
$wineDataCSV = new CSV('winequalityN.csv', true, ',', '"');
$columnNames = [
    'type', 'fixed acidity', 'volatile acidity', 'citric acid', 'residual sugar', 
    'chlorides', 'free sulfur dioxide', 'total sulfur dioxide', 'density', 
    'pH', 'sulphates', 'alcohol', 'quality'
];

$dataset = Labeled::fromIterator($wineDataCSV);

//Apply transformations
$callback = function (&$sample, $offset, $context) {
    $type = $sample[0];
    $sample[0] = (int)($type == 'red');
};
$typeTransformer = new LambdaFunction($callback);
$numericTransformer = new NumericStringConverter();

$dataset->apply($typeTransformer);
$dataset->apply($numericTransformer);
$dataset->transformLabels(function($value){
    return "Quality = ".$value;
});

$cleanRecord = function ($record){
    $missesContinuousValue = iterator_contains_nan($record);
    return !$missesContinuousValue;
};
$dataset = $dataset->filter($cleanRecord);
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
echo "<center><strong>Quality</strong></center>";
echo "</div>";

//Remove missing values and outliers
$description = $dataset->describe();
$upperFences = [];
$lowerFences = [];
foreach($description as $fieldDetails){
    if($fieldDetails['type'] != 'continuous'){
        continue;
    }
    $upperFences[] = $fieldDetails["75%"] + (1.5 * ($fieldDetails["75%"] - $fieldDetails["25%"])) ;
    $lowerFences[] = $fieldDetails["25%"] - (1.5 * ($fieldDetails["75%"] - $fieldDetails["25%"]));
}

$cleanRecord = function ($record) use ($upperFences, $lowerFences, $columnNames){
    $missesContinuousValue = iterator_contains_nan($record);
    $isInsideFences = true;
    foreach($record as $key=>$value){
        if($key != (count($columnNames) - 1)){        
            if($value > $upperFences[$key] || $value < $lowerFences[$key]){
                $isInsideFences = false;
                break;
            }
        }
    }
    return !$missesContinuousValue && $isInsideFences;
};
$dataset = $dataset->filter($cleanRecord);


//Apply ZScaleStandardizer
$dataset->apply(new ZScaleStandardizer());

//Split the dataset into training and testing sets
[$training, $testing] = $dataset->stratifiedSplit(0.8);


//Apply Logistic regression
$oneVsRestClassifier = new OneVsRest(new LogisticRegression());
$oneVsRestClassifier->train($training);

$report = new MulticlassBreakdown();
$results = $report->generate($oneVsRestClassifier->predict(new Unlabeled($testing->samples())), $testing->labels());
echo "<pre>";
print_r($results);
echo "</pre>";

?>
</div>

</body>
</html>

