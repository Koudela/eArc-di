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

class DependencyObject
{
    protected $dc;
    protected $name;
    protected $config;
    protected $instance;

    public function __construct(string $name, $config, \eArc\di\DependencyContainer $dc)
    {
        $this->dc = $dc;
        $this->name = $name;
        $this->config = $config;
    }

    /**
     * Get an instance of the enclosed object
     *
     * @return object
     */
    public function getInstance(): object
    {
        if (!isset($this->instance))
            $this->instance = $this->makeInstance();

        return $this->instance;
    }

    /**
     * Get a new instance of the enclosed object
     *
     * @return object
     */
    public function makeInstance(): object {
        if (is_object($this->config) && ($this->config instanceof \Closure))
            return ($this->config)();

        if (\count($this->config) === 0)
            return new $this->name();

        $dc = new \eArc\di\DependencyContainer($this->dc);
        return new $this->name(...$dc->calculateArguments($this->config));
    }
}
