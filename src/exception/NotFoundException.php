<?php

namespace fize\framework\exception;

use RuntimeException;
use fize\web\Request;

/**
 * 404 错误
 */
class NotFoundException extends RuntimeException
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
