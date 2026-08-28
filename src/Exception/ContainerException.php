<?php

namespace Fize\Framework\Container;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * 容器异常
 */
class ContainerException extends RuntimeException implements ContainerExceptionInterface
{

}
