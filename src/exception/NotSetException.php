<?php

namespace fize\framework\exception;

use RuntimeException;
use fize\web\Request;

/**
 * 未设置错误
 */
class NotSetException extends RuntimeException
{
    /**
     * 获取访问的 URL
     * @return string
     */
    public function url()
    {
        return Request::url();
    }
}
