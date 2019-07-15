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
use eArc\Container\Exceptions\ItemNotFoundException;
use eArc\DI\Exceptions\InvalidObjectConfigurationException;
use Psr\Container\ContainerInterface;

/**
 * Interface for basic psr-11 compatible dependency injection containers.
 */
interface DependencyInjectionInterface extends ContainerInterface
{
    /**
     * Get a new instance of the configured class item.
     *
     * @param string $name container item identifier of the class to create a
     * new instance for.
     *
     * @return object The new instance.
     *
     * @throws ItemNotFoundException
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     */
    public function make(string $name): object;
}
