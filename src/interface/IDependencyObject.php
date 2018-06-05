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
 * Wrapper interface needed to abstract the DependencyContainer from the
 * enclosed objects
 */
interface IDependencyObject
{
    /**
     * Wrappes the object.
     *
     * @param string $name The classname of the enclosed object.
     * @param array $config The configuration for the enclosed object.
     * @param IDependencyContainer $dc The DependencyContainer in charge of
     * resolving the dependencies of the enclosed object. (This is needed for lazy
     * loading.)
     */
    public function __construct(string $name, array $config, IDependencyContainer $dc);

    /**
     * Get an instance of the enclosed object.
     *
     * @return object
     */
    public function getInstance(): object;

    /**
     * Get a **new** instance of the enclosed object.
     *
     * @return object
     */
    public function makeInstance(): object;
}
