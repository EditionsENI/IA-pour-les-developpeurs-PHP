<?php

//Récolte des données
use function Rubix\ML\iterator_contains_nan;

use Php2plotly\stats\BoxPlot;
use Php2plotly\stats\Histogram;
use Rubix\ML\Classifiers\AdaBoost;
use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Classifiers\RandomForest;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Extractors\ColumnPicker;
use Rubix\ML\Extractors\CSV;
use Rubix\ML\Transformers\MissingDataImputer;
use Rubix\ML\Transformers\NumericStringConverter;

require_once __DIR__ . '../../../vendor/autoload.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Decision Tree</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>

<body style="display:flex; flex-wrap:wrap; max-width:100vw;">
<?php

//Import Data from CSV file
$titanicData = new CSV('titanic.csv', true, ',', '"');
$columnNames = ['Age','SibSp','Parch','Fare', 'Pclass', 'Sex', 'Embarked', 'Survived'];
$picker = new ColumnPicker($titanicData, $columnNames);
$dataset = Labeled::fromIterator($picker);
$dataset = $dataset->deduplicate();

//Data type conversion String to numeric
$numericStringConverter = new NumericStringConverter();
$dataset->apply($numericStringConverter);
$dataset->apply(new MissingDataImputer());

$dataset->transformLabels(function ($label) {
    return $label == '1' ? 'Survived' : 'Disappeared';
});


//Remove missing values and outliers
$description = $dataset->describe();
$upperFences = [];
$lowerFences = [];
foreach($description as $key=>$fieldDetails){
    if($fieldDetails["type"] != "continuous"){
        continue;
    }
    $upperFences[$key] = $fieldDetails["75%"] + (1.5 * ($fieldDetails["75%"] - $fieldDetails["25%"])) ;
    $lowerFences[$key] = $fieldDetails["25%"] - (1.5 * ($fieldDetails["75%"] - $fieldDetails["25%"]));
}

$cleanRecord = function ($record) use ($upperFences, $lowerFences, $description){
    $missesContinuousValue = iterator_contains_nan($record);
    $isInsideFences = true;
    foreach($record as $key=>$value){
        if($description[$key]["type"] == "continuous"){    
            if($value > $upperFences[$key] || $value < $lowerFences[$key]){
                $isInsideFences = false;
                break;
            }
        }
    }
    return !$missesContinuousValue && $isInsideFences;
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
    if($description[$key]["type"] != "continuous"){
        continue;
    }
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
echo "<center><strong>Survival ?</strong></center>";
echo "</div>";

//Split the dataset
[$training, $testing] = $dataset->stratifiedSplit(0.8);

//Create the decision tree
$estimatorClassificationTree = new ClassificationTree();
$estimatorClassificationTree->train($training);

$report = new MulticlassBreakdown();
$resultsClassificationTree = $report->generate($estimatorClassificationTree->predict(new Unlabeled($testing->samples())), $testing->labels());

echo "<pre>";
print_r($resultsClassificationTree);
echo "</pre>";

//Create the Random Forest
$estimatorRandomForest = new RandomForest();
$estimatorRandomForest->train($training);

$reportRandomForest = new MulticlassBreakdown();
$resultsRandomForest = $reportRandomForest->generate($estimatorRandomForest->predict(new Unlabeled($testing->samples())), $testing->labels());

echo "<pre>";
print_r($resultsRandomForest);
echo "</pre>";

$estimatorAdaBoost = new AdaBoost(new ClassificationTree(), 0.4);
$estimatorAdaBoost->train($training);

$reportAdaBoost = new MulticlassBreakdown();
$resultsAdaBoost = $reportAdaBoost->generate($estimatorAdaBoost->predict(new Unlabeled($testing->samples())), $testing->labels());

echo "<pre>";
print_r($resultsAdaBoost);
echo "</pre>";

?>
</body>





