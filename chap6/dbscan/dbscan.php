<?php

use Php2plotly\basic\ScatterPlot;
use Rubix\ML\Clusterers\DBSCAN;
use Rubix\ML\Datasets\Generators\Agglomerate;
use Rubix\ML\Datasets\Generators\Blob;
use Rubix\ML\Datasets\Generators\Circle;
use Rubix\ML\Datasets\Generators\HalfMoon;

require_once __DIR__ . '../../../vendor/autoload.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DBSCAN</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>

<body style="display:flex; flex-wrap:wrap; max-width:100vw;">
    <h1>DBSCAN</h1>
<?php

$generator = new Agglomerate([
    'blob1' => new Blob([5, 7], 1.0),
    'circle1' => new Circle(2, -4, 1.0, 0.3),
    'circle2' => new Circle(2, -4, 4.0, 0.3),
    'halfmoon1' => new HalfMoon(-2.0, 6.0, 1.5),
    'halfmoon2' => new HalfMoon(-2.0, 4.5, 1.5, 270),
], [
    1, 1, 1, 1, 1
]);

$dataset = $generator->generate(5000);

//Plot the dataset
echo "<div>";
$data =[
    ['x' => $dataset->feature(0), 'y' => $dataset->feature(1), 'name'=>'points', 'mode' => 'markers', 'type' => 'scatter']
];
echo "<div id='scatter'></div>";
$scatter = new ScatterPlot('scatter', $data);
echo "<script>".$scatter->render()."</script>";
echo "</div>";

//DBSCAN
$dbscan = new DBSCAN();
$groups = $dbscan->predict($dataset);

//Group by cluster
$numberGroups = count(array_unique($groups));
$grouped = [];
foreach($groups as $i => $group){
    $grouped[$group][] = $dataset->sample($i);
}

//Plot the clusters
echo "<div>";
$data = [];
foreach($grouped as $i => $samples){
    if($i == -1){
        $data[] = ['x' => array_column($samples, 0), 'y' => array_column($samples, 1), 'name'=>'noise', 'mode' => 'markers', 'type' => 'scatter'];
    }else{
        $data[] = ['x' => array_column($samples, 0), 'y' => array_column($samples, 1), 'name'=>'cluster '.$i, 'mode' => 'markers', 'type' => 'scatter'];
    }
}
echo "<div id='clusters'></div>";
$scatter = new ScatterPlot('clusters', $data);
echo "<script>".$scatter->render()."</script>";
echo "</div>";


?>
</body>