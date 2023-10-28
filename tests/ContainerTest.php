<?php

use Tests\TestDependencies\{ 
    Dependency, 
    ConcreteClass, 
    ConcreteClassFactory,
    ParentDependency
};

use Peroxide\DependencyInjection\Container;
use Peroxide\DependencyInjection\Exceptions\NotFoundException;
use Peroxide\DependencyInjection\Exceptions\NotInvokableClassException;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Container\ContainerInterface;

use Peroxide\DependencyInjection\Invokables\Singleton;

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
            ConcreteClass::class  => ConcreteClassFactory::class,
            ConcreteClass2::class => new ConcreteClassFactory(),
            ConcreteClass3::class => fn() => new ConcreteClass()
        ]);

        $concreteClass = $container->get(ConcreteClass::class);
        $this->assertInstanceOf(ConcreteClass::class, $concreteClass);

        $concreteClass2 = $container->get(ConcreteClass2::class);
        $this->assertInstanceOf(ConcreteClass::class, $concreteClass2);

        $concreteClass3 = $container->get(ConcreteClass3::class);
        $this->assertInstanceOf(ConcreteClass::class, $concreteClass3);
    }

    public function testContainerShouldThrowTypeError()
    {
        $this->expectException(TypeError::class);

        $container = new Container([
            ConcreteClass3::class => new ConcreteClass(),
        ]);
    }

    public function testContainerShouldThrowNotInvokableClass()
    {
        $this->expectException(NotInvokableClassException::class);

        $container = new Container([
            ConcreteClass3::class => ConcreteClass::class,
        ]);
    }

    public function testContainerShouldThrowClassNotInProject()
    {
        $this->expectException(NotFoundException::class);

        $container = new Container([
            ConcreteClass3::class => InexistentClass::class,
        ]);
    }

    public function testContainerShouldInjectInnerDependencies()
    {
        $container = new Container([
            // Dependency parent with dependency child
            Dependency::class       => fn()   => new Dependency(),
            ParentDependency::class => fn($c) => new ParentDependency($c->get(Dependency::class))
        ]);

        $dependencyParent = $container->get(ParentDependency::class);
        $dependency = $dependencyParent->getInnerDependency();

        $this->assertInstanceOf(Dependency::class, $dependency);
    }

    public function testContainerShouldReturnSingletonObject()
    {
        $container = new Container([
            // Dependency parent with dependency child
            Dependency::class       => new Singleton(fn() => new Dependency()),
            ParentDependency::class => new Singleton(
                fn($container) => new ParentDependency($container->get(Dependency::class))
            )
        ]);

        $dependency1 = $container->get(Dependency::class);
        $dependency2 = $container->get(Dependency::class);

        $this->assertSame($dependency1, $dependency2);

        $containeredDependency1 = $container->get(ParentDependency::class);
        $containeredDependency2 = $container->get(ParentDependency::class);

        $this->assertSame($containeredDependency1, $containeredDependency2);
    }

    public function testContainerShouldReturnSingletonInsideFactoryCallable()
    {
        $container = new Container([
            // Dependency parent with dependency child
            Dependency::class        => new Singleton(fn() => new Dependency()),
            AnotherDependency::class => function($container) {
                $dep1 = $container->get(Dependency::class);
                $dep2 = $container->get(Dependency::class);
                $this->assertSame($dep1, $dep2);
                return $dep2;
            }
        ]);

        $container->get(AnotherDependency::class);
    }

    public function testContainerShouldReturnSingletonStateChange()
    {
        $container = new Container([
            // Dependency parent with dependency child
            Dependency::class        => new Singleton(fn() => new Dependency()),
            AnotherDependency::class => function($container) {
                $dep1 = $container->get(Dependency::class);
                $dep1->testProp = 123;
                return $dep1;
            }
        ]);

        $container->get(Dependency::class);

        $changedStateDependency = $container->get(AnotherDependency::class);

        $dependency = $container->get(Dependency::class);

        $this->assertEquals(123, $dependency->testProp);
    }
}
