<?php

namespace Peroxide\DependencyInjection\Invokables;

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
     * @var Closure $factoryAction
     */
    protected Closure $factoryAction;

    public function __construct(
        callable $factory,
        protected string $dependencyId = ''
    ) {
        $this->dependencyId = uniqid();
        $this->factoryAction = $factory;
    }

    public function has(string $dependencyId): bool
    {
        return isset($this->singletonObjects[$dependencyId]);
    }

    protected function store(string $dependencyName, callable $factoryAction, ContainerInterface $container): void
    {
        $this->singletonObjects[$dependencyName] = $factoryAction($container);
    }

    public function __invoke(ContainerInterface $container): object
    {
        if ($this->has($this->dependencyId)) {
            return $this->singletonObjects[$this->dependencyId];
        }
        $this->store($this->dependencyId, $this->factoryAction, $container);
        return $this->singletonObjects[$this->dependencyId];
    }
}