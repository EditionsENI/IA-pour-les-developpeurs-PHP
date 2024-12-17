<?php

use Php2plotly\basic\ScatterPlot;
use Rubix\ML\Clusterers\KMeans;
use Rubix\ML\Datasets\Generators\Agglomerate;
use Rubix\ML\Datasets\Generators\Blob;
use Rubix\ML\Datasets\Generators\Circle;

require_once __DIR__ . '../../../vendor/autoload.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KMeans</title>
    <script src="../../assets/js/plotly-2.32.0.min.js" charset="utf-8"></script>
</head>

<body style="display:flex; flex-wrap:wrap; max-width:100vw;">
    <h1>KMeans</h1>
<?php

$generator = new Agglomerate([
    'blob1' => new Blob([5, 7], 0.3),
    'blob2' => new Blob([3, 2], 0.3),
    'circle1' => new Circle(-4, -2, 0.0, 0.2),
    'circle2' => new Circle(-4, -2, 2.0, 0.2),
], [
    1, 1, 1, 1
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

$inertias = [];

// Try different numbers of clusters (e.g., from 1 to 6)
for ($k = 1; $k <= 6; $k++) {
    // Create a KMeans clusterer with k clusters
    $clusterer = new KMeans($k);
    // Train the clusterer on the dataset
    $clusterer->train($dataset);
    // Get the centroids of the clusters
    $centroids = $clusterer->centroids();
    // Initialize the inertia for k clusters
    $inertia = 0.0;
    // Calculate the inertia
    foreach ($dataset->samples() as $i => $sample) {
        // Predict the cluster for each sample
        $cluster = $clusterer->predictSample($sample);
        // Calculate the squared Euclidean distance between the sample and its centroid
        $distance = 0.0;
        foreach ($sample as $j => $feature) {
            $distance += ($feature - $centroids[$cluster][$j]) ** 2;
        }
        // Add the squared distance to the total inertia
        $inertia += $distance;
    }

    // Record the inertia for this number of clusters
    $inertias[$k] = $inertia;
}

// Plot the inertia as a function of the number of clusters
echo "<div>";
$data =[
    ['x' => array_keys($inertias), 'y' => array_values($inertias), 'name'=>'inertia', 'mode' => 'lines', 'type' => 'scatter']
];
echo "<div id='inertia'></div>";
$scatter = new ScatterPlot('inertia', $data);
echo "<script>".$scatter->render()."</script>";
echo "<center>Elbow method with Inertia data</center>";
echo "</div>";

//With the best number of clusters
$bestK = 3;
$kmeans = new KMeans($bestK);
$kmeans->train($dataset);
$groups = $kmeans->predict($dataset);

//Group by cluster
$grouped = [];
foreach($groups as $i => $group){
    $grouped[$group][] = $dataset->sample($i);
}

//Plot the clusters
echo "<div>";
$data = [];
foreach($grouped as $i => $samples){
    $data[] = ['x' => array_column($samples, 0), 'y' => array_column($samples, 1), 'name'=>'cluster '.$i, 'mode' => 'markers', 'type' => 'scatter'];
}
echo "<div id='clusters'></div>";
$scatter = new ScatterPlot('clusters', $data);
echo "<script>".$scatter->render()."</script>";
echo "</div>";

//With K=4 clusters
$TestK = 4;
$kmeansTest = new KMeans($TestK);
$kmeansTest->train($dataset);
$groupsTest = $kmeansTest->predict($dataset);

//Group by cluster
$groupedTest = [];
foreach($groupsTest as $i => $group){
    $groupedTest[$group][] = $dataset->sample($i);
}

//Plot the clusters
echo "<div>";
$dataTest = [];
foreach($groupedTest as $i => $samples){
    $dataTest[] = ['x' => array_column($samples, 0), 'y' => array_column($samples, 1), 'name'=>'cluster '.$i, 'mode' => 'markers', 'type' => 'scatter'];
}
echo "<div id='clustersTest'></div>";
$scatterTest = new ScatterPlot('clustersTest', $dataTest);
echo "<script>".$scatterTest->render()."</script>";
echo "</div>";



?>
</body>