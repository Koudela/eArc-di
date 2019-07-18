#TODO

- REWRITE DOCUMENTATION!
- REWRITE TESTS

# earc/di

Standalone lightweight dependency injection component of the eArc libraries.

If you need to decouple your components, restrict injection access or want to make 
your app components explicit use the [earc/component-di](https://github.com/Koudela/eArc-component-di) library
which builds on the top of earc/di.

## table of contents
 
 - [installation](#installation)
 - [pro/cons](#procons)
 - [basic usage](#basic-usage)
 - [parameters](#parameters)
 - [factories](#factories)
 - [tagging](#tagging)
 - [decoration](#decoration)
 - [mocking](#mocking)
 - [exceptions](#exceptions)
 - [advanced usage](#advanced-usage)
   - [performance considerations](#performance-considerations)
 - [releases](#releases)
   - [release v2.0](#release-v20)
   - [release v1.0](#release-v10)
   - [release v0.1](#release-v01)

## installation

Install the earc dependency injection library via composer.

```
$ composer require earc/di
```

You can even use it with [symfony](#integration-with-other-di-systems).

## pro/cons

### pro

- **no container** - instances are generated on the fly
- **no configuration overhead** - dependency information is all part of the class and
 not part of the dependency resolving process. It does not matter until autoloading
 of the class.
- **no loading overhead** - dependencies are resolved on usage.
- **no limitations on writing tests** - mocking is not limited to constructor arguments
- -> you are free to inject your dependencies where they evolve 
- -> therefore no need to use heavy overhead pre build class extending [proxies](https://github.com/Ocramius/ProxyManager)
 like the one used by symfony to achieve lazy loading on usage.
 - **use of global functions** - once initialized there is no need to inject the
  injector anywhere
- **use it everywhere** - injection works even in vanilla functions and closures.  
- **architectural optimized code** - no pre building or pre compiling needed
- **support for all standard dependency enrichment techniques** - decoration, mocking, 
 tagging
- **support for explicit programming/architecture** - a class has hold of all its 
 implementation details (apart from decoration, mocking and parameters which are by 
 their very nature foreign context driven)
 - **extendable** - integrates with [other dependency systems](#integration-with-other-di-systems).

### cons

- **dependency** on a dependency injection library
- **small overhead** - decoration, mocking and instantiation callables need some programming
logic

## basic usage

A new dependency resolver can be initialized with no arguments. Use this in your 
`index.php`, bootstrap or configuration script.

```php
use eArc\DI\DI;

DI::init();
```

After that classes and parameters can be accessed via the di functions.

```php
di_get(SomeClass::class);

di_param('some.parameter');
```

Classes must not have constructor parameters.

```php
public function construct()
{
    $this->dependency1 = di_get(DependencyOne::class);
    $this->dependency2 = di_get(DependencyTwo::class);
    $this->parameterAlpha = di_param('alpha');
    $this->parameterBeta = di_param('beta');
    $this->parameterGamma = di_param('gamma');
}
```

Method and even function injection is supported.

```php
class Example
{
    public function getResult($param)
    {
        $math = di_get(Math::class)
        return $math->calculate($param, di_param('pi'))
    }
}

function depending_on_injection($param)
{
        $math = di_get(Math::class)
        return $math->calculate($param, di_param('pi'))
}

$result = depending_on_injection(42);
``` 

There is no need for any further dependency configuration!

Of course you need to import the parameters though.

## parameters

```php
```

## factories

earc/di does not know the concept of a factory. The best practice is to inject the 
factory itself. Thus the class keeps all the information where its dependencies come 
from.  

```php
public function construct()
{
    $factory = di_get(Factory::class)
    $this->object = $factory->build();
}
```

## tagging

Maybe you solve a problem by implementing the [chain of responsibility - design pattern](https://sourcemaking.com/design_patterns/chain_of_responsibility).
Only the third party software knows which services add to this implementation. This
leaves four questions:

1. How to register to a base service without instantiating it?
2. How to tell the base service on instantiation without instantiating all handlers?  
3. Where is the best place to write the information?
4. Where is the best place to store the information?

Three answers solves earc/di for you through tagging.

The third party can store this piece of information by executing

```php
di_tag(Service002::class,'tag.name');
di_tag(Service007::class,'tag.name');
di_tag(Service014::class,'tag.name');
```

Your base class can retrieve the service classes by iterating over `di_get_tagged('tag.name')`

```php
foreach (di_get_tagged('tag.name') as $handlerName) {
    $handler = di_get($handlerName);
    if ($handler->canHandleTask($task)) {
        $result = $handler->handleTask($task);
        
        return $result;
    }
}

throw new TaskNotHandledException($task);
```

Of course the services should implement an interface and the base service should
check for it to avoid failure on name conflicts or forgotten methods. And yes,
logging handlers not implementing the interface is a good idea. But you know about
the architecture your software needs (and can afford) best.

## decoration

Maybe you write a library. You add some cool features and bugfixes every month. 
You have a job, a wife, a few kids, so your reaction time on issues and merge
requests does not perform very well. Your library is used as third party library 
in production nevertheless. After an upgrade an error in a method is detected in 
production. The programming engineers need a quick way to fix your library. They 
do not want to fork your library, because keeping a forked library up to date could 
be a big task. Wouldn't it be nice if they could just exchange the method. 

`di_decorate` does the job. Extending the class, overwriting the erroneous method
and decorating the original class is all it needs.

```php
di_decorate(Service::class, ServiceDecorator::class);

get_class(di_get(Service::class)); // equals ServiceDecorator::class
get_class(di_make(Service::class)); // equals ServiceDecorator::class
```

For debugging purpose `di_is_decorated` and `di_get_decorator` are handy functions.
But be aware that it debugs the *current* decoration, not the result of a decorator
chain.

```php
di_decorate(Service::class, ServiceDecorator::class);
di_decorate(ServiceDecorator::class, MegaDecorator::class);

di_get_decorator(Service::class); // equals ServiceDecorator::class
get_class(di_get(Service::class)); // equals MegaDecorator::class
```

To clear a decoration decorate a class by itself.

```php
di_decorate(Service::class, Service::class);

di_is_decorated(Service::class); // equals false
get_class(di_get(Service::class)); // equals Service::class
```

## mocking

In some testing libraries mocks are class objects manipulated by reflection. Thus
you can't use decoration for replacing the original class. `di_mock` is made for
this use cases. 
   
```php
$getObj = di_get(Service::class);
$makeObj = di_get(Service::class);

$mockedService = TestCase::createMock(Service::class);
di_mock(Service::class, $mockedService)

Assert::assertSame($mockedService, di_get(Service::class)); // passes 
Assert::assertSame($mockedService, di_make(Service::class)); // passes
Assert::assertSame($getObj, di_get(Service::class)); // fails 
Assert::assertSame($makeObj, di_make(Service::class)); // fails
```

You can check if an service is mocked by `di_is_mocked`.

```php
Assert::assertSame(true, di_is_mocked(ServiceClass)); // passes 
Assert::assertSame(true, di_is_mocked(AnotherService::class)); // fails
```

If you need the real service again use `di_clear_mock`.

```php
di_clear_mock(Service::class)

Assert::assertSame($mockedService, di_get(Service::class)); // fails 
Assert::assertSame($mockedService, di_make(Service::class)); // fails
Assert::assertSame($getObj, di_get(Service::class)); // passes
Assert::assertSame($makeObj, di_make(Service::class)); // fails
```

You can even clear all existing mocks.

```php
di_clear_mock();
```

Mocking is applied after decoration. But the `di_*mock` functions does not take
any decoration into account. That gives you more grip on the mocking process but
more care is also needed. Keep in mind, you have to mock the decorator not the decorated
class.

```php
di_mock(Service::class, (object) ['iAmMock' => 1]);
di_mock(ServiceDecorator::class, (object) ['iAmMock' => 2]);
di_decorate(Service::class, ServiceDecorator::class);

Assert::assertSame(true, di_is_mocked(Service::class)) // passes
Assert::assertSame(true, di_is_mocked(ServiceDecorator::class)) // passes

Assert::assertSame(1, di_get(Service::class)->iAmMock) // fails
Assert::assertSame(2, di_get(Service::class)->iAmMock) // passes
Assert::assertSame(2, di_get(ServiceDecorator::class)->iAmMock) // passes
```

## troubleshooting

earc/di has dropped circular dependency detection in favour of performance. If you
experience an error like the following in the earc/di code its cause is most likely
a circular dependency of the classes. 

```
$ PHP Fatal error:  Uncaught Error: Maximum function nesting level of '256' reached, aborting! 
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



--
...
--


--
...
--


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

## dependency configuration 

In the case of class configuration via constructor arguments the use of the 
fully qualified class name as key is mandatory. If you want to use another key 
you can use a factory.

Hint: Since there is no way to distinct between a key and a string argument 
equal to a key you have to use the argument via parameter if the string that 
need to be passed to the constructor conflicts with an existing key.





## advanced usage  

### container merging

Consider an third party api that does not get an dependency container injected 
but returns one. Consider you need to write code that uses several of such
third party stuff. Keeping track of several dependency container could go messy.
At this stage container merging comes into play. Creating one to rule them all
makes your live easy again.    


#### container merging at construction time

### performance considerations

Objects retrieved by `get()` can not be garbage collected until the dependency
container is not referenced any more. If you use an object only once or for a 
tiny moment it might save a bit of memory if you use `make()` instead. 

Even though there is a performance gain through lazy evaluated dependencies
the configuration of the unused classes is parsed and loaded into memory by PHP.
Therefore it is a good practice to separate the configuration of the different
business domains and load them in a lazy manner too. You can dynamically call
load to achieve this or use
[earc/components-di](https://github.com/Koudela/eArc-component-di).


The aim of subset generation was given up. You can achieve something similar
using [earc/components-di](https://github.com/Koudela/eArc-component-di).

In modern agile teams maybe the biggest advantage is that no programmer can
introduce a new dependency accidentally. Thus helping to keep the architectural
design clean.

## integration with other di systems
There is even a way to combine it with the symfony container if you want to test it
or migrate your symfony app over a longer time period.

## releases

### release v2.0

* complete rewrite based on a new view on dependency injection

* usage of global functions for injection

* support for container merging dropped - in favour of customization (via extending
 or interfaces)

* support for flags dropped - all can now be done explicit

* support for tree typed dependencies dropped - extend your classes with different
 constructors or use a factory to support different injection types (its more explicit 
 by the way)

* support for circular dependency detection dropped - in favour of php doing the job 

* all types of dependency configuration are dropped - in favour of pure injection
 (Yes, its true! There is no dependency configuration anymore just parameter import,
 decoration and mocking.)

* dependency on other libraries dropped - in favour of a lightweight architecture



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
