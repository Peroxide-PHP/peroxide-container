<?php

namespace Peroxide\DependencyInjection\Interfaces;

interface SetDependency
{
    public function set(string $id, object $factory): void;
    public function setInvokableClass(string $id, string $invocableClass): void;
}