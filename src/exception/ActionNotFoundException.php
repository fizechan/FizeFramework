<?php


namespace fize\framework\exception;

/**
 * 操作不存在
 */
class ActionNotFoundException extends NotFoundException
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
     * @var string 操作
     */
    protected $action;

    /**
     * 初始化
     * @param string $module 模块
     * @param string $controller 控制器
     * @param string $action 操作
     */
    public function __construct($module, $controller, $action)
    {
        $this->module = $module;
        $this->controller = $controller;
        $this->action = $action;
        parent::__construct('ActionNotFoundException', 404);
    }

    /**
     * 获取模块名
     * @return string
     */
    public function module()
    {
        return $this->module;
    }

    /**
     * 取得控制器名
     * @return string
     */
    public function controller()
    {
        return $this->controller;
    }

    /**
     * 取得操作名
     * @return string
     */
    public function action()
    {
        return $this->action;
    }
}