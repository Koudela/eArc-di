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
 * Generic container class.
 */
class Container implements IContainer
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
        if (!isset($this->data)) throw NotFoundException();
        try {
            return $this->data[$id];
        }
        catch (\Throwable $e) {
            throw ContainerException($e->getMessage(), 0, $e);
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
    public function merge(Container $container, bool $overwrite=false)
    {
        foreach ($container->getKeys() as $key) {
            if ($this->has($key) && !$overwrite) continue;
            $this->set($container);
        }
    }
}
