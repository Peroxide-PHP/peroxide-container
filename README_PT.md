[eng](README.md) / [pt-br](README_PT.md)
# Peroxide/Container

Um contêiner de Injeção de Dependência direto, projetado para ser usado com APIs, aderindo ao padrão PSR-11. Ele oferece funcionalidade mínima e opera de forma independente, sem depender de recursos externos.
## Nossa filosofia
Somos apaixonados por trabalhar com componentes o mais limpos e simples possível. **Peroxide/Container** é uma fusão de inspiração proveniente de bibliotecas como *Laminas\ServiceManager*, *Pimple*, e um toque de *PHP-DI*.

A grande vantagem é que não temos dependências externas. Toda a configuração é feita por meio de código PHP usando arquivos de configuração em forma de arrays. Tudo o que você precisa fazer é garantir que seu Framework suporte a PSR-11, configure a biblioteca e você estará pronto para começar sua jornada de codificação.

## Como usar
### Instalando
```bash
composer require peroxide/container
```
---

## Iniciando sua jornada
**Peroxide\Container** está totalmente em conformidade com a PSR-11 e oferece os seguintes métodos:

```php
# From PSR-11
public function get(string $id): object;
public function has(string $id): bool;

# From our interface SetDependency
public function set(string $id, callable $factory): void;
public function setInvokableClass(string $id, string $invocableClass): void;
```

### Crie sua configuração com arrays
```php
<?php
use Peroxide\DependencyInjection\Container;

$config = [
    YourDependencyName::class => fn() => new YourDependencyName(),
    YourDependency::class     => YourDependencyFactoryClass::class,
    
    // deve ser uma classe invocável
    ConcreteClass::class      => new ConcreteClassFactory(),
    
    // Ou passe como uma referencia em string seu factory
    ConcreteClass::class      => ConcreteClassFactory::class
];

$container = new Container($config);

// como resgatar sua dependência pronta
$container->get(YourDependencyName::class);
$container->get(YourDependency::class);
$container->get(ConcreteClass::class);
```
### Criando sua classe Factory
```php
use Psr\Container\ContainerInterface;
use Peroxide\DependencyInjection\Interfaces\ContainerFactory;

class ConcreteClassFactory implements ContainerFactory
{
    public function __invoke(ContainerInterface $container): ConcreteClass
    {
        // configure sua injeção de dependência aqui
        // você pode compor sua dependência
        // retorne new ParentDependency($container->get(DependencyChild::class));
        return new ConcreteClass();
    }
}
```
Também é possível definir dependências separadamente, após obter a instância do seu contêiner:
```php
use Peroxide\DependencyInjection\Container;

$container = new Container();

$container->set(DependencyPath::class, fn() => new DependencyInstance());
```

Se a dependência não existir, ela será criada; caso contrário, será substituída pela atual definição.
## Mais configurações
Para lidar com injeção de dependência dentro do contêiner, você pode facilmente usar uma 
```arrow function``` para compor suas dependências.
```php
$container = new Container([
    // todas as dependências devem ser envolvidas por uma Closure (função ou fn())
    Dependency::class       => fn() => new Dependency(),
    
    
    ComponentThatHasAnotherDependency::class => function($container) { 
        return new ComponentThatHasAnotherDependency(
            $container->get(Dependency::class)
        );
    },

    // ou simplesmente
    ComponentThatHasAnotherDependency::class => fn($c) => 
        new ComponentThatHasAnotherDependency($c->get(Dependency::class)),

    // uma injeção mais complexa
    ComponentThatHasTwoDeps::class => fn($c) => new ComponentThatHasTwoDeps(
        $c->get(Dependency::class),
        $c->get(AnotherDependency::class),
    )
]);
```
Você também pode compor sua configuração usando o operador de expansão, como mostrado no exemplo:
```php
use Peroxide\DependencyInjection\Container;
# no arquivo de configuração 'dependencies.php'
$config1 = [ ... ];
$config2 = [ ... ];
return [...$config1, ...$config2];

// -------------------
# em index.php
$config = require __DIR__ . '/dependencies.php';

$container = new Container($config);
```
## Como lidar com Singleton?
Basta usar a classe ```Singleton```, aqui está um exemplo:
```php
use Peroxide\DependencyInjection\Container;
use Peroxide\DependencyInjection\Invokables\Singleton;

$container = new Container([
    // Dependência pai com filha.
    Dependency::class       => new Singleton(fn() => new Dependency()),
    
    ParentDependency::class => new Singleton(
        fn($container) => new ParentDependency($container->get(Dependency::class))
    ),
    
    // Singleton passando string como referencia de classe factory
    ConcreteClass::class    => new Singleton(ConcreteClassFactory::class)
]);
```
A classe ```Peroxide\DependencyInjection\Invokables\Singleton``` atua como um invólucro 
para indicar ao nosso contêiner que desejamos que esta classe não crie uma nova instância 
toda vez que for solicitada.

## Por que não posso configurar parâmetros no contêiner?
Acreditamos que não é necessário armazenar valores de configuração no contêiner de dependência.
Em vez disso, cada serviço deve ser configurado usando dados de ambiente externos (por exemplo .env).
Fazendo isso, você centraliza a configuração do seu projeto.