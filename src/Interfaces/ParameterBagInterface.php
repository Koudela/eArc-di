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

use eArc\DI\Exceptions\NotFoundDIException;

/**
 * Describes the interface of a static parameter bag. Methods supporting the dot
 * syntax transform a dotted key e.g. 'alpha.beta.gamma' to a multidimensional array
 * key ['alpha']['beta']['gamma'].
 */
interface ParameterBagInterface
{
    /**
     * Retrieves a parameter from the bag. `get` supports the dot syntax.
     *
     * @param string $key
     *
     * @return mixed
     * @throws NotFoundDIException The parameter does not exist.
     *
     */
    public static function get(string $key);

    /**
     * Checks whether a parameter exists. `has` supports the dot syntax.
     *
     * @param string $key
     *
     * @return bool
     */
    public static function has(string $key): bool;

    /**
     * Sets a parameter of the parameter bag. `set` supports the dot syntax.
     *
     * @param string $key
     *
     * @param $value
     */
    public static function set(string $key, $value): void;

    /**
     * Imports a multidimensional array with parameters. Already existing parameters
     * are overwritten if they have the same $key.
     *
     * @param array $additionalParameter
     */
    public static function import(array $additionalParameter): void;
}