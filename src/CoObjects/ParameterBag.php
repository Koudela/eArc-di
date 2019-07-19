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

use eArc\DI\Exceptions\InvalidArgumentException;
use eArc\DI\Exceptions\NotFoundException;
use eArc\DI\Interfaces\ParameterBagInterface;

abstract class ParameterBag implements ParameterBagInterface
{
    protected static $parameter = [];

    public static function has(string $key): bool
    {
        if (strpos($key, '.')) {
            return ArrayPattern::has(explode('.', $key), self::$parameter);
        } else {
            return isset(self::$parameter[$key]);
        }
    }

    public static function get(string $key)
    {
        if (!static::has($key)) {
            throw new NotFoundException(sprintf('Parameter %s was never added to the parameter bag.', $key));
        }

        if (strpos($key, '.')) {
            return ArrayPattern::get(explode('.', $key), self::$parameter);
        } else {
            return self::$parameter[$key];
        }
    }

    public static function set(string $key, $value): void
    {
        if (strpos($key, '.')) {
            try {
                ArrayPattern::set(explode('.', $key), $value, self::$parameter);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException(sprintf('Parameter key %s is used already.', $key));
            }
        } else {
            self::$parameter[$key] = $value;
        }
    }

    public static function import(array $additionalParameter): void
    {
        self::$parameter = array_replace_recursive(self::$parameter, $additionalParameter);
    }
}
