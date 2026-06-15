<?php

/**
 * 中间件配置
 *
 * global: 全局中间件，对每个请求都执行
 * route:  命名路由中间件，可在控制器中按需指定
 */
return [
    'global' => [
        // \App\Middleware\CorsMiddleware::class,
    ],
    'route' => [
        // 'auth'  => \App\Middleware\AuthMiddleware::class,
        // 'admin' => \App\Middleware\AdminMiddleware::class,
    ],
];
