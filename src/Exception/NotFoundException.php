<?php

namespace Fize\Framework\Exception;

use RuntimeException;
use Fize\Web\Request;

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
