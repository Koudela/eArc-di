<?php declare(strict_types=1);

namespace eArc\DI\CoObjects;

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
        if (!self::has($key)) {
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
            ArrayPattern::set(explode('.', $key), $value, self::$parameter);
        } else {
            self::$parameter[$key] = $value;
        }
    }

    public static function import(array $additionalParameter): void
    {
        self::$parameter = array_replace_recursive(self::$parameter, $additionalParameter);
    }
}
