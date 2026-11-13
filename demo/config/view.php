<?php

return [
    'handler' => 'Twig',
    'config'  => [
        'view'  => FIZE_APP_PATH . '/Index/View',  // @todo 模块定位问题。
        'cache' => FIZE_RUNTIME_PATH . '/view',
        'debug' => true
    ]
];