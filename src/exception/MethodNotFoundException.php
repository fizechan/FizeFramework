<?php


namespace fize\framework\exception;


/**
 * 方法不存在
 */
class MethodNotFoundException extends NotFoundException
{
    /**
     * @var string 类完全限定名
     */
    protected $class;

    /**
     * @var string 方法名
     */
    protected $method;

    /**
     * 初始化
     * @param string $class 类完全限定名
     * @param string 方法名
     */
    public function __construct($class, $method)
    {
        $this->class = $class;
        $this->method = $method;
        parent::__construct('MethodNotFoundException', 404);
    }

    /**
     * 获取类完全限定名
     * @return string
     */
    public function class()
    {
        return $this->class;
    }

    /**
     * 获取方法名
     * @return string
     */
    public function method()
    {
        return $this->method;
    }
}