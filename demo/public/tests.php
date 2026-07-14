<?php

require __DIR__ . '/../../vendor/autoload.php';

use Fize\Framework\App;

$app = new App(
    [
        'root_path' => dirname(__FILE__, 2),
        'module'    => 'tests'
    ]
);
$app->run();
