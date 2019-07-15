<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * dependency injection component
 *
 * @package earc/di
 * @link https://github.com/Koudela/eArc-di/
 * @copyright Copyright (c) 2018-2019 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\DI\Interfaces;

/**
 * Flags manipulate the behaviour of a dependency configuration. They can also
 * be supplied/overwritten by the Flags::class key in the dependency
 * configuration itself. Flags returned by the static function are overwritten
 * by flags defined in the dependency configuration.
 *
 * Dependency configuration example:
 *
 * ...
 * itemKey => [
 *     ...
 *     Flags::class => [
 *         Flags::CLASS_NAME => MyClass::class,
 *         Flags::DO_NOT_RESOLVE => true,
 *         Flags::INSTANT_MAKE => false,
 *         Flags::ITEM_KEY => 'someItemKey'
 *         Flags::SAVE_NO_REFERENCE => true
 *     ]
 *     ...
 * ],
 * ...
 */
interface Flags
{
    /**
     * type: string
     * default: as defined
     * Overwrites the container name under which the item is saved.
     */
    const ITEM_KEY = 0;

    /**
     * type: string
     * default: as defined
     * Instantiated the class by using this class name.
     */
    const CLASS_NAME = 1;

    /**
     * type: bool
     * default: false
     * The configuration does not get resolved and the class not instantiated.
     */
    const DO_NOT_RESOLVE = 2;

    /**
     * type: bool
     * default: false
     * An instance is build on load.
     */
    const INSTANT_MAKE = 3;

    /**
     * type: bool
     * default: false
     * The item is not added to the dependency container. Hint: Use in
     * combination with the INSTANT_MAKE flag.
     */
    const SAVE_NO_REFERENCE = 4;

    /**
     * type: callable
     * default: none
     * The resolved dependencies are passed to the factory instead of using the
     * new operator.
     */
    const FACTORY = 5;

    /**
     * @return mixed[]
     */
    public static function getDependencyInjectionFlags(): array;
}
