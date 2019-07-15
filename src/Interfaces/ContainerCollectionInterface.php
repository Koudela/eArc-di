<?php
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

use Psr\Container\ContainerInterface;

/**
 * Interface for a collection of psr-11 compatible container.
 */
interface ContainerCollectionInterface extends ContainerInterface
{
    /**
     * Merges a container into the collection.
     *
     * @param ContainerInterface $container
     */
    public function merge(ContainerInterface $container): void;

    /**
     * Removes all container from the collection.
     */
    public function reset(): void;
}
