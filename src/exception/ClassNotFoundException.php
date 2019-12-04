<?php


namespace fize\framework\exception;


/**
 * 类不存在
 */
class ClassNotFoundException extends NotFoundException
{

    /**
     * @var string 类完全限定名
     */
    protected $class;

    /**
     * 初始化
     * @param string $class 类完全限定名
     */
    public function __construct($class)
    {
        $this->class = $class;
        parent::__construct('ClassNotFoundException', 404);
    }

    /**
     * 获取类完全限定名
     * @return string
     */
    public function class()
    {
        return $this->class;
    }
}