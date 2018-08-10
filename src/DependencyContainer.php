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
 * Container with support of lazy dependency injection.
 */
class DependencyContainer extends Container implements interfaces\DependencyContainerUsageInterface, interfaces\DependencyContainerConfigurationInterface
{
    private $base;

    public function __construct(?DependencyContainer $dc = null)
    {
        parent::__construct();
        $this->base = ($dc === null ? $this : $dc->base);
    }

    /**
     * @inheritDoc
     */
    public function load(array $config): void
    {
        foreach ($config as $className => $conf)
        {
            $this->set($className, $conf);
        }
    }

    /**
     * @inheritDoc
     */
    public function loadFile(string $filename): void
    {
        $this->load(require $filename);
    }

    /**
     * @inheritDoc
     */
    public function set(string $id, $config): void
    {
        if (parent::has($id)) {
            \trigger_error('Overwrite of existing $id', E_USER_WARNING);
        }

        parent::set($id, new DependencyObject($id, $config, $this, $this->base));
        
    }

    /**
     * @inheritDoc
     */
    public function get($id): object
    {
        return parent::get($id)->getInstance();
    }

    /**
     * @inheritDoc
     */
    public function make($id): object
    {
        return parent::get($id)->makeInstance();        
    }
}
