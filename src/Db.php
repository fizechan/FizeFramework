<?php


namespace fize\framework;

use fize\db\Db as FizeDb;

/**
 * 数据库
 * @package fize\framework
 */
class Db extends FizeDb
{

    /**
     * 构造为静态方法做准备
     * @param string $type 类型
     * @param string $mode 模式
     * @param array $config 配置
     */
    public function __construct($type, $mode, array $config)
    {
        $options = [
            'type'   => $type,
            'mode'   => $mode,
            'option' => $config
        ];
        self::init($options);
    }
}