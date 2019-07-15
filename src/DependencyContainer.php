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

namespace eArc\DI;

use eArc\Container\Exceptions\ItemNotFoundException;
use eArc\DI\Exceptions\CircularDependencyException;
use eArc\DI\Exceptions\InvalidFactoryException;
use eArc\DI\Exceptions\InvalidObjectConfigurationException;
use eArc\DI\Interfaces\ContainerCollectionInterface;
use eArc\DI\Interfaces\DependencyContainerInterface;
use eArc\DI\Support\ContainerCollection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class DependencyContainer implements DependencyContainerInterface, ContainerCollectionInterface
{
    /** @var DependencyResolver */
    protected $dependencyResolver;

    /** @var ContainerCollection */
    protected $mergedContainer;

    /**
     * @param DependencyResolver $dependencyResolver
     * @param ContainerCollection $mergedContainer
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function __construct(
        DependencyResolver $dependencyResolver = null,
        ContainerCollection $mergedContainer = null)
    {
        $this->dependencyResolver = $dependencyResolver ?? new DependencyResolver();
        $this->mergedContainer = $mergedContainer ?? new ContainerCollection();
    }

    /**
     * Load the configuration into the dependency container for lazy resolving.
     *
     * @param array $config
     * @param array $flags
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function load(array $config, $flags = []): void
    {
        $this->dependencyResolver->load($config, $flags);
    }

    /**
     * Merge a psr-11 compatible container object into the dependency container.
     * The new items get looked up by has or get if the lookup in the local
     * dependency container fails. (There is no make support for these
     * containers and they will not get used for building new objects.)
     *
     * @param ContainerInterface $container
     */
    public function merge(ContainerInterface $container): void
    {
        $this->mergedContainer->merge($container);
    }

    /**
     * Set a dependency container item vor lazy resolving. If an item with the
     * same name is set already it gets overwritten.
     *
     * @param $name
     * @param $item
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function set($name, $item): void
    {
        $this->dependencyResolver->set($name, $item);
    }

    /**
     * Check whether the dependency container has an item.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name): bool
    {
        return $this->dependencyResolver->has($name) || $this->mergedContainer->has($name);
    }

    /**
     * Get an item from the dependency container.
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function get($name)
    {
        if ($this->dependencyResolver->has($name)) {
            return $this->dependencyResolver->get($name);
        }

        return $this->mergedContainer->get($name);
    }

    /**
     * Get a new item instance from the dependency container.
     *
     * @param string $name
     *
     * @return object
     *
     * @throws ItemNotFoundException
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     */
    public function make(string $name): object
    {
        return $this->dependencyResolver->make($name);
    }

    /**
     * Removes all merged container.
     */
    public function reset(): void
    {
        $this->mergedContainer = [];
    }
}
