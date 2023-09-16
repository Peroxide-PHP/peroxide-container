<?php

namespace Peroxide\DependencyInjection;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface
{ }