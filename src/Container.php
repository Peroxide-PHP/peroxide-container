<?php
declare(strict_types=1);

namespace Peroxide\DependencyInjection;

use Peroxide\DependencyInjection\Exceptions\NotInvokableClassException;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Container\ContainerInterface;
use Peroxide\DependencyInjection\Interfaces\SetDependency;

final class Container implements ContainerInterface, SetDependency
{
    public function __construct(
        /**
         * @var <string, object|string>array $dependencies
         */
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
        $invocableObject = new $invocableClass($this);
        if (true === is_callable($invocableObject)) {
            $this->dependencies[$id] = new $invocableClass($this);
            return;
        }
        throw new NotInvokableClassException("Class '$id' has not a '__invoke' method.");
    }

    public function set(string $id, object $factory): void
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