# earc/di

Standalone lightweight dependency injection component of the eArc libraries.

If you need to decouple your components, restrict injection access or want to make 
your app components explicit use the [earc/component-di](https://github.com/Koudela/eArc-component-di) library
which builds on the top of earc/di.

## table of contents
 
 - [pro/cons](#procons)
 - [installation](#installation)
 - [basic usage](#basic-usage)
 - [parameters](#parameters)
   - [usage](#usage)
   - [dot syntax](#dot-syntax)
   - [import](#import)
   - [best practice](#best-practice)
 - [factories](#factories)
 - [decoration](#decoration)
 - [namespace decoration](#namespace-decoration)
 - [mocking](#mocking)
 - [tagging](#tagging)
 - [troubleshooting](#troubleshooting)
 - [exceptions](#exceptions)
 - [advanced usage](#advanced-usage)
   - [performance considerations](#performance-considerations)
   - [architectural considerations](#architectural-considerations)
   - [integration with other di systems](#integration-with-other-di-systems)
 - [releases](#releases)
   - [release 3.1](#release-31)
   - [release 3.0](#release-30)
   - [release 2.4](#release-24)
   - [release 2.3](#release-23)
   - [release 2.2](#release-22)
   - [release 2.1](#release-21)
   - [release 2.0](#release-20)
   - [release 1.0](#release-10)
   - [release 0.1](#release-01)

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
- **easy serialization** - no need to rewire your dependencies on wakeup.
- **support for decorating plain function calls**
- **support for explicit programming/architecture** - a class has hold of all its 
 implementation details (apart from decoration, mocking and parameters which are by 
 their very nature foreign context driven)
 - **extendable** - integrates with [other dependency systems](#integration-with-other-di-systems).

### cons

- **dependency** on a dependency injection library - although it's a really soft
coupling (you can adjust and even replace the logic behind the `di_*` functions by 
any logic you like.) 
- **small overhead** - decoration, mocking and tagging need some programming logic

## installation

Install the earc dependency injection library via composer.

```
$ composer require earc/di
```

You can even use it with [symfony](#integration-with-other-di-systems).

## basic usage

A new dependency resolver can be initialized with no arguments. Use this in your 
`index.php`, bootstrap or configuration script.

```php
use eArc\DI\DI;

DI::init();
```

After that classes and parameters can be accessed via the `di_*` functions.

```php
di_get(SomeClass::class); // returns an instance of SomeClass::class

di_param('some.parameter'); // returns the value of the parameter `some.parameter`
```

`di_get` returns the same instance in successive calls. If you need a *new* object
 use `di_make`. It returns a new instance on each call. In both functions the use 
of the fully qualified class name as parameter is mandatory.

Classes injected by `di_get` or `di_make` must not have constructor parameters. 
Surprisingly \*cough\* there is no need for constructor parameters. 

```php
class alphaCentauri {
    //...
    public function construct()
    {
        $this->dependency1 = di_get(DependencyOne::class);
        $this->dependency2 = di_get(DependencyTwo::class);
        $this->parameterAlpha = di_param('alpha');
        $this->parameterBeta = di_param('beta');
        $this->parameterGamma = di_param('gamma');
    }
    //...
}
```

Method and even function injection is supported.

```php
class Example
{
    public function getResult($param)
    {
        $math = di_get(Math::class);
        return $math->calculate($param, di_param('pi'));
    }
}

function depending_on_injection($param)
{
        $math = di_get(Math::class);
        return $math->calculate($param, di_param('pi'));
}

$result = depending_on_injection(42);
``` 

There is no need for any further dependency configuration!

Of course you need to import the parameters though.

## parameters

A parameter is key value pair. The key is a `string` and the value can be of any
type - even a closure. There may be other restricting factors though. If you choose
to organize your parameters in YAML-files you might be restricted to `string`, `int`, 
`float`, `bool` and array.   

### usage

`di_param('key_name')` returns the parameter value that belongs to the key `key_name`.

You can check for existence by `di_has_param('key_name')`.

If a parameter is generated dynamically you can use `di_set_param('key_name', $value)`
to make it globally available. A immutable request object would make in most use 
cases a valuable dynamically generated parameter.

Hint: A mutable global parameters is no parameter at all. Otherwise it would introduce
really huge side effects.

If the parameter is not set a `NotFoundException` is thrown by `di_param`. You can 
suppress this behaviour by supplying a default parameter other than null as second 
argument. Instead of throwing an exception the default parameter is returned.

### dot syntax

In big applications the parameter key names can cause naming conflicts. Therefore 
it is a good idea to organize your parameter keys in a tree hierarchy like the namespaces
in php. Arrays give you that tree hierarchy for free but it is not easy to see
the tree behind 
```php
di_param('base_key')['level1']['level2']['parameter_name'];
```

It gets even harder if you have to search `parameter_name` in unknown code as it 
may be used by several different parameters. Therefore earc/di supports the dot 
syntax for `di_param`, `di_has_param` and `di_set_param`. 

In dot syntax the above would be
 
```php
di_param('base_key.level1.level2.parameter_name');
```

### import

`di_import_param` takes an (potentially multidimensional) array as argument. This
keeps it flexible for all implementation details and frameworks. You can hardcode
your Parameters
 
```php
# config.php

di_import_param([
    'data' => [
        'server' => [
            'mysql' => [
                'user' => 'foo',
                'db' => 'bar',
                'password' => 'x23W!_bxTff',
                //...
            ],
            //...
        ],
        //...
    ],
    //...
]);
```

or use the popular YAML-format

```YAML
# config.yml
data:
    server:
        mysql:
            user: 'foo'
            db: 'bar'
            password: 'x23W!_bxTff'
...
```

```php
# bootstrap.php

di_import_param(
    Yaml::parse(
        file_get_contents('/path/to/config.yml')
    )
);
```

`di_import_param` is different to `di_set_param` as parameters may be overwritten.
Thus libraries are able to offer default parameter which may (or may not) be changed
by software using this library.

### best practice

Define in your Project one `ParameterInterface` per module. Define all parameter
keys (dot syntax) via constants in the `ParameterInterface`. Let all classes using
parameters implement the `ParameterInterface` of the module.

For example the `ParameterInterface` of the 
[earc/event-tree](https://github.com/Koudela/eArc-eventTree/blob/master/src/Interfaces/ParameterInterface.php) 
let you know all Parameters that might be relevant to you. You don't need to know 
all the documentation, just one file. Such explicit programming could be a live 
saver in sparsely documented projects.

```php
interface ParameterInterface
{
    const VENDOR_DIR = 'earc.vendor_directory';
    const ROOT_DIRECTORIES = 'earc.event_tree.directories';
    const BLACKLIST = 'earc.event_tree.blacklist';
}
```   

## factories

If you want to use objects of third party libraries which are not using earc/di, 
they are sometimes not easy to build. Think of the doctrine `EntityManager`. It is
such a common object in apps using doctrine, it is a serious obstacle to inject
a factory instance instead of the real object. 

Use `di_register_factory` instead to registers a `callable` to a fully qualified 
class name. 

```php
di_register_factory(SomeInterface::class, [Factory::class, 'build']);
```

Thereafter the `callable` is used for building the object.

If an instance was retrieved by `get` already, you must call `clear_cache($fCQN)`
manually to `get` the instance from the factory.

If you register a second factory to the same fully qualified class name, the second
factory is used. By passing `null` as factory parameter you can unregister a factory.

## decoration

Using an interface as argument of `di_get` or `di_make` is a good idea. earc/di uses
the argument as type hint. A class as argument states that all potentially injected 
classes have to inherit from the type hinted class. An interface as argument results
in a potentially wider range of classes to inject as they only need to implement the
interface. But that freedom comes with a trade-off: earc/di does not know  the class 
to build and inject anymore. Therefore each interface used as parameter for `di_get`
or `di_make` must be decorated first.

```php
di_decorate(SomeInterface::class, ThisImplementsTheInterface::class);
```   

But decoration is much more. It is where the last decision is made what to inject
and this as many times as it is needed.

It is where the **inversion of control** takes place, the **service locator** part.

Assume you write a library. You add some cool features and bugfixes every month. 
You have a job, a wife, a few kids, so your reaction time on issues and merge
requests does not perform very well. Your library is used as third party library 
in production nevertheless. After an upgrade an error in a method is detected in 
production. The programming engineers need a quick way to fix your library. They 
do not want to fork your library, because keeping a forked library up to date could 
be a big task. Wouldn't it be nice if they could just exchange the method. 

`di_decorate` does the job. Extending the class, overwriting the erroneous method
and decorating the original class is all it needs.

```php
di_decorate(ServiceContainingAnError::class, ServiceDecorator::class);

get_class(di_get(ServiceContainingAnError::class)); // equals ServiceDecorator::class
get_class(di_make(ServiceContainingAnError::class)); // equals ServiceDecorator::class
```

Everywhere the error-prone service was injected now the class with the fix demands 
its place.

earc/di enables you to decorate abstract classes.

```php
$staticService = di_static(AbstractService::class);
$staticService::staticMethod(); // calls AbstractService::staticMethod()

di_decorate(StaticService::class, AbstractServiceDecorator::class);

$staticService = di_static(StaticService::class);
$staticService::staticMethod(); // calls AbstractServiceDecorator::staticMethod()
```

Decoration is something you do in the configuration part of your software. To be not
too cumbersome there is a way to do this in a configuration file. On calling `DI::importParameter()` 
the parameter `earc.di.class_decoration` is imported.

```YAML
# config.yml

earc:
    di:
        class_decoration:
            namespace\\A\\SomeServiceClass: 'namespace\\B\\SomeServiceClass'
            namespace\\A\\SomeOtherServiceClass: 'namespace\\B\\SomeOtherServiceClass'
...
```

```php
use eArc\DI\DI;

DI::init();

di_import_param(
    Yaml::parse(
        file_get_contents('/path/to/config.yml')
    )
);

DI::importParameter();
```

Please note the order of the calls is important.

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
## namespace decoration

Sometimes you mirror the file structure from one project in another project. Then
it may be desirable to decorate a class automatically by (re)placing it in the mirrored 
file structure. That is what namespace decoration is for. You can activate this feature
by setting the `earc.di.namespace_decoration` parameter before calling `DI::importParameter()`.

```YAML
# config.yml

earc:
    di:
        namespace_decoration:
            ['namespace\\of\\dir\\project\\A', 'namespace\\of\\mirrored\\dir\\project\\B']
            # you can decorate as many namespaces you want.
            # but you can even chain them. You have to name 
            # every namespace decoration explicitly:
            ['namespace\\of\\mirrored\\dir\\project\\B', 'namespace\\of\\mirrored\\dir\\project\\C'] 
            ['namespace\\of\\dir\\project\\A', 'namespace\\of\\mirrored\\dir\\project\\C']
```

```php
use eArc\DI\DI;

DI::init();

di_import_param(
    Yaml::parse(
        file_get_contents('/path/to/config.yml')
    )
);

DI::importParameter();
```    

## mocking

In most testing libraries mocks are objects. Thus you can't use decoration for replacing 
the original class. `di_mock` is made for this use cases. 

```php
$getObj = di_get(Service::class);
$makeObj = di_get(Service::class);

$mockedService = TestCase::createMock(Service::class);
di_mock(Service::class, $mockedService);

Assert::assertSame($mockedService, di_get(Service::class)); // passes 
Assert::assertSame($mockedService, di_make(Service::class)); // passes
Assert::assertSame($getObj, di_get(Service::class)); // fails 
Assert::assertSame($makeObj, di_make(Service::class)); // fails
```

Keep in mind: Static accessed methods (via `di_static`) can be mocked via decoration only
as `di_static` returns a `string` and `di_mock` takes an `object`.

```php
di_static(StaticService::class)::staticMethod(); // calls StaticService::staticMethod()

di_decorate(StaticService::class, StaticMock::class);

di_static(StaticService::class)::staticMethod(); // calls StaticMock::staticMethod()
```

You can check if an service is mocked by `di_is_mocked`.

```php
Assert::assertSame(true, di_is_mocked(ServiceClass)); // passes 
Assert::assertSame(true, di_is_mocked(AnotherService::class)); // fails
```

If you need the real service again use `di_clear_mock`.

```php
di_clear_mock(Service::class);

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

Assert::assertSame(true, di_is_mocked(Service::class)); // passes
Assert::assertSame(true, di_is_mocked(ServiceDecorator::class)); // passes

Assert::assertSame(1, di_get(Service::class)->iAmMock); // fails
Assert::assertSame(2, di_get(Service::class)->iAmMock); // passes
Assert::assertSame(2, di_get(ServiceDecorator::class)->iAmMock); // passes
```

As you see in the example code mocks are not forced to follow the type hints. This
means you can pass as mock whatever you want. (As long as your code do not pass your 
Services as type hinted arguments around.) 

## tagging

Maybe you solve a problem by implementing the [chain of responsibility - design pattern](https://sourcemaking.com/design_patterns/chain_of_responsibility).
Only the third party software knows which services add to this implementation. This
leaves four questions:

1. How to register to a base service without instantiating it?
2. How to tell the base service on instantiation without instantiating all handlers?  
3. Where is the best place to write the information?
4. Where is the best place to store the information?

Three answers solves earc/di for you through tagging.

The third party can store the relevant piece of information by executing

```php
di_tag('tag.name', Service002::class);
di_tag('tag.name', Service007::class);
di_tag('tag.name', Service014::class);
```

Your base class can retrieve the service classes by iterating over `di_get_tagged('tag.name')`

```php
foreach (di_get_tagged('tag.name') as $handlerName => $argument) {
    $handler = di_get($handlerName);
    if ($handler->canHandleTask($task)) {
        $result = $handler->handleTask($task);
        
        return $result;
    }
}

throw new TaskNotHandledException($task);
```

If you would have pass a third argument to `di_tag` `$argument` would hold this
argument instead of `null`. 

Of course the services should implement an interface and the base service should
check for it to avoid failure on name conflicts or forgotten methods. And yes,
logging handlers not implementing the interface is a good idea. But you know about
the architecture your software needs (and can afford) best.

Hint: Decoration is not applied to the tags but to `di_get` of course.

## troubleshooting

earc/di has dropped circular dependency detection in favour of performance. If you
experience an error like the following in the earc/di code its cause is most likely
a circular dependency. Search for a circular class dependencies in your code. 

```
$ PHP Fatal error:  Uncaught Error: Maximum function nesting level of '256' reached, aborting! 
```

## exceptions

 * All exceptions thrown inherit from `BaseException`.

 * An `InvalidArgumentException` is thrown if 
    1. `init()` is called and the classes identified by the parameter does not implement 
    the `ResolverInterface` and the `ParameterBagInterface` respectively.
    2. `di_make()` or `di_get()` use decorators that do not respect the type hint given
    by the argument. (Please note, this check is not done on calling `di_decorate`
    to avoid early loading of the class files.)
    3. `di_set_param` would overwrite an existing parameter. 
 
 * An `MakeClassException` is thrown if some Exception is thrown while calling the 
 class constructor.

 * An `NotFoundDIException` is thrown if a parameter should be retrieved that never 
 was set/imported.
 
## advanced usage  

Some usages are not obvious but desirable, like function decoration. If you call
your own functions with `di_static` they can be decorated using `di_decorate`.

```php
function something_cool($times) {
    return $times.' x icecream';
}

di_static('something_cool')(42); // returns '42 x icecream'

function something_cool_but_its_winter($times) {
    return $times.' x hot tea';
}

di_decorate('something_cool', 'something_cool_but_its_winter');

di_static('something_cool')(42); // returns '42 x hot tea'
```

### performance considerations

The classes that handle the behaviour of earc/di have in sum round about 250 
lines of code, fast array calculations mainly. But nevertheless some big app
or server limitations may force a second thought on performance.

Objects retrieved by `di_get` can not be garbage collected until someone calls `di_clear_cache`.
If you use an object only once or for a tiny moment it might save a bit of memory 
if you use `di_make` instead. 

Calling `di_has` performs existence checks for classes and interfaces. This checks
can trigger an autoload. Use them wisely. The same is true for `di_is_decorated` in
the case you use namespace decoration.

Namespace decoration uses string substitution which is considerable slower than
key lookups on arrays. Nevertheless if you have to decorate hundreds of classes 
it outperforms explicit configuration since it is used only on classes which are 
active for the request, wheres configuration must be processed on every request 
for all decorated classes.

### architectural considerations

Don't rely on the singleton behaviour of `di_get`. Its main purpose are performance 
considerations. If your architecture need to get always the same instance for a 
class make it explicit and use a real singleton instead.

In earc/di each type hint is set globally. This means each type hint forces exactly
one class. If you feel pushed to use `di_decorate` outside of the configuration part
of your app to change that behaviour you experience a bad architecture smell. It shows
that some of your classes demand more than they type hint. Maybe your interfaces 
do not follow the interface segregation principle, maybe you need just another 
interface or maybe some of your classes do not follow the single responsibility 
principle.

### integration with other di systems

You are able to completely rewrite the behavioral logic behind the scene. This enables
earc/di to integrate with nearly all dependency injection systems. Extend the Resolver::class
or the ParameterBag::class to your need or implement the corresponding Interfaces.
Register your class(es) by the `DI::init` method. Now the `di_*` functions
follow the logic you have implemented.

If the third party di-system uses a container then integration is a beginners task. 
You can find a ready to use example for symfony in the bridge folder. Don't forget 
to switch your symfony service definitions to `public`.

If you use symfony register the SymfonyDICompilerPass and you are ready go (or to 
migrate step by step).    

There is no limit. Create your own one to rule them all and make your live easy again.    

## releases

### release 3.1

* circular dependency detection

### release 3.0

* PHP 8.0 support
* di_tag parameter swap to give you a better ability to search relevant pieces in code

### release 2.4

* IDE support for PHPStorm:
    - return type support for `di_get`, `di_make` and `di_static`

### release 2.3

* factory support

### release 2.2

* default parameter 
* batch decoration
* namespace decoration

### release 2.1

* an argument can be passed for a tag

### release 2.0

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
 decoration, tagging and mocking. Dependency configuration is reduced to the service 
 locator pattern it is based on. Each type hint is set globally, which reduces
 the information you must cope with significantly.)

* dependency on other libraries dropped - in favour of a lightweight architecture

### release 1.0

* support for flags.

* support for container merging and container merging at construction time only.

* announced new feature subset generation is dropped in favour of 
[earc/components-di](https://github.com/Koudela/eArc-component-di)

* `NotFoundException` and `OverwriteException` dropped in favour of 
`ItemNotFoundException` and `ItemOverwriteException` from the 
[earc/payload-container package](https://github.com/Koudela/eArc-payload-container).

* `DependencyContainer::loadFile()` is no longer supported. You can emulate it 
by `DependencyContainer::load(include ...)`.

### release 0.1

the first official release
