<?php

require __DIR__ . '/../vendor/autoload.php';

use Fize\Framework\App;

$app = new App(
    [
        'root_path' => __DIR__,
        'module'    => 'tests'
    ]
);
$app->run();
