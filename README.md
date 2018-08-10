# eArc di

Dependency injection component of the [eArc framework](https://github.com/Koudela/eArc-core).

The eArc dependency injection container is [psr-11](https://www.php-fig.org/psr/psr-11/)
compatible, supports lazy instantiation, tree typed dependencies, subset 
generation, container merging, inline factories and dynamic configuration.
 
## Installation

If you want to use the eArc dependency injection container without the eArc
framework, you can install the component via composer.

```
$ composer install earc/di
```

## Basic Usage

A new dependency container instance on the top level is always constructed with
a null argument.  

```php
use eArc\di\DependencyContainer;

$dc = new DependencyContainer(null);
```

The classes are accessed via the `get('classKey')` or `make('classKey')` method.
`get` always returns the same instance whereas `make` returns each time a new
instance.

There are three ways of dependency configuration.
1. Via key names (class names).
2. Using an inline factory (closure). 
3. Direct instantiation.

### Configuration via key names

The first argument references the key under which the configuration and possibly 
the instance is accessed. It is always a string. If you have only one
configuration of a class it is recommended to use `yourClassName::class`. The 
second is an array of arguments. String arguments which are used as keys are 
interpreted as references to an class instance. That is why you need to use an
inline factory if you have arguments that exists as keys but need to be passed
as strings to the constructor.  

```php
$dc->set(
    yourClassName::class, 
    [
        firstArgument::class, 
        secondArgument::class, 
        'IAmOnlyAPlainString', 
        100, 
        ['An', 'Array', 'of', 'Strings']
    ]
);
```
 
### Configuration using an inline factory

If you need some calculation to get the constructor arguments right you can use 
a closure as factory. The closure gets evaluated on the first call to the class.

```php
$dc->set(
    yourClassName::class,
    function() {
        ...do some calculation...
        return new yourClassName(...the calculated arguments...);
    } 
);
```

You can even use the dependency injection inside the factory.

```php
$dc->set(
    yourClassName::class,
    function() use ($dc) {
        ...do some calculation with $dc->get(iNeedThisClass::class)->myMethod()...
        return new yourClassName($dc->get(someDependency::class), ...the calculated arguments...);
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

### Configuration by direct instantiation

If object instantiation does not get in the way the eArc dependency container 
can be used as plain container. Thus you can set your objects in the direct way.
Please note that your object do not benefit of lazy instantiation using this.  

```php
$dc->set(
    yourClassName::class,
    new yourClassName(...arguments go here...) 
);
```

### Using load

If you need to set more than one class dependencies it is handy to use the load
syntax. It is uses an array. The container keys are the array keys mapping to 
the configuration array.  

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

To load from file you save the configuration array to a file and reference it 
via the `loadFile()` method.   

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
different objects. You can hide this different dependencies behind a factory or
make it explicit through the use of tree typed dependencies.

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

Each class can thus be defined individually on a deeper level without harming
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

It is a bad habit to inject the dependency container into the business classes.
That way the dependencies get hidden and every programmer need to know the whole
code to keep track of dependencies. On the other hand injecting the classes
itself into the business api kills the benefits of lazy evaluation. To get the
best of both worlds the eArc dependency container supports subset generation.

```php
$dsc = $dc->subset(1st::class, 2nd::class, 3rd::class);
```

`$dsc` is a new dependency container and if you `set` new dependencies the 
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
            3rd::class'
        )
    }
    $apiDC = $dc;
    $apiDC->load([
        ...the dependencies only relevant to the business domain itself go here...
    ]);
}
```

On the controller side there may be something like:

```php
$api = new oneOfMyBuisinessApis($dc->subset(1st::class, 2nd::class, 3rd::class));
```

Thus reading the controller gives every programmer an idea which dependencies
get injected. On the other hand reading the business api class constructor
uncovers all dependencies of the business domain. This is as explicit as a good
architecture can get.

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

To save a few lines use the static `mergeAll` method.

```php
$dcAlmighty = DependencyContainer::mergeAll($dc0, $dc1, $dc2, $dc3);
```

There exists no overwrite flag for the `mergeAll` method. The keys of the 
container left hand are used in favour to the other. Thus the ordering enforce
the key overwrite behaviour. 

Since the keys of the ruling container only hold references but the referenced 
elements remain in the original container you can even merge an php-di into an
earc/di container without loosing the laziness.

Keep in mind that there is no real function forwarding. Only methods using same 
function name and parameters as the eArc dependency container could be called. 

(! Container merging is not implemented yet.)