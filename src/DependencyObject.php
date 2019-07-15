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
use eArc\DI\Exceptions\InvalidFactoryException;
use eArc\DI\Exceptions\CircularDependencyException;
use eArc\DI\Exceptions\InvalidObjectConfigurationException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use \Throwable;

class DependencyObject
{
    /** @var string|callable */
    protected $factory;

    /** @var mixed[] */
    protected $config;

    /** @var DependencyResolver */
    protected $dependencyResolver;

    /** @var bool|object|null */
    protected $instance = false;

    /** @var bool */
    protected $buildIsInProgress = false;

    /**
     * @param string|callable    $factory The class name of the enclosed object
     * or a factory.
     * @param array              $config The configuration for the enclosed
     * object.
     * @param DependencyResolver $dependencyResolver
     *
     * @throws InvalidFactoryException
     */
    public function __construct(
        $factory,
        array &$config,
        DependencyResolver $dependencyResolver)
    {
        if ((!is_string($factory) || !class_exists($factory)) && !is_callable($factory)) {
            throw new InvalidFactoryException();
        }
        $this->factory = $factory;
        $this->config = $config;
        $this->dependencyResolver = $dependencyResolver;
    }

    /**
     * Get an instance.
     *
     * @return object|null
     *
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     */
    public function get(): ?object
    {
        if (false === $this->instance) {
            $this->instance = $this->make();
        }

        return $this->instance;
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * Get a new instance.
     *
     * @return object|null
     *
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     */
    public function make(): ?object
    {
        try {
            $args = $this->calculateArguments();

            if (is_callable($this->factory)) {
                return ($this->factory)(...$args);
            }

            $className = $this->factory;
            return new $className(...$args);

        } catch (Throwable $throwable) {
            if ($throwable instanceof CircularDependencyException) {
                /** @noinspection PhpUnhandledExceptionInspection */
                throw $throwable;
            }
            throw new InvalidObjectConfigurationException(
                $throwable->getMessage(),
                $throwable->getCode(),
                $throwable
            );
        }
    }

    /**
     * Calculate the arguments for the enclosed object.
     *
     * @return mixed[]
     *
     * @throws ItemNotFoundException
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    protected function calculateArguments(): array
    {
        if (empty($this->config)) {
            return [];
        }

        if ($this->buildIsInProgress) {
            throw new CircularDependencyException();
        }

        $this->buildIsInProgress = true;

        $args = [];

        foreach ($this->config as $key => $item) {
            if (!is_string($key)) {
                if (!is_string($item) || !$this->dependencyResolver->hasFromAll($item)) {
                    $args[] = $item;
                    continue;
                }
                $key = $item;
            }

            $args[] = $this->dependencyResolver->getFromAll($key);
        }

        $this->buildIsInProgress = false;

        return $args;
    }

    /**
     * @return DependencyResolver
     */
    public function getDependencyResolver(): DependencyResolver
    {
        return $this->dependencyResolver;
    }
}
