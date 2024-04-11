<?php

namespace Peroxide\DependencyInjection\Invokables;

use Peroxide\DependencyInjection\Exceptions\NotFoundException;
use Peroxide\DependencyInjection\Exceptions\NotInvokableClassException;
use Peroxide\DependencyInjection\Interfaces\ContainerFactory;
use Psr\Container\ContainerInterface;

use Closure;

class Singleton implements ContainerFactory
{
    /**
     * @var array<string, object>
     */
    protected array $singletonObjects = [];

    /**
     * @var Closure|string $factoryAction
     */
    protected Closure|string $factoryAction;

    public function __construct(
        callable|string $factory,
        protected string $dependencyId = ''
    ) {
        $this->dependencyId = uniqid();
        $this->factoryAction = $factory;
    }

    public function has(string $dependencyId): bool
    {
        return isset($this->singletonObjects[$dependencyId]);
    }

    protected function store(
        string $dependencyName,
        callable|string $factoryAction,
        ContainerInterface $container
    ): void
    {
        $callable = $this->getCallableFrom($factoryAction, $container);
        $this->singletonObjects[$dependencyName] = $callable($container);
    }

    public function __invoke(ContainerInterface $container): object
    {
        if ($this->has($this->dependencyId)) {
            return $this->singletonObjects[$this->dependencyId];
        }
        $this->store($this->dependencyId, $this->factoryAction, $container);
        return $this->singletonObjects[$this->dependencyId];
    }

    protected function getCallableFrom(callable|string $factoryAction, $container): callable
    {
        if (is_string($factoryAction)) {
            return $this->getClassFactoryInstance($factoryAction);
        }
        return $factoryAction;
    }

    protected function getClassFactoryInstance(string $factoryAction)
    {
        if (false === class_exists($factoryAction)) {
            throw new NotFoundException("Class '$factoryAction' was not found");
        }

        $instance = new $factoryAction();
        if (false === is_callable($instance)) {
            throw new NotInvokableClassException("Class '$factoryAction' is not invokable");
        }
        return $instance;
    }
}