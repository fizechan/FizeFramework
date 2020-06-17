<?php


namespace fize\framework\exception;

use RuntimeException;

/**
 * 数据错误
 */
class DataException extends RuntimeException
{
    /**
     * @var array 数据
     */
    protected $data;

    /**
     * 构造
     * @param array  $data    数据
     * @param string $message 错误描述
     * @param int    $code    错误码
     */
    public function __construct(array $data, $message = "", $code = 0)
    {
        $this->data = $data;
        parent::__construct($message, $code);
    }

    /**
     * 获取数据
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
