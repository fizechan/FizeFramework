<?php

namespace fize\framework;

require __DIR__ . '/../vendor/autoload.php';

$env = [
    'root_path' => dirname(dirname(__FILE__))
];

new App($env);