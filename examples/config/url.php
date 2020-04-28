<?php

/**
 * 路由使用正则命名捕获组实现
 */
return [
    'rules' => [
        'news/title110' => 'index/news/details?id=110',  //完整匹配
        'news/(?<id>\d+)' => 'index/news/details',  //常规匹配
        'event/(?<year>\d+)/(?<month>\d+)/(?<day>\d+)/(?<title>\S*)' => 'index/event/detail',  //title捕获组在未赋值时该捕获组将制空
        'event2/(?<year>\d+)/(?<month>\d+)/(?<day>\d+)(?:/?\S*)' => 'index/event2/detail',  //含非捕获组
        'mp/'
    ]
];
