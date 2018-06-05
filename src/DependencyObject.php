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
 * @inheritDoc
 */
class DependencyObject implements IDependencyObject
{
    protected $dc;
    protected $name;
    protected $config;
    protected $instance;

    /**
     * @inheritDoc
     */
    public function __construct(string $name, array $config, DependencyContainer $dc)
    {
        $this->dc = $dc;
        $this->name = $name;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getInstance(): object
    {
        if (!isset($this->instance))
            $this->instance = $this->makeInstance();

        return $this->instance;
    }

    /**
     * @inheritDoc
     */
    public function makeInstance(): object {
        if ($this->config instanceof \Closure)
            return ($this->config)();

        if (\count($this->config) === 0)
            return new $this->name();

        $dc = new DependencyContainer($this->dc);
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
        foreach ($config as $key => $item) {
            if (\is_string($item) && \class_exists($item)) {
                $args[] = $this->dc->base->get($item);
                continue;
            }
            if (\is_string($key) && \class_exists($key)) {
                $this->set($key, $item);
                $args[] = $this->dc->get($key);
                continue;    
            }
            $args[] = $item;
        }
        return $args;
    }
}
