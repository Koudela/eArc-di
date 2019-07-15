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

namespace eArc\DI\Support;

use eArc\Container\Exceptions\ItemNotFoundException;
use eArc\DI\Interfaces\ContainerCollectionInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContainerCollection implements ContainerCollectionInterface
{
    /** ContainerInterface[] */
    protected $container = [];

    /**
     * @inheritdoc
     */
    public function merge(ContainerInterface $container): void
    {
        $this->container[spl_object_hash($container)] = $container;
    }

    /**
     * @inheritdoc
     */
    public function reset(): void
    {
        $this->container = [];
    }

    /**
     * Check whether there exists a container with this item key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key): bool
    {
        foreach ($this->container as $container) {
            if ($container->has($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a item from the container collection.
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws ItemNotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function get($key)
    {
        foreach ($this->container as $container) {
            if ($container->has($key)) {
                return $container->get($key);
            }
        }

        throw new ItemNotFoundException($key);
    }
}
