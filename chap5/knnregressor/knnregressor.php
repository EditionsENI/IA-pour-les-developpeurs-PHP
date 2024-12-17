<?php

use function Rubix\ML\iterator_contains_nan;

use Php2plotly\stats\BoxPlot;
use Php2plotly\stats\Histogram;
use Rubix\ML\CrossValidation\HoldOut;
use Rubix\ML\CrossValidation\Metrics\RMSE;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Extractors\ColumnPicker;
use Rubix\ML\Extractors\CSV;
use Rubix\ML\GridSearch;
use Rubix\ML\Loggers\Screen;
use Rubix\ML\Regressors\KNNRegressor;
use Rubix\ML\Transformers\NumericStringConverter;
use Rubix\ML\Transformers\ZScaleStandardizer;

require_once __DIR__ . '../../../vendor/autoload.php';

?>
<html>
<head>
    <title>KNN</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>
<body>
<h1>KNN</h1>
<div style="display: flex; flex-wrap: wrap;">
<?php

//Extract Dataset
$extractorSongDataCSV = new CSV('song_data.csv', true, ',', '"');

$columnNames = ['song_duration_ms', 'acousticness', 'danceability', 'energy', 'instrumentalness', 'liveness', 'loudness','speechiness', 'tempo', 'audio_valence', 'song_popularity'];
$dataPicked = new ColumnPicker($extractorSongDataCSV, $columnNames);
//Process continuous data
$dataset = Labeled::fromIterator($dataPicked);

$numericTransformer = new NumericStringConverter();
$dataset->apply($numericTransformer);
$dataset->transformLabels("floatVal");

$dataset = $dataset->deduplicate();

echo "Initial samples number : ".$dataset->numSamples();

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
echo "<center><strong>Popularity</strong></center>";
echo "</div>";

//Remove missing values and outliers
$description = $dataset->describe();
$upperFences = [];
$lowerFences = [];
foreach($description as $fieldDetails){
    $upperFences[] = $fieldDetails["75%"] + (1.5 * ($fieldDetails["75%"] - $fieldDetails["25%"])) ;
    $lowerFences[] = $fieldDetails["25%"] - (1.5 * ($fieldDetails["75%"] - $fieldDetails["25%"]));
}

$cleanRecord = function ($record) use ($upperFences, $lowerFences){
    $missesContinuousValue = iterator_contains_nan($record);
    $isInsideFences = true;
    foreach($record as $key=>$value){
        if($value > $upperFences[$key] || $value < $lowerFences[$key]){
            $isInsideFences = false;
            break;
        }
    }
    return !$missesContinuousValue && $isInsideFences;
};
$dataset = $dataset->filter($cleanRecord);

echo "Samples number after cleaning : ".$dataset->numSamples();

$standardizer = new ZScaleStandardizer();
$dataset->apply($standardizer);

//Grid search optimisation
//Set all params combinations to optimize
$params = [
    [5, 10, 20, 30]
];

//GridSearch creation and training
echo "
<div>
<h2>Grid Search</h2>
<pre>";   
$optimizedEstimator = new GridSearch(KNNRegressor::class, $params, new RMSE(), new HoldOut(0.2));
$optimizedEstimator->setLogger(new Screen('KNNRegressor'));
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

