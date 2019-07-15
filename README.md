#TODO

- REWRITE DOCUMENTATION!
- REWRITE TESTS

Circular dependency Exception:
```
$ PHP Fatal error:  Uncaught Error: Maximum function nesting level of '256' reached, aborting! 
```
# earc/di

Dependency injection component of the 
[earc framework](https://github.com/Koudela/eArc-core). earc/di can also be used 
as standalone or in combination with other frameworks.

The earc dependency injection container is 
[psr-11](https://www.php-fig.org/psr/psr-11/) compatible, supports lazy 
instantiation, tree typed dependencies, container merging, factories and 
dynamic configuration.

If you need to decouple your components or want to make the dependencies of
your apps components explicit use the 
[earc/component-di container](https://github.com/Koudela/eArc-component-di).
 
## table of contents
 
 - [installation](#installation)
 - [basic usage](#basic-usage)
 - [dependency configuration](#dependency-configuration)
   - [configuration via constructor arguments](#configuration-via-constructor-arguments)
   - [configuration using an factory](#configuration-using-an-factory)
   - [configuration by direct instantiation](#configuration-by-direct-instantiation)
   - [decoration of a class](#decoration-of-a-class)
   - [using load](#using-load)
 - [exceptions](#exceptions)
 - [advanced usage](#advanced-usage)
   - [flags](#flags)
   - [tree typed dependencies](#tree-typed-dependencies)
   - [container merging](#container-merging)
   - [container merging at construction time](#container-merging-at-construction-time) 
   - [performance considerations](#performance-considerations)
   - [subset generation/decoupling dependencies](#subset-generationdecoupling-dependencies)
 - [releases](#releases)
   - [release v1.0](#release-v10-beta---develop-branch)
   - [release v0.1](#release-v01)

## installation

You can install the earc dependency injection container without the eArc
framework via composer.

```
$ composer install earc/di
```

## basic usage

A new dependency container instance can be constructed with no arguments.

```php
use eArc\DI\DependencyContainer;

$dc = new DependencyContainer();
```

Container items can be classes or parameter. They are accessed via their name.

```php
$dc->has($itemName);
```

Checks for existence of a container item.

```php
$dc->get($itemName);
```

Retrieves the parameter or the object. If the item is a object `get()` behaves
like calling a singleton, it always returns the same instance.

```php
$dc->make($itemName);
```

Retrieves a **_new_** **object** e.g. `make()` behaves like calling `new` for
the class, it returns a new instance on each call.

```php
$dc->set($itemname, $parameterClosureOrConfiguration)
```

`set()` can be used to set a parameter or object dynamically. If the items name
is not a class name or the item is neither an array nor a closure it is seen 
as parameter.

Hint: You can use an object as parameter if you neither need lazy instantiation
nor `make()` for that type of object.

```php
$logger = new Monolog\Logger();

$dc->set('logger', $logger);
``` 

If you overwrite an existing item `ItemOverwriteException` is thrown. If it is 
by purpose you can catch the exception and go on as if it never happened. The 
item is set before the exception gets thrown.

Is the items name a class name and the item is an array the array is seen as
list of arguments for the class.

Is the items name a class name and the item is closure the closure is seen as 
factory for the object. 

Hint: You can use closures or objects to overwrite class name keys with the 
wrong class object. That might be useful in some testing context or if you need
to reference some objects by their extended child class in some extending app
context (in symfony this is called decoration).

## dependency configuration 

A dependency configuration of a single class consists of the fully qualified
class name as key and a build instruction.

```php
$dc->set(FooClass::class, /* build instruction goes here */);
```

There are three types of build instructions:
1. an configuration array of constructor arguments including plain parameter,
parameter names or class names
2. an inline factory/closure
3. the object itself

### configuration via constructor arguments

The usage of the configuration array is basically the same as using the `new`
operator.

```php
$obj = new yourClassName(
    'IAmOnlyAPlainString',
    200,
    ['An', 'Array', 'Of', '5', 'Strings'],
    null
);
```
is the same as 
```php
$dc->set(
    yourClassName::class,
    [
        'IAmOnlyAPlainString',
        200,
        ['An', 'Array', 'Of', '5', 'Strings'],
        null
    ]
);
$obj = $dc->get(yourClassName::class);
```

The dependency magic comes into play if classes get instantiated by other
classes. String arguments that are used as keys are interpreted as references to
a parameter or a class instance.

```php
$obj1 = new 1stClass(/* here goes the configuration */);
$parameter = 'Hello World!'
$obj2 = new 2ndClass($obj1, $parameter);
```
is the same as 
```php
$dc->set(1stClass::class, [/* here goes the configuration */]);
$dc->set(2ndClass::class, [1stClass::class, 'myParameter']);
$dc->set('myParameter', 'Hello World!');

$obj2 = $dc->get(2ndClass::class);
```

You can use the `set()` calls in any order you like.

As you might have guessed you can mix direct parameters, parameter items and
class names to your liking. 

```php
$dc->set(
    yourClassName::class, 
    [
        firstArgument::class,
        'IAmOnlyAPlainString', 
        'IAmAParameter', 
        fourthArgument::class,
        ['An', 'Array', 'Of', 'Strings'],
        null
    ]
);
```

In the case of class configuration via constructor arguments the use of the 
fully qualified class name as key is mandatory. If you want to use another key 
you can use a factory.

Hint: Since there is no way to distinct between a key and a string argument 
equal to a key you have to use the argument via parameter if the string that 
need to be passed to the constructor conflicts with an existing key.

### configuration using an factory

If you need some calculation to get the constructor arguments right you can use 
a closure as factory. The closure gets evaluated on the first `get()` and
on every `make()` call to the class.

```php
$dc->set(
    yourClass::class,
    function() {
        //...do some calculation...
        return new yourClass(/*...the calculated arguments...*/);
    } 
);
```

You can even use the dependency injection inside the factory.

```php
$dc->set(
    yourClass::class,
    function() use ($dc) {
        //...do some calculation with $dc->get(iNeedThisClass::class)->myMethod()...
        return new yourClass($dc->get(someDependency::class), /*...the calculated arguments...*/);
    } 
);
```

If you already have an factory statically attached to an class you need to wrap
it in a closure. 

```php
$dc->set(
    yourClassName::class,
    function() {
        return yourFactoryClassName::build();
    } 
);
```

Keep in mind: Factory methods that are not static need their class to be
instantiated. If you use the dependency container this happens on the first
call. In the example below if the first call to `yourClassName::class` happens
the `yourFactoryClassName::class` gets instantiated.

```php
$dc->set(
    yourClassName::class,
    function() use ($dc) {
        return $dc->get(yourFactoryClassName::class)->build();
    } 
);
```

### configuration by direct instantiation

The eArc dependency container can be used as plain container. Thus you can set
your objects the direct way. Please note that a direct instantiated object does
not benefit of lazy instantiation.   

```php
$dc->set(
    yourClass::class,
    new yourClass(...arguments go here...) 
);
```

Hint: You can store everything in the underlying plain container. If you want
to store a closure or an array and use a class name as key wrap them in a 
closure.

```php
$dc->set(
    FooReferencingAnClosure::class,
    function() {
        return function() {
            // ... the closure body ...
        }
    }
);

$dc->set(
    BarReferencingAnArray::class,
    function() {
        return [
            // ... the array contents ...
        ]
    }
);
```

### decoration of a class

The best way of decorating a class is to use a closure as proxy:

```php
$dc->set(FooAsDecorator::class, /* configuration */);
$dc->set(
    BarAsDecorated::class,
    function() use ($dc) {
        return $dc->get(FooAsDecorator::class);
    }
)
```

## using load

To set up a whole bunch of dependencies one by one is not convenient. The
`load()` method uses the array syntax to get that job done faster and cleaner.
The item keys are hereby the array keys and the array values are mapped to the 
item values.  

```php
$dc->load([
    'firstParameter' => 42,
    FirstClass::class => [SecondClass::class, SeventhClass::class, ...],
    SecondClass::class => function() {
        return FactoryClass::build();
    },
    ThirdClass::class => [SomeOtherClass::class],
    FourthClass::class => ['firstParameter', 'iAmNotAParameterButAString'],
    FifthClass::class => ['I', 'have', 5, 'plain', 'arguments'],
    SomeOtherClass::class => [],
    'secondParameter' => 23,
    ... 
]);
```

## exceptions

 * An `ItemNotFoundException` is thrown if the item does not exists or `make()` 
 is called but there is no configuration for the class.
 
 * An `InvalidFactoryException` if you call `make()` but the items name is not
 a fully qualified class name.

 * An `InvalidObjectConfigurationException` is thrown if the class is not 
 configured properly.
 
 * An `CircularDependencyException` is thrown if the classes configuration 
 depends in some way on the class itself. If this exception is thrown in your 
 app something is wrong with your dependency container configuration.

 * An `ItemOverwriteException` is thrown if the item name is already set. If 
 it is by purpose you can catch the exception. The item is set before the
 exception gets thrown.


## advanced usage  

### flags

### tree typed dependencies

There may be times when you need the same objects instantiated differently for
different objects. You can hide the different dependencies behind a factory or
make them explicit through the use of tree typed dependencies.

Instead of only passing the `className::class` as configuration argument the 
eArc dependency container accepts the `className::class` as key pointing to a
separate configuration.

```php
$dc->load([
    A::class => ['majorConfigurationString'],
    B::class => [A::class],
    C::class => [
        A::class => ['minorConfiguration']
    ]
]);
```

Thus each class can be defined individually on a deeper level without harming
the configuration on higher levels. You get a tree of dependencies corresponding
to your nested configuration. That is a tree typed dependency configuration.   

```php
$dc->load([
    A::class => [
        B1::class => [
            C::class => ['argumentString1'],
            D::class
        ],
        B2::class => [
            ...
        ],
        D::class => ['dConfig1'],
        ...
    ],
    C::class => ['argumentString2'],
    D::class => ['dConfig2'],
    E::class => [D::class],
    ...
]);
```

The eArc dependency injection uses the configuration on the nearest level. In
the example the `B1::class` uses the `D::class` configuration wrapped in 
`A::class` whereas `E::class` uses the `D::class` configuration at the top 
level.

### container merging

Consider an third party api that does not get an dependency container injected 
but returns one. Consider you need to write code that uses several of such
third party stuff. Keeping track of several dependency container could go messy.
At this stage container merging comes into play. Creating one to rule them all
makes your live easy again.    

```php
$dcAlmighty = new DependencyContainer();
$dcAlmighty->merge($dc1);
$dcAlmighty->merge($dc2);
$dcAlmighty->merge($dc3);
$dcAlmighty->merge($dc0);
```

Since the ruling container only hold references but the referenced elements 
remain in the original container you can even merge an php-di/php-di container 
into an earc/di container without loosing the laziness of each one.

The container need to implement the psr `ContainerInterface`. This interface
only supports `has()` and `get()`. Hence if you want to use `make()` the 
container has to implement the earc `DependencyInjectionInterface`. Container
who does not support the earc `DependencyInjectionInterface` but support a 
`make()` method like php-di/php-di need a wrapper/proxy for using `make()` in 
the merged state.
 
```php
use eArc\DI\Interfaces\DependencyInjectionInterface;
use Psr\Container\ContainerInterface;

class DICWrapper implements DependencyInjectionInterface
{
    protected $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container
    }
    
    public function has()
    {
        return $this->container->has();
    }
    
    public function get()
    {
        return $this->container->get();
    }
    
    public function make()
    {
        return $this->container->makeFunctionOfContainer();
    }
}
```

```php
$foreignContainer = ForeignContainerFactory::build(/* ... configuration ... */);
$wrappedForeignContainer = new DICWrapper($foreignContainer);

$dc->merge($wrappedForeignContainer);

$fooObject = $dc->make(FooFromForeignContainer::class);
```

#### container merging at construction time

Some advanced usages (like 
[earc/components-di](https://github.com/Koudela/eArc-component-di)) need a 
mechanism to provide some object getters only at construction time of an object.
To use this container merging at construction time pass an closure to the
container as second construction argument.

```php
use eArc\DI\DependencyContainer;
use eArc\PayloadContainer\Exceptions\ItemNotFoundException;

$otherContainer = // ... configuration ...

$dc = new DependencyContainer(null, function(&$nameReturnsObjectOrParameter) use ($otherContainer) {
    if ($otherContainer->has($nameReturnsObjectOrParameter) {
        $nameReturnsObjectOrParameter = $otherContainer->get($nameReturnsObjectOrParameter);
        
        return true; 
    }
    
    return false;
});
``` 

### performance considerations

Objects retrieved by `get()` can not be garbage collected until the dependency
container is not referenced any more. If you use an object only once or for a 
tiny moment it might save a bit of memory if you use `make()` instead. (Hint:
`make()` uses `get()` for its dependencies to reduce the calculation and 
configuration overhead. There is no way to circumvent this behaviour by 
now/version 1.0.) 

Even though there is a performance gain through lazy evaluated dependencies
the configuration of the unused classes is parsed and loaded into memory by PHP.
Therefore it is a good practice to separate the configuration of the different
business domains and load them in a lazy manner too. You can dynamically call
load to achieve this or use
[earc/components-di](https://github.com/Koudela/eArc-component-di).

### subset generation/decoupling dependencies

Injecting the dependency container of the controller into the business classes 
is a bad but unfortunate popular habit. At first sight it makes life easy, but
it hides the dependencies and every programmer need to know the whole code to
keep track of dependencies. On the other hand injecting the classes itself into
the business api kills the benefits of lazy evaluation. To get the best of both
worlds the earc dependency container supports subset generation.

The aim of subset generation was given up. You can achieve something similar
using [earc/components-di](https://github.com/Koudela/eArc-component-di).

In modern agile teams maybe the biggest advantage is that no programmer can
introduce a new dependency accidentally. Thus helping to keep the architectural
design clean.

## releases

### release v1.0

* support for flags.

* support for container merging and container merging at construction time only.

* announced new feature subset generation is dropped in favour of 
[earc/components-di](https://github.com/Koudela/eArc-component-di)

* `NotFoundException` and `OverwriteException` dropped in favour of 
`ItemNotFoundException` and `ItemOverwriteException` from the 
[earc/payload-container package](https://github.com/Koudela/eArc-payload-container).

* `DependencyContainer::loadFile()` is no longer supported. You can emulate it 
by `DependencyContainer::load(include ...)`.

### release v0.1

the first official release
