<?php

namespace fize\framework\exception;

/**
 * 参数未设置
 */
class ParameterNotSetException extends NotSetException
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
     * @var string 参数名
     */
    protected $parameter;

    /**
     * 初始化
     * @param string $class     类完全限定名
     * @param string $method    方法名
     * @param string $parameter 参数名
     */
    public function __construct($class, $method, $parameter)
    {
        $this->class = $class;
        $this->method = $method;
        $this->parameter = $parameter;
        parent::__construct("ParameterNotSet: `{$parameter}` for class `{$class}` in method `{$method}`", 400);
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

    /**
     * 获取参数名
     * @return string
     */
    public function parameter()
    {
        return $this->parameter;
    }
}
