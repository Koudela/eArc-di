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

/**
 * Interface for objects used as callables in the resolver context.
 */
interface DICallableInterface
{
    /**
     * Returns the fully qualified class name identifier of the class the callable
     * is attached to.
     *
     * @return string
     */
    public function getClassName(): string;

    /**
     * Returns the callable that should get called after instantiation of the class.
     *
     * @return callable
     */
    public function getCallable(): callable;

    /**
     * Returns the arguments that should be passed to the callable.
     *
     * Hint: The first argument passed to the callable is the class object. The arguments
     * returned by this method are passed from second position on.
     *
     * @return array
     */
    public function getArguments(): array;

    /**
     * Returns the names the resolver callable is tagged with.
     *
     * @return string[]
     */
    public function getTags(): array;

    /**
     * Checks whether the callable is tagged with at least one of the passed tags.
     * If the passed `$tags` are empty `isTaggedBy($tags)` is always true.
     *
     * @param string[] $tags The tags to check.
     *
     * @return bool
     */
    public function isTaggedBy(array $tags=[]): bool;
}
