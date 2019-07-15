<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * dependency injection component
 *
 * @package earc/di
 * @link https://github.com/Koudela/eArc-di/
 * @copyright Copyright (c) 2018-2019 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\DI\Interfaces;

use eArc\DI\Exceptions\ClassNotFoundException;
use eArc\DI\Exceptions\ExecuteCallableException;
use eArc\DI\Exceptions\MakeClassException;

/**
 * Describes the interface of a static dependency resolver.
 */
interface ResolverInterface
{
    /**
     * Returns an instance of the class identified by its fully qualified class name.
     * The resolver returns the same instance for successive calls unless `clearCache`
     * or `decorate` are used for the same identifier in between.
     *
     * Hint: Don't rely on the `singleton` behaviour of `get`. Its main purpose are
     * performance considerations. If your architecture need to get always the same
     * instance for a class use a factory or a real singleton instead.
     *
     * @param string $fQCN Identifier of the class to resolve.
     *
     * @return object An instance.
     *
     * @throws ClassNotFoundException   No class was found for **this** identifier.
     * @throws MakeClassException       Error while instantiating the class.
     * @throws ExecuteCallableException Error while executing the callables.
     */
    public static function get(string $fQCN): object;

    /**
     * Returns a new instance of the class identified by its fully qualified class
     * name.
     *
     * Implementation detail: The Resolver must not save a reference on the returned
     * object.
     *
     * Hint: Use `make` instead of `get` if you need garbage collection for your
     * object.
     *
     * @param string $fQCN Identifier of the class to create a new instance for.
     *
     * @return object The new instance.
     *
     * @throws ClassNotFoundException   No class was found for **this** identifier.
     * @throws MakeClassException       Error while instantiating the class.
     * @throws ExecuteCallableException Error while executing the callables.
     */
    public static function make(string $fQCN): object;

    /**
     * Returns true if the resolver can return a class object for the given identifier.
     * Returns false otherwise.
     *
     * `has($fQCN)` returning true does not mean that `get($fQCN)` or `make($fQCN)`
     * will not throw an exception. It does however mean that `get($fQCN)` and `make($fQCN)`
     * will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $fQCN Identifier of the class to look for.
     *
     * @return bool
     */
    public static function has(string $fQCN);


    /**
     * Forces the resolver to clear his cache for the specified instance. Thus `get($fQCN)`
     * is forced to return a new instance once. If null is passed as argument this
     * applies for all instances.
     *
     * @param string|null $fQCN
     */
    public static function clearCache(string $fQCN=null): void;

    /**
     * Decorates the class identified by its fully qualified class name $fQCN with
     * the class identified by its fully qualified class name $fQCNReplacement. After
     * that `get($fQCN)` and `make($fQCN)` behave as if `get($fQCNReplacement)` or
     * `make($fQCNReplacement)` has been called. Successive decorations apply.
     *
     * This method is useful for fixing unchangeable classes or mocking classes in
     * the test environment.
     *
     * @param string $fQCN
     * @param string $fQCNReplacement
     *
     * @throws ClassNotFoundException The arguments can not be resolved.
     */
    public static function decorate(string $fQCN, string $fQCNReplacement): void;

    /**
     * Returns true if the class identified by its fully qualified class name is
     * decorated. Returns false otherwise.
     *
     * @param string $fQCN
     *
     * @return bool
     *
     * @throws ClassNotFoundException The argument can not be resolved.
     */
    public static function isDecorated(string $fQCN): bool;

    /**
     * Returns the decorator of a class identified by its fully qualified class name.
     * Successive decorations does not apply.
     *
     * @param string $fQCN The identifier of the decorated class.
     * .
     * @return string The Decorator.
     *
     * @throws ClassNotFoundException The argument can not be resolved or no decoration
     * is applied on **this** identifier.
     */
    public static function getDecorator(string $fQCN): string;


    /**
     * Registers a callable to a class identified by its fully qualified class name.
     * Each time a **new** instance is created by `get($fQCN)` or `make($fQCN)`
     * the callable is called just before the instance is returned.
     *
     * Hint: Use this method to register some class X to a class $fQCN if the class
     * X can not or must not be registered by design.
     *
     * @param ResolverCallableInterface $callable
     */
    public static function registerCallable(ResolverCallableInterface $callable): void;

    /**
     * Checks whether at least one callable is registered to a class identified by
     * the fully qualified class name. If null is passed instead of the fully qualified
     * class name the check applies to all classes. If `$tags` are not empty the
     * check applies to callables with the specified tags only.
     *
     * @param string|null $fQCN The identifier of the class or null.
     * @param array $tags Filter by a group of tags.
     *
     * @return bool
     */
    public static function hasRegisteredCallables(string $fQCN=null, array $tags=[]): bool;

    /**
     * Executes the callables registered to a class identified by the fully qualified
     * class name. If `$tags` are not empty the callables with the specified tags
     * are applied only.
     *
     * Hint: Use this method in the constructor if you decorate a class and want to
     * see the callables from the decorated class executed.
     *
     * @param object $class The object the callables get applied to.
     * @param string $fQCN  The fully qualified class name of the class the callables
     * get applied to.
     * @param array  $tags  Filter by a group of tags.
     *
     * @throws ExecuteCallableException Error while executing the callables.
     */
    public static function executeCallables(object $class, string $fQCN, array $tags=[]): void;

    /**
     * Returns an iterable for all callables registered to a class identified by the
     * fully qualified class name. If null is passed instead of the fully qualified
     * class name this applies to all classes. If `$tags` are not empty the
     * check applies to callables with the specified tags only.
     *
     * @param string|null $fQCN The identifier of the class or null.
     * @param array $tags Filter by a group of tags.
     *
     * @return iterable
     */
    public static function getIterableForRegisteredCallables(string $fQCN=null, array $tags=[]): iterable;

    /**
     * Unregisters all callables registered to a class identified by the fully qualified
     * class name. If null is passed instead of the fully qualified class name this
     * applies to all classes. If `$tags` are not empty the callables with the specified
     * tags are getting unregistered only.
     *
     * @param string|null $fQCN The identifier of the class or null.
     * @param array $tags Filter by a group of tags.
     */
    public static function clearRegisteredCallables(string $fQCN=null, array $tags=[]): void;
}
