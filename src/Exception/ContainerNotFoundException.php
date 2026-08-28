<?php

namespace Fize\Framework\Container;

use Psr\Container\NotFoundExceptionInterface;

/**
 * 容器条目未找到
 */
class ContainerNotFoundException extends ContainerException implements NotFoundExceptionInterface
{

}
