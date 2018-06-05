<?php
/**
 * e-Arc Framework - the explizit Architecture Framework 
 *
 * @package earc/di
 * @link https://github.com/Koudela/earc-di/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\di;

/**
 * Usage interface for the eArc DependencyContainer.
 */
interface IDependencyContainerUse
{
    /**
     * Returns true if the container is configured for the given class 
     * identifier. Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an 
     * exception. It does however mean that `get($id)` will not throw a 
     * `NotFoundExceptionInterface`.
     *
     * @param string $id Class identifier of the object to look for.
     *
     * @return bool
     */
    public function has($id): bool;

    /**
     * Get an instance of the object.
     *
     * @return object
     */
    public function get($id): object;

    /**
     * Get a **new** instance of the object.
     *
     * @return object
     */
    public function make($id): object;
}