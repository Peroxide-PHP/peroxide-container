[eng](README.md) / [pt-br](README_PT.md)
# Peroxide/Container

A straightforward Dependency Injection container, designed for use with APIs, adhering to the PSR-11 standard. It boasts minimal functionality and operates independently, free from external dependencies.

## Our filosophy
We are passionate about working with components that are as clean and simple as possible. **Peroxide\Container** is a fusion of inspiration drawn from libraries such as *Laminas\ServiceManager*, *Pimple*, and with a touch of *PHPCI*.

The great advantage is that we have no external dependencies. All configuration is achieved through PHP code using array configuration files. All you need to do is ensure that your framework supports PSR-11, set up the configuration, and you're ready to begin your coding journey.
## How to use it
### Instaling
```bash
composer require peroxide/container
```
---

## Starting your journey
**Peroxide\Container** is fully compliant with PSR-11, and it provides the following methods:

```php
# From PSR-11
public function get(string $id): object;
public function has(string $id): bool;

# From our interface SetDependency
public function set(string $id, callable $factory): void;
public function setInvokableClass(string $id, string $invocableClass): void;
```

### Create your configuration as *array*
```php
<?php
use Peroxide\DependencyInjection\Container;

$config = [
    YourDependencyName::class => fn() => new YourDependencyName(),
    YourDependency::class     => YourDependencyFactoryClass::class,
    
    // should be invokable class
    ConcreteClass::class      => new ConcreteClassFactory(),
    
    // Or passing as reference string
    ConcreteClass::class      => ConcreteClassFactory::class
];

$container = new Container($config);

// how to get dependencies
$container->get(YourDependencyName::class);
$container->get(YourDependency::class);
$container->get(ConcreteClass::class);
```
### Creating your Factory Class
```php
use Psr\Container\ContainerInterface;
use Peroxide\DependencyInjection\Interfaces\ContainerFactory;

class ConcreteClassFactory implements ContainerFactory
{
    public function __invoke(ContainerInterface $container): ConcreteClass
    {
        // config your dependency injection here
        // you can compose your dependency
        // return new ParentDependency($container->get(DependencyChild::class));
        return new ConcreteClass();
    }
}
```
It is also possible to set dependencies separately, after obtaining your container instance:
```php
use Peroxide\DependencyInjection\Container;

$container = new Container();

$container->set(DependencyPath::class, fn() => new DependencyInstance());
```

If the dependency doesn't exist, it will be created; otherwise, it will be replaced by the new factory.
## More configurations
To handle dependency injection within the container, you can easily use ```arrow function``` to compose your dependencies.
```php
$container = new Container([
    // Dependency parent with dependency child
    
    // all dependencies should be involved by a Closure(function() or fn()) 
    Dependency::class       => fn() => new Dependency(),
    
    ParentDependency::class => function($container) { 
        return new ParentDependency(
            $container->get(Dependency::class)
        );
    },

    // or simply
    ParentDependency::class => fn($c) => new ParentDependency($c->get(Dependency::class)),

    // more complex injections
    ParentDependency::class => fn($c) => new ComponentThatHasTwoDeps(
        $c->get(Dependency::class),
        $c->get(AnotherDependency::class),
    )
]);
```
You can also compose your configuration using the spread operator, as shown in the example:
```php
use Peroxide\DependencyInjection\Container;
# on 'dependencies.php' config file
$config1 = [ ... ];
$config2 = [ ... ];
return [...$config1, ...$config2];

// -------------------

$config = require __DIR__ . '/dependencies.php';

$container = new Container($config);
```
## How to deal with Singleton?
Just use the Singleton invocable class, here's an example:
```php
use Peroxide\DependencyInjection\Container;
use Peroxide\DependencyInjection\Invokables\Singleton;

$container = new Container([
    // Dependency parent with dependency child
    Dependency::class       => new Singleton(fn() => new Dependency()),
    ParentDependency::class => new Singleton(
        fn($container) => new ParentDependency($container->get(Dependency::class))
    )
]);
```
The ```Peroxide\DependencyInjection\Invokables\Singleton``` class serves as a wrapper to indicate to our container that we want this class to not create a new instance every time it is retrieved.

The first parameter of ```Singleton``` constructor, only accepts callable class or closures.

## Why can't I config parameters on container?
We believe that storing configuration values in the dependency container is unnecessary. Instead, each service should be configured using external environment data. By doing so, you can centralize your project's configuration.
