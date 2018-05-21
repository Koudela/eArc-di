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
 * No entry was found for the identifier in the container.
 */
class NotFoundException extends \Exception implements \Psr\Container\NotFoundExceptionInterface
{
}

/**
 * Base container exception class
 */
class ContainerException extends \Exception implements \Psr\Container\ContainerExceptionInterface
{
}

class Container implements \Psr\Container\ContainerInterface
{
    protected $data;

    public function __construct()
    {
        $this->data = [];
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        if (!isset($this->data)) throw \eArc\di\NotFoundException();
        try {
            return $this->data[$id];
        }
        catch (\Throwable $e) {
            throw \eArc\di\ContainerException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function has($id): bool
    {
        return isset($this->data[$id]);
    }

    /**
     * Sets/Overwrites an entry for a specific $id.
     *
     * @param string  $id Identifier of the entry.
     * @param [mixed] $payload The (new) entry for the given $id.
     * @return void
     */
    public function set(string $id, $payload): void
    {
        $this->data[$id] = $payload;
    }

    /**
     * Get the existing ids.
     *
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->data, null, true);
    }

    /**
     * Merges a container object into the container. 
     *
     * @param \eArc\di\Container $container
     * @param bool $overwrite If a key is already present: true overwrites the existing entry, false dropes the new entry. 
     * @return void
     */
    public function merge(\eArc\di\Container $container, bool $overwrite=false)
    {
        foreach ($container->getKeys() as $key) {
            if ($this->has($key) && !$overwrite) continue;
            $this->set($container);
        }
    }
}
