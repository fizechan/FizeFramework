<?php

return [
    'handler' => 'Twig',
    'config'  => [
        'view'  => '%module_path%/%app_view_dir%',
        'cache' => '%runtime_path%/view',
        'debug' => true
    ]
];
