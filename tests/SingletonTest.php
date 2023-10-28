<?php

namespace Tests;

use Peroxide\DependencyInjection\Container;
use Peroxide\DependencyInjection\Invokables\Singleton;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tests\TestDependencies\Dependency;

#[CoversClass(Singleton::class)]
class SingletonTest extends TestCase
{
    public function testDependencyShouldBeSingleCall()
    {
        $singleton = new Singleton(fn() => new Dependency());
        $dependency1 = $singleton(new Container());
        $this->assertInstanceOf(Dependency::class, $dependency1);
    }

    public function testDependencyShouldBeUniqueOnSecondCallTheSameInstance()
    {
        $singleton = new Singleton(fn() => new Dependency());

        $dependency1 = $singleton(new Container());
        $dependency2 = $singleton(new Container());

        $this->assertSame($dependency1, $dependency2);
    }

    public function testDependencyShouldBeUniqueOnThreeCallsTheSameInstance()
    {
        $singleton = new Singleton(fn() => new Dependency());

        $container = new Container();

        $dependency1 = $singleton($container);
        $dependency2 = $singleton($container);
        $dependency3 = $singleton($container);

        $this->assertSame($dependency1, $dependency2);
        $this->assertSame($dependency1, $dependency3);
        $this->assertSame($dependency2, $dependency3);
    }
}