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

use eArc\DI\Exceptions\MakeClassException;

/**
 * Describes the interface of a static dependency resolver.
 */
interface ResolverInterface
{
    /**
     * Returns an instance of the class identified by its fully qualified class name.
     * The resolver returns the same instance for successive calls unless `clearCache`
     * is used for the same identifier in between.
     *
     * Hint: Don't rely on the `singleton` behaviour of `get`. Its main purpose are
     * performance considerations. If your architecture need to get always the same
     * instance for a class make it explicit and use a factory or a real singleton
     * instead.
     *
     * @param string $fQCN Identifier of the class to resolve.
     *
     * @return object An instance.
     *
     * @throws MakeClassException Error while instantiating the class.
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
     * @throws MakeClassException Error while instantiating the class.
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
    public static function has(string $fQCN): bool;

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
     * If `$fQCN` is equal to `fQCNReplacement` the decoration of `$fQCN` is removed.
     *
     * This method is useful for fixing unchangeable classes, replacing classes
     * to change behavior in different environments or mocking by explicit class
     * mocks.
     *
     * @param string $fQCN
     * @param string $fQCNReplacement
     */
    public static function decorate(string $fQCN, string $fQCNReplacement): void;

    /**
     * Returns true if the class identified by its fully qualified class name is
     * decorated. Returns false otherwise.
     *
     * @param string $fQCN
     *
     * @return bool
     */
    public static function isDecorated(string $fQCN): bool;

    /**
     * Returns the decorator of a class identified by its fully qualified class name.
     * Successive decorations does not apply.
     *
     * @param string $fQCN The identifier of the decorated class.
     * .
     * @return string|null The Decorator or null if the class is not decorated.
     */
    public static function getDecorator(string $fQCN): ?string;

    /**
     * Adds a tag to a class.
     *
     * @param string $fQCN The fully qualified class name of the class to tag.
     * @param string $name The tag name.
     */
    public static function tag(string $fQCN, string $name): void;

    /**
     * Returns an iterable for iterating over all fully qualified class names being
     * tagged by a tag.
     *
     * @param string $name The tag name.
     *
     * @return iterable
     */
    public static function getTagged(string $name): iterable;

    /**
     * Clears a tag from a class identifier. If the class identifier is null all
     * tags of the same name are cleared.
     *
     * @param string      $name The tag name.
     * @param string|null $fQCN The fully qualified class name or null.
     */
    public static function clearTags(string $name, string $fQCN=null): void;

    /**
     * Mocks a class. `get($fQCN)` and `make($fQCN)` always return the object passed
     * as mock. Decoration is applied before mocking. You need to mock the decorator
     * not the decorated class.
     *
     * @param string $fQCN The identifier of the class to mock.
     * @param object $mock The Mock.
     */
    public static function mock(string $fQCN, object $mock): void;

    /**
     * Checks whether a class is mocked.
     *
     * @param string $fQCN The identifier of the possibly mocked
     *
     * @return bool
     */
    public static function isMocked(string $fQCN): bool;

    /**
     * Unset a mock. `get($fQCN)` and `make($fQCN)` behave again normally. If null
     * is passed as argument all mocks are cleared.
     *
     * @param string|null $fQCN The identifier of the mocked class.
     */
    public static function clearMock(string $fQCN=null): void;
}
