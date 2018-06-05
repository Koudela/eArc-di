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
 * Lazy configuration interface for the eArc DependencyContainer.
 */
interface IDependencyContainerConfig
{
    /**
     * Lazy initialation of the DependencyContainer configuration via array.
     *
     * @param array $config The dependency configuration of the container.
     * @return void
     */
    public function load(array $config): void;

    /**
     * Lazy initialation of the DependencyContainer configuration via file.
     *
     * @param string $filename Location of the configuration file returning an
     * dependency configuration array.
     * @return void
     */
    public function loadFile(string $filename): void;

    /**
     * Set a lazy configuration for an object.
     *
     * @param string $id The objects class name.
     * @param array $config The dependency configuration of the object.
     * @return void
     */
    public function set(string $id, array $config): void;
}
