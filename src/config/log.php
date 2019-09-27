<?php

use fize\framework\App;

return [
    'handler' => 'File',
    'config'  => [
        'path' => App::runtimePath() . '/log'
    ]
];