<?php

namespace Tests;

use Fize\Framework\Container\Container;
use Fize\Framework\Container\ContainerException;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

class ContainerDep
{
}

class ContainerSubject
{
    public $dep;

    public function __construct(ContainerDep $dep)
    {
        $this->dep = $dep;
    }
}

class TestContainer extends TestCase
{
    public function testSetGetHas()
    {
        $container = new Container();
        $entry = new stdClass();
        $container->set(stdClass::class, $entry);

        self::assertTrue($container->has(stdClass::class));
        self::assertFalse($container->has('missing'));
        self::assertSame($entry, $container->get(stdClass::class));
        self::assertSame($entry, $container->get(stdClass::class));
    }

    public function testGetUnknownThrows()
    {
        $container = new Container();
        $this->expectException(NotFoundExceptionInterface::class);
        $container->get('missing');
    }

    public function testMakeAutowireAndNotCached()
    {
        $container = new Container();
        $dep = new ContainerDep();
        $container->set(ContainerDep::class, $dep);

        $first = $container->make(ContainerSubject::class);
        $second = $container->make(ContainerSubject::class);

        self::assertInstanceOf(ContainerSubject::class, $first);
        self::assertSame($dep, $first->dep);
        self::assertNotSame($first, $second);
        self::assertSame($dep, $second->dep);
    }

    public function testMakeUnresolvedThrows()
    {
        $container = new Container();
        $this->expectException(ContainerException::class);
        $container->make(ContainerSubject::class);
    }
}
