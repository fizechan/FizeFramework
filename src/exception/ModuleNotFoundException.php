<?php


namespace fize\framework\exception;


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
     * @param string $module 模块
     */
    public function __construct($module)
    {
        $this->module = $module;
        parent::__construct('ModuleNotFoundException', 404);
    }

    /**
     * 获取模块名
     * @return string
     */
    public function module()
    {
        return $this->module;
    }
}