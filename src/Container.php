<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/di
 * @link https://github.com/Koudela/earc-di/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\di;

use eArc\di\interfaces\ContainerInterface;
use eArc\di\exceptions\NotFoundException;
use eArc\di\exceptions\ContainerException;

/**
 * Generic container class.
 */
class Container implements ContainerInterface
{
    private $data;

    public function __construct()
    {
        $this->data = [];
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        if (!isset($this->data)) throw new NotFoundException();

        try {
            return $this->data[$id];
        }
        catch (\Throwable $e) {
            throw new ContainerException($e->getMessage(), 0, $e);
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
     * @inheritDoc
     */
    public function set(string $id, $payload): void
    {
        $this->data[$id] = $payload;
    }

    /**
     * @inheritDoc
     */
    public function getKeys(): array
    {
        return array_keys($this->data, null, true);
    }

    /**
     * @inheritDoc
     */
    public function merge(ContainerInterface $container, bool $overwrite=false)
    {
        foreach ($container->getKeys() as $key) {
            if ($this->has($key) && !$overwrite) continue;
            $this->set($key, $container->get($key));
        }
    }
}
