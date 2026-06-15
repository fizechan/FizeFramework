<?php


namespace Fize\Framework\Exception;

use Fize\Exception\NotFoundException;
use Throwable;

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
     * @param string         $module     模块
     * @param string         $controller 控制器
     * @param string         $action     操作
     * @param string|null    $path       路径
     * @param string         $message    错误信息
     * @param int            $code       错误码
     * @param Throwable|null $previous   上个异常
     */
    public function __construct($module, $controller, $action, string $path = null, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->module = $module;
        $this->controller = $controller;
        $this->action = $action;
        if (is_null($path)) {
            $path = "{$module}/{$controller}->{$action}";
        }
        parent::__construct($path, $message, $code, $previous);
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

    /**
     * 取得操作名
     * @return string
     */
    public function action(): string
    {
        return $this->action;
    }
}
