<?php

namespace Fize\Framework\Exception;

use Fize\Exception\NotFoundException;

/**
 * 控制器不存在
 */
class ControllerNotFoundException extends NotFoundException
{
    /**
     * @var string 模块
     */
    protected $module;

    /**
     * @var string 控制器
     */
    protected $controller;

    /**
     * 初始化
     * @param string $module     模块
     * @param string $controller 控制器
     */
    public function __construct($module, $controller)
    {
        $this->module = $module;
        $this->controller = $controller;
        parent::__construct('ControllerNotFoundException', 404);
    }

    /**
     * 获取模块名
     * @return string
     */
    public function module(): string
    {
        return $this->module;
    }

    /**
     * 取得控制器名
     * @return string
     */
    public function controller(): string
    {
        return $this->controller;
    }
}
