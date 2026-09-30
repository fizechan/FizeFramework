<?php

namespace Fize\Framework\Controller;

use fize\think\handle\ControllerHandle;
use think\App;

/**
 * 简易控制器
 */
abstract class SimpleController
{

    /**
     * 应用实例
     * @var App
     */
    protected $app;

    /**
     * @var ControllerHandle
     */
    protected $handle;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->handle = new ControllerHandle($app, $this->batchValidate);
    }
}