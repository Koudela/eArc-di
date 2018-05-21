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

class DependencyContainer extends \eArc\di\Container
{
    private $base;

    public function __construct(?DependencyContainer $dc)
    {
        parent::__construct();
        $this->base = ($dc === null ? $this : $dc->base);
    }

    /**
     * Lazy initialation of the DependencyContainer configuration
     *
     * @param array $config The dependency configuration of the container
     * @return void
     */
    public function load(array $config): void
    {
        foreach ($config as $classname => $conf) {
            $this->set($classname, $conf);
        }
    }

    /**
     * Lazy initialation of the DependencyContainer configuration
     *
     * @param string $filename Location of the configuration file
     * @return void
     */
    public function loadFile(string $filename): void
    {
        $this->load(require $filename);
    }

    /**
     * Set a configuration for the object
     *
     * @param string $id The objects class name
     * @param array $config The dependency configuration of the object
     * @return void
     */
    public function set(string $id, $config): void
    {
        if (parent::has($id)) trigger_error('Overwrite of existing $id', E_USER_WARNING);
        parent::set($id, new \eArc\di\DependencyObject($id, $config, $this));
        
    }

    /**
     * Get an instance of the object
     *
     * @return object
     */
    public function get($id): object
    {
        return parent::get($id)->getInstance();
    }

    /**
     * Get a **new** instance of the object
     *
     * @return object
     */
    public function make($id): object
    {
        return parent::get($id)->makeInstance();        
    }

    /**
     * Calculate the arguments for the object
     *
     * @return array
     */
    public function calculateArguments($config): array
    {
        $args = [];
        foreach ($config as $key => $item) {
            if (\is_string($item) && \class_exists($item)) {
                $args[] = $this->base->get($item);
                continue;
            }
            if (\is_string($key) && \class_exists($key)) {
                $this->set($key, $item);
                $args[] = $this->get($key);
                continue;    
            }
            $args[] = $item;
        }
        return $args;
    }
}
