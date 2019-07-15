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

use eArc\DI\Exceptions\CircularDependencyException;
use eArc\DI\Exceptions\InvalidFactoryException;
use eArc\DI\Exceptions\InvalidObjectConfigurationException;

/**
 * Interface for container compatible to the lazy loaded eArc dependency
 * injection container.
 */
interface DependencyContainerInterface extends DependencyInjectionInterface
{
    /**
     * Configuration of the dependency injection container via an array.
     * Executes set() for each configuration item. Can be called arbitrary times.
     *
     * @param array $config The dependency configuration of the container.
     *
     * @return void
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function load(array $config): void;

    /**
     * Set and resolve an entry. If the $name exists already it gets overwritten.
     *
     * @param string      $name   The key name.
     * @param array|mixed $config The configuration of the object or the plain
     * container item.
     *
     * @return void
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function set(string $name, array $config): void;
}
