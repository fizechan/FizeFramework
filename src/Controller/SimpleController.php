<?php

namespace Fize\Framework\Controller;

/**
 * 简易控制器
 */
abstract class SimpleController
{

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
     */
    public function __construct()
    {
        $this->handle = new ControllerHandle($this->batchValidate);
    }
}