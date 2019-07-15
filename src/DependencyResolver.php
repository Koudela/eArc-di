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
use eArc\Container\Items;
use eArc\DI\Exceptions\CircularDependencyException;
use eArc\DI\Exceptions\InvalidFactoryException;
use eArc\DI\Exceptions\InvalidObjectConfigurationException;
use eArc\DI\Interfaces\Flags;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class DependencyResolver
{
    /** @var DependencyResolver|null */
    protected $parent;

    /** @var ContainerInterface|null */
    protected $readOnlyContainer;

    /** @var Items */
    protected $items;

    /**
     * @param array $config
     * @param DependencyResolver|null $parent
     * @param ContainerInterface $readOnlyContainer
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function __construct(
        $config = [],
        DependencyResolver $parent = null,
        ContainerInterface $readOnlyContainer = null)
    {
        $this->parent = $parent;
        $this->readOnlyContainer = $readOnlyContainer;
        $this->items = new Items();
        $this->load($config);
    }

    /**
     * Load the configuration into the dependency resolver for lazy
     * instantiation.
     *
     * @param array $config
     * @param array $flags
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function load(array &$config, array $flags = []): void
    {
        foreach ($config as $name => $item) {
            $this->set($name, $item, $flags);
        }
    }

    /**
     * Set an dependency resolver item vor lazy instantiation. If an item with
     * the same name is set already it gets overwritten.
     *
     * @param string|int $name
     * @param mixed      $item
     * @param array      $flags
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function set($name, &$item, array $flags = []): void
    {
        $flags = $this->getFlagsFromClass($name) + $flags;

        if (is_array($item) && isset($item[Flags::class])) {
            $flags = $item[Flags::class] + $flags;
        }

        if (isset($flags[Flags::ITEM_KEY])) {
            $name = $flags[Flags::ITEM_KEY];
        }

        if (!isset($flags[Flags::CLASS_NAME]) || !is_array($item)
            || isset($flags[Flags::DO_NOT_RESOLVE]) && $flags[Flags::DO_NOT_RESOLVE]) {
            if (is_string($name) && (!isset($flags[Flags::SAVE_NO_REFERENCE]) || !$flags[Flags::SAVE_NO_REFERENCE])) {
                if (is_array($item)) {
                    unset($item[Flags::class]);
                }
                $this->items->overwrite($name, $item);
            }
            return;
        }

        unset($item[Flags::class]);

        $dependencyObject = new DependencyObject(
            isset($flags[Flags::FACTORY]) ? $flags[Flags::FACTORY] : $flags[Flags::CLASS_NAME],
            $item,
            $this->newChild($item)
        );

        if (isset($flags[Flags::INSTANT_MAKE]) && $flags[Flags::INSTANT_MAKE]) {
            $dependencyObject->get();
        }

        if (isset($flags[Flags::SAVE_NO_REFERENCE]) && $flags[Flags::SAVE_NO_REFERENCE]) {
            return;
        }

        $this->items->overwrite($name, $dependencyObject);
    }

    /**
     * Get the flags from the class definition.
     *
     * @param string $name
     *
     * @return array|null
     */
    protected function getFlagsFromClass($name): array
    {
        if (!is_string($name) || !class_exists($name)) {
            return [];
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $flags = is_subclass_of($name, Flags::class) ? $name::getDependencyInjectionFlags() : [];

        if (!isset($flags[Flags::CLASS_NAME])) {
            $flags[Flags::CLASS_NAME] = $name;
        }

        return $flags;
    }

    /**
     * Get the parent of the dependency resolver or null if it has no parent.
     *
     * @return DependencyResolver|null
     */
    public function getParent(): ?DependencyResolver
    {
        return $this->parent;
    }

    /**
     * Get a new child dependency resolver.
     *
     * @param array $config
     *
     * @return DependencyResolver
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function newChild(array $config): DependencyResolver
    {
        return new DependencyResolver($config, $this, $this->readOnlyContainer);
    }

    /**
     * Check whether the current dependency resolver has an item.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasLocal(string $name): bool
    {
        return $this->items->has($name);
    }

    /**
     * Get an item from the current dependency resolver.
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws ItemNotFoundException
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     */
    public function getLocal(string $name)
    {
        $item = $this->items->get($name);

        if (is_callable($item)) {
            return $item();
        }

        if ($item instanceof DependencyObject) {
            return $item->get();
        }

        return $item;
    }

    /**
     * Get a new item instance from the current dependency resolver.
     *
     * @param string $className
     *
     * @return object
     *
     * @throws ItemNotFoundException
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     */
    public function makeLocal(string $className): object
    {
        $item = $this->items->get($className);

        if (is_callable($item)) {
            return $item();
        }

        if ($item instanceof DependencyObject) {
            return $item->make();
        }

        throw new InvalidObjectConfigurationException(sprintf(
            '`%s` is not configured for make().',
            $className)
        );
    }

    /**
     * Check whether the dependency resolver chain has an item.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        $dependencyResolver = $this;

        do {
            if ($dependencyResolver->hasLocal($name)) {
                return true;
            }
        } while ($dependencyResolver = $dependencyResolver->getParent());

        return false;
    }

    /**
     * Get an item from the dependency resolver chain.
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws ItemNotFoundException
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     */
    public function get(string $name)
    {
        $dependencyResolver = $this;

        do {
            if ($dependencyResolver->hasLocal($name)) {
                return $dependencyResolver->getLocal($name);
            }
        } while ($dependencyResolver = $dependencyResolver->getParent());

        throw new ItemNotFoundException($name);
    }

    /**
     * Get a new item instance from the dependency resolver chain.
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
        $dependencyResolver = $this;

        do {
            if ($dependencyResolver->hasLocal($name)) {
                return $dependencyResolver->makeLocal($name);
            }
        } while ($dependencyResolver = $dependencyResolver->getParent());

        throw new ItemNotFoundException($name);
    }

    /**
     * Check if the item is located in the dependency resolver chain or at the
     * available read only container.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasFromAll(string $name): bool
    {
        return $this->has($name)
            || null !== $this->readOnlyContainer
            && $this->readOnlyContainer->has($name);
    }

    /**
     * Get an item from the dependency resolver chain or from the available read
     * only container.
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws ItemNotFoundException
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function getFromAll(string $name)
    {
        if ($this->has($name) || null === $this->readOnlyContainer) {
            return $this->get($name);
        }

        return $this->readOnlyContainer->get($name);
    }
}
