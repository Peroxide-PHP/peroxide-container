<?php

namespace Tests\TestDependencies;

class ParentDependency
{
    public function __construct(
        protected Dependency $dependency
    ) {
    }

    public function getInnerDependency(): Dependency
    {
        return $this->dependency;
    }
}