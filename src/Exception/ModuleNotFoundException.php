<?php

namespace Fize\Framework\Exception;

use Fize\Exception\NotFoundException;
use Throwable;

/**
 * 模块不存在
 */
class ModuleNotFoundException extends NotFoundException
{

    /**
     * @var string 模块
     */
    protected $module;

    /**
     * 初始化
     * @param string         $module   模块
     * @param string|null    $path     路径
     * @param string         $message  错误信息
     * @param int            $code     错误码
     * @param Throwable|null $previous 上个异常
     */
    public function __construct($module, string $path = null, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->module = $module;
        if (is_null($path)) {
            $path = $module;
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
}
