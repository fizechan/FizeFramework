<?php

return [
    'rules' => [
        'news/<id:\d+>' => 'index/news/details?id=<id>',
        'event/<year:\d+>/<month:\d+>/<day:\d+>/?[.]*' => 'index/event/detail?year=<year>&month=<month>&day=<day>',
    ]
];