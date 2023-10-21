<?php

namespace Tests\TestDependencies;

use Peroxide\DependencyInjection\Interfaces\ContainerFactory;
use Psr\Container\ContainerInterface;

class ConcreteClassFactory implements ContainerFactory
{
    public function __invoke(ContainerInterface $container): ConcreteClass
    {
        return new ConcreteClass();
    }
}