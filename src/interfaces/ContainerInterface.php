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

/**
 * Generic container interfaces.
 */
interface ContainerInterface extends \Psr\Container\ContainerInterface {
    
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
     * @return array
     */
    public function getKeys(): array;

    /**
     * Merge a container object into the container. 
     *
     * @param ContainerInterface $container
     * @param bool $overwrite If true this method overwrites an existing entry,
     * otherwise the new entry is dropped.
     * @return void
     */
    public function merge(ContainerInterface $container, bool $overwrite=false);
}
