<?php

namespace Fize\Framework\Exception;

use RuntimeException;
use Fize\Web\Request;

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
