<?php

namespace Fize\Framework\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * 容器条目未找到
 */
class ContainerNotFoundException extends ContainerException implements NotFoundExceptionInterface
{

}
