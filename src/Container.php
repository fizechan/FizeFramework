<?php

namespace Fize\Framework;

use Fize\Framework\Exception\ContainerException;
use Fize\Framework\Exception\ContainerNotFoundException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

/**
 * 容器
 */
class Container implements ContainerInterface
{

    /**
     * @var array 已注册的共享条目
     */
    protected $entries = [];

    /**
     * 注册共享实例
     * @param string $id    条目标识
     * @param mixed  $entry 实例
     */
    public function set(string $id, $entry)
    {
        $this->entries[$id] = $entry;
    }

    /**
     * @param string $id 条目标识
     * @return mixed
     * @throws ContainerNotFoundException
     */
    public function get(string $id)
    {
        if (!$this->has($id)) {
            throw new ContainerNotFoundException("Container entry [{$id}] not found");
        }
        return $this->entries[$id];
    }

    /**
     * @param string $id 条目标识
     * @return bool
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->entries);
    }

    /**
     * 按构造函数类型提示装配，结果不缓存
     * @param string $class 类名
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function make(string $class)
    {
        if (!class_exists($class)) {
            throw new ContainerException("Class [{$class}] not found");
        }

        $ref = new ReflectionClass($class);
        if (!$ref->isInstantiable()) {
            throw new ContainerException("Class [{$class}] is not instantiable");
        }

        $constructor = $ref->getConstructor();
        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return new $class();
        }

        $args = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type && method_exists($type, 'isBuiltin') && !$type->isBuiltin()) {
                $type_name = $type->getName();
                if ($this->has($type_name)) {
                    $args[] = $this->get($type_name);
                    continue;
                }
                if ($parameter->isOptional()) {
                    $args[] = $parameter->getDefaultValue();
                    continue;
                }
                if (method_exists($parameter, 'allowsNull') && $parameter->allowsNull()) {
                    $args[] = null;
                    continue;
                }
                throw new ContainerException("Unable to resolve [{$type_name}] for {$class}");
            }
            if ($parameter->isOptional()) {
                $args[] = $parameter->getDefaultValue();
                continue;
            }
            throw new ContainerException("Unable to resolve parameter [\${$parameter->getName()}] for {$class}");
        }

        return $ref->newInstanceArgs($args);
    }
}
