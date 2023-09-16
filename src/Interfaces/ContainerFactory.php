<?php

namespace Peroxide\DependencyInjection\Interfaces;

use Psr\Container\ContainerInterface;

interface ContainerFactory
{
    public function __invoke(ContainerInterface $container);
}