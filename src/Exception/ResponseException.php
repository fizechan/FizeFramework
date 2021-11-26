<?php

namespace Fize\Framework\Exception;

use RuntimeException;
use Fize\Web\Response;

/**
 * HTTP响应
 */
class ResponseException extends RuntimeException
{
    /**
     * @var Response 响应对象
     */
    protected $response;

    /**
     * 初始化时设置响应对象
     * @param Response $response 响应对象
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
        parent::__construct('ResponseException', $response->getStatusCode());
    }

    /**
     * 获取响应对象
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
