<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/di
 * @link https://github.com/Koudela/earc-di/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\di\interfaces;

use eArc\di\DependencyContainer;

/**
 * Wrapper interfaces needed to abstract the DependencyContainer from the
 * enclosed objects
 */
interface DependencyObjectInterface
{
    /**
     * Wraps the object.
     *
     * @param string $name The class name of the enclosed object.
     * @param array $config The configuration for the enclosed object.
     * @param DependencyContainer $dc The DependencyContainer in charge of
     * resolving the dependencies of the enclosed object. (This is needed for
     * lazy loading.)
     * @param DependencyContainer $base The base of the DependencyContainer in
     * charge of resolving the dependencies of the enclosed object. (This is
     * needed for accessing dependencies encapsulated on a higher level.)
     */
    public function __construct(string $name, array $config, DependencyContainer $dc, DependencyContainer $base);

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
