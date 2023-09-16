<?php

require __DIR__ . '/TestDependencies/Dependency.php';
require __DIR__ . '/TestDependencies/ConcreteClass.php';
require __DIR__ . '/TestDependencies/ConcreteClassFactory.php';

use Peroxide\DependencyInjection\Container;
use PHPUnit\Framework\Attributes\CoversClass;
use Peroxide\DependencyInjection\NotFoundException;
use Psr\Container\ContainerInterface;

#[CoversClass(Container::class)]
class ContainerTest extends PHPUnit\Framework\TestCase
{
    public function testContainerShouldSetDependencyAndGetDependency()
    {
        $container = new Container();
        $container->set(Dependency::class, function (ContainerInterface $container) {
            return new Dependency();
        });

        $this->assertInstanceOf(Dependency::class, $container->get(Dependency::class));
    }

    public function testContainerShouldThrowNotFoundExceptionDependencyAndGetDependency()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Dependency: InexistentDependency not found');
        $container = new Container();

        $container->get(InexistentDependency::class);
    }

    public function testContainerShouldInvokeDependencieFromInvokeMethodClass()
    {
        $container = new Container();

        $container->set(ConcreteClass::class, new ConcreteClassFactory());

        $concreteClass = $container->get(ConcreteClass::class);
        $this->assertInstanceOf(ConcreteClass::class, $concreteClass);
    }

    public function testContainerShouldConfigDependenciesByConfigConstructorParameter()
    {
        $container = new Container([
            ConcreteClass::class => ConcreteClassFactory::class,
            ConcreteClass2::class => new ConcreteClassFactory(),
            ConcreteClass3::class => new ConcreteClass(),
        ]);

        $concreteClass = $container->get(ConcreteClass::class);
        $this->assertInstanceOf(ConcreteClass::class, $concreteClass);
    }
}
