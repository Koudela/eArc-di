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
 * Generic container interface.
 */
interface IContainer extends \Psr\Container\ContainerInterface {
    
    /**
     * Set/Overwrite an entry for a specific $id.
     *
     * @param string $id Identifier of the entry.
     * @param mixed $payload The (new) entry for the given $id.
     * @return void
     */
    public function set(string $id, $payload): void;

    /**
     * Get the existing ids.
     *
     * @return array
     */
    public function getKeys(): array;

    /**
     * Merge a container object into the container. 
     *
     * @param \eArc\di\Container $container
     * @param bool $overwrite If true this method overwrites an existing entry,
     * otherwise the new entry is droped. 
     * @return void
     */
    public function merge(Container $container, bool $overwrite=false);
}
