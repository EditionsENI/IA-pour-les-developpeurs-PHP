<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Serializers\RBX;
use Rubix\Server\HTTPServer;

//Load estimator from file
$fileSystem = new Filesystem("../save/randomForest.rbx");
$encoding = $fileSystem->load();
$serializer = new RBX();
$estimator = $serializer->deserialize($encoding);

$server = new HTTPServer('127.0.0.1', 8003);
$server->serve($estimator);