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

/**
 * @inheritDoc
 */
class DependencyObject implements interfaces\DependencyObjectInterface
{
    protected $dc;
    protected $base;
    protected $name;
    protected $config;
    protected $instance;

    /**
     * @inheritDoc
     */
    public function __construct(string $name, $config, DependencyContainer $dc, DependencyContainer $base)
    {
        $this->dc = $dc;
        $this->base = $base;
        $this->name = $name;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    final public function getInstance(): object
    {
        if (!isset($this->instance)) {
            $this->instance = $this->makeInstance();
        }

        return $this->instance;
    }

    /**
     * @inheritDoc
     */
    final public function makeInstance(): object
    {
        if ($this->config instanceof \Closure) {
            return ($this->config)();
        }

        if (\count($this->config) === 0) {
            return new $this->name();
        }

        return new $this->name(...$this->calculateArguments($this->config));
    }

    /**
     * Calculate the arguments for the enclosed object.
     *
     * @param array $config
     * @return array
     */
    private function calculateArguments(array $config): array
    {
        $args = [];

        foreach ($config as $key => $item)
        {
            if (\is_string($item) && \class_exists($item)) {
                $args[] = $this->base->get($item);
                continue;
            }

            if (\is_string($key) && \class_exists($key)) {
                $this->dc->set($key, $item);
                $args[] = $this->dc->get($key);
                continue;    
            }

            $args[] = $item;
        }

        return $args;
    }
}
