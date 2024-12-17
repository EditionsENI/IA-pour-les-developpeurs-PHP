<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Serializers\RBX;

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Load model</title>
</head>

<body>
    <h1>Load model</h1>

<?php

//Load estimator from file
$fileSystem = new Filesystem("../save/randomForest.rbx");
$encoding = $fileSystem->load();
$serializer = new RBX();
$estimator = $serializer->deserialize($encoding);

// echo "<pre>";
// print_r($estimator);
// echo "</pre>";

//Test the loaded model
$test = [[0, 0], [1, 1], [2, 2], [3, 3], [-1, 0], [5, 5], [6, 6], [7, 7], [8, 8], [9, 9]];
$predictions = $estimator->predict(new Unlabeled($test));

echo "<pre>";
print_r($predictions);
echo "</pre>";