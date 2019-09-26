<?php

namespace fize\framework;

require __DIR__ . '/../vendor/autoload.php';

$config = [
    'root_path' => dirname(dirname(__FILE__))
];

new App($config);