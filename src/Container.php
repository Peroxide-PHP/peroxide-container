<?php

declare(strict_types=1);

namespace Peroxide\DependencyInjection;

use Peroxide\DependencyInjection\Exceptions\NotFoundException;
use Peroxide\DependencyInjection\Exceptions\NotInvokableClassException;
use Peroxide\DependencyInjection\Interfaces\SetDependency;
use Psr\Container\ContainerInterface;

final class Container implements ContainerInterface, SetDependency
{
    /**
     * @param array<string, string|callable> $dependencies
     * @throws NotInvokableClassException
     */
    public function __construct(
        protected array $dependencies = []
    ) {
        foreach ($dependencies as $id => $dependency) {
            if (is_object($dependency)) {
                $this->set($id, $dependency);
                continue;
            }
            $this->setInvokableClass($id, $dependency);
        }
    }

    public function setInvokableClass(string $id, string $invocableClass): void
    {
        if (false === class_exists($invocableClass)) {
            throw new NotFoundException("Class '$id' isn't in project autoload");
        }

        $invocableObject = new $invocableClass($this);
        if (true === is_callable($invocableObject)) {
            $this->dependencies[$id] = $invocableObject;
            return;
        }

        throw new NotInvokableClassException("Class '$id' has not a '__invoke' method.");
    }

    public function set(string $id, callable $factory): void
    {
        $this->dependencies[$id] = $factory;
    }

    public function get(string $id): object
    {
        if (true === $this->has($id)) {
            return $this->dependencies[$id]($this);
        }
        // throw error
        throw new NotFoundException("Dependency: $id not found");
    }

    public function has(string $id): bool
    {
        return isset($this->dependencies[$id]);
    }
}
