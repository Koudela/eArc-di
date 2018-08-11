# eArc di

Dependency injection component of the [eArc framework](https://github.com/Koudela/eArc-core).

The eArc dependency injection container is [psr-11](https://www.php-fig.org/psr/psr-11/)
compatible, supports lazy instantiation, tree typed dependencies, subset 
generation, container merging, inline factories and dynamic configuration.
 
 ## Table of Contents
 
 - [Installation](#installation)
 - [Basic Usage](#basic-usage)
   - [Accessing instances](#accessing-instances)
   - [Dependency Configuration](#dependency-configuration)
     - [Configuration via constructor arguments](#configuration-via-constructor-arguments)
     - [Configuration using an inline factory](#configuration-using-an-inline-factory)
     - [Configuration by direct instantiation](#configuration-by-direct-instantiation)
   - [Using load](#using-load)
   - [Loading configuration from file](#loading-configuration-from-file)
   - [Overwriting existing dependencies](#overwriting-existing-dependencies)
 - [Advanced Usage](#advanced-usage)
   - [Tree typed dependencies](#tree-typed-dependencies)
   - [Performance considerations](#performance-considerations)
   - [Subset generation](#subset-generation)
   - [Container merging](#container-merging)
 - [Releases](#releases)
   - [release v0.2 (not released yet)](#release-v02-not-released-yet)
   - [release v0.1](#release-v01)

## Installation

If you want to use the eArc dependency injection container without the eArc
framework, you can install the component via composer.

```
$ composer install earc/di
```

Hint: If you want to install the eArc framework use the
[earc/minimal package](https://github.com/Koudela/eArc-minimal).

## Basic Usage

A new dependency container instance is always constructed with no arguments.

```php
use eArc\di\DependencyContainer;

$dc = new DependencyContainer();
```

### Accessing instances

The class instances are accessed via the `get()` or `make()` method.
`get()` always returns the same instance whereas `make()` returns a new instance
on each call.

```php
$objBehavingLikeASingleton = $dc->get('classKeyString');
$objBehavingLikeANormalNewClass = $dc->make('classKeyString');
```

The parameter is the key string under which the instance is accessed. It is the 
same key the configuration used. In most cases it is recommended to use 
`className::class`.

```php
$objBehavingLikeASingleton = $dc->get(className::class);
$objBehavingLikeANormalNewClass = $dc->make(className::class);
```

### Dependency Configuration 

A dependency configuration of a single class consists of a key and a build
instruction.  

```php
$dc->set('classKeyString', /* build instruction goes here */);
```

There are three types of build instructions:
1. an configuration array of constructor arguments possibly including key 
   names/class names
2. an inline factory/closure
3. the object itself

#### Configuration via constructor arguments

The usage of the configuration array is basically the same as using the `new`
operator.

```php
$obj = new yourClassName(
    'IAmOnlyAPlainString',
    200,
    ['An', 'Array', 'Of', '5', 'Strings'],
    null
);

// is the same as 

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
a class instance.

```php
$obj1 = new 1stClass(/* here goes the configuration */);

$obj2 = new 2ndClass($obj1);

// is the same as 

$dc->set(1stClass::class, [/* here goes the configuration */]);

$dc->set(2ndClass::class, [1stClass::class]);

$obj2 = $dc->get(2ndClass::class);
```

As you might have guessed you can mix normal configuration and class names to
your liking. 

```php
$dc->set(
    yourClassName::class, 
    [
        firstArgument::class,
        'IAmOnlyAPlainString', 
        100, 
        fourthArgument::class,
        ['An', 'Array', 'Of', 'Strings'],
        null
    ]
);
```

In the case of dependency configuration via constructor arguments the use of the 
PHP class name as key is mandatory. If you want to use another key you can use a
factory.

Since there is no way to distinct between a key and a string argument equal to 
a key, you have to use a factory if you have arguments that exists as keys but
need to be passed as strings to the constructor.

#### Configuration using an inline factory

If you need some calculation to get the constructor arguments right you can use 
a closure as factory. The closure gets evaluated on the first call to the class.

```php
$dc->set(
    yourClassName::class,
    function() {
        //...do some calculation...
        return new yourClassName(/*...the calculated arguments...*/);
    } 
);
```

You can even use the dependency injection inside the factory.

```php
$dc->set(
    yourClassName::class,
    function() use ($dc) {
        //...do some calculation with $dc->get(iNeedThisClass::class)->myMethod()...
        return new yourClassName($dc->get(someDependency::class), /*...the calculated arguments...*/);
    } 
);
```

If you already have an factory statically attached to an class you need to wrap
it in a closure. 

```php
$dc->set(
    yourClassName::class,
    function() {
        return yourFactoryClassName::factory();
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
        return $dc->get(yourFactoryClassName::class)->factory();
    } 
);
```

#### Configuration by direct instantiation

The eArc dependency container can be used as plain container. Thus you can set
your objects the direct way. Please note that a direct instantiated object does
not benefit of lazy instantiation.   

```php
$dc->set(
    yourClassName::class,
    new yourClassName(...arguments go here...) 
);
```

Note: You can store everything except closures and arrays in the underlying
plain container. Closures gets interpreted as inline factories and arrays will
be read as configuration arrays.

### Using load

To set up a whole bunch of dependencies one by one is not convenient. The
`load()` method uses the array syntax to get that job done faster and cleaner.
The container keys are hereby the array keys mapping to the configuration array.  

```php
$dc->load([
    firstClassName::class => [secondClassName::class, seventhClassName::class, ...],
    secondClassName::class => function() {
        return FactoryClassName::factory();
    },
    thirdClassName::class => [someOtherClass::class],
    fourthClassName::class => [],
    fifthClassName::class => ['I', 'have', 5, 'plain', 'arguments'],
    ... 
]);
```

### Loading configuration from file

To load the configuration from file simply save the configuration array to a
file and reference it via the `loadFile()` method.   

```php
$dc->loadFile('/absolute/path/to/some/file.conf');
```

The file need to return the configuration array.

```php
<?php

// you may do some calculation here

return [
    firstClassName::class => [secondClassName::class, seventhClassName::class],
    secondClassName::class => function() {
        return FactoryClassName::factory();
    },
    thirdClassName::class => [someOtherClass::class],
    fourthClassName::class => [],
    fifthClassName::class => ['I', 'have', 5, 'plain', 'arguments']
];
```

### Overwriting existing dependencies

If you set an already existing dependency key the new dependency overwrites the
existing one and an `E_USER_WARNING` is triggered. 

## Advanced Usage  

### Tree typed dependencies

There may be times when you need the same objects instantiated differently for
different objects. You can hide the different dependencies behind a factory or
make them explicit through the use of tree typed dependencies.

Instead of only passing the `className::class` as configuration argument the 
eArc dependency container accepts the `className::class` as key pointing to a
separate configuration.

```php
$dc->load([
    A::class => ['majorConfigurationString'],
    B::class => [A:class],
    C::class => [
        A:class => ['minorConfiguration']
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

### Performance considerations

Even though there is a performance gain through lazy evaluated dependencies
the configuration of the unused classes is parsed and loaded into memory by PHP.
Therefore it is a good practice to separate the configuration of the different
business domains and load them in a lazy manner too. You need a clear
architecture to do this properly. The eArc framework supports you in this
strive.

### Subset generation

Injecting the dependency container of the controller into the business classes 
is a bad but unfortunate popular habit. At first sight it makes life easy, but
it hides the dependencies and every programmer need to know the whole code to
keep track of dependencies. On the other hand injecting the classes itself into
the business api kills the benefits of lazy evaluation. To get the best of both
worlds the eArc dependency container supports subset generation.

```php
$dsc = $dc->subset(1st::class, 2nd::class, 3rd::class);
```

`$dsc` is a new dependency container and if you define new dependencies the 
original container is unaffected, but `1st::class`, `2nd::class` and 
`3rd::class` reference the configuration and the instances in the original `$dc`
container thus keeping the overhead minimal.

To check the subset condition use the `hasExact()` method.

Imagine the constructor code of an business domain api:

```php
protected $apiDC;

pubilc function __construct(\eArc\dc\DependencyContainer $dc)
{
    if (!$dc->hasExact(1st::class, 2nd::class, 3rd::class)) {
        throw new \Exception(
            'The dependency container violates the subset condition: ' . 
            1st::class . ', ' . 
            2nd::class . ', ' . 
            3rd::class
        )
    }
    $apiDC = $dc;
    $apiDC->load([
        // ...the dependency configuration only relevant to the business domain
        // itself goes here...
    ]);
}
```

On the controller side there may be something like:

```php
$api = new oneOfMyBuisinessApis($dc->subset(1st::class, 2nd::class, 3rd::class));
```

Thus reading the controller gives every programmer an idea which dependencies
get injected. On the other hand reading the business api class constructor
uncovers all dependencies of the business domain. This is as explicit as good
architecture can get.

In modern agile teams maybe the biggest advantage is that no programmer can
introduce a new dependency accidentally. Thus helping to keep the architectural
design clean.

(! Subset generation is not implemented yet.)

### Container merging

Consider an third party api that does not get an dependency container injected 
but returns one. Consider you need to write code that uses several of such
third party stuff. Keeping track of several dependency container could go messy.
At this stage container merging comes into play. Creating one to rule them all
makes your live easy again.    

```php
$dcAlmighty = new DependencyContainer(null);
$dcAlmighty->merge($dc1, false);
$dcAlmighty->merge($dc2, false);
$dcAlmighty->merge($dc3, false);
$dcAlmighty->merge($dc0, true);
```

The second parameter is the overwrite flag.

To save a few lines use the static `mergeAll()` method.

```php
$dcAlmighty = DependencyContainer::mergeAll($dc0, $dc1, $dc2, $dc3);
```

There exists no overwrite flag for the `mergeAll()` method. The keys of the 
container left hand are used in favour to the other. Thus the ordering enforce
the key overwrite behaviour. 

Since the keys of the ruling container only hold references but the referenced 
elements remain in the original container you can even merge an php-di into an
earc/di container without loosing the laziness of each one.

Keep in mind that there is no real function forwarding. Only methods using same 
function name and parameters as the eArc dependency container can be called
savely. 

(! Container merging is not implemented yet.)


## Releases

### release v0.2 (not released yet)

new features: subset generation, container merging

### release v0.1

the first official release

