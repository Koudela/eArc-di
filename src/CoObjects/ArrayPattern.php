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

namespace eArc\DI\CoObjects;

class ArrayPattern
{
    public static function has(array $keys, array $array): bool
    {
        $key = array_shift($keys);

        if (empty($keys)) {
            return isset($array[$key]);
        }

        if (!isset($array[$key]) || !is_array($array[$key])) {
            return false;
        }

        return self::has($keys, $array[$key]);
    }

    public static function get(array $keys, array $array)
    {
        $key = array_shift($keys);

        if (empty($keys)) {
            return $array[$key];
        }

        return self::get($keys, $array[$key]);
    }

    public static function set(array $keys, $value, array &$array): void
    {
        $key = array_shift($keys);

        if (empty($keys)) {
            $array[$key] = $value;
        }

        if (!isset($array[$key]) || !is_array($array[$key])) {
            $array[$key] = [];
        }

        self::set($keys, $value, $array[$key]);
    }
}