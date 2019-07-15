<?php declare(strict_types=1);

namespace eArc\DI;

use RuntimeException;

abstract class ParameterBag
{
    protected static $parameter = [];

    public static function has(string $name): bool
    {
        return isset(self::$parameter[$name]);
    }

    public static function get(string $name)
    {
        if (!self::has($name)) {
            throw new RuntimeException(sprintf('Parameter %s was never added to the parameter bag.', $name));
        }

        return self::$parameter[$name];
    }

    public static function set(string $name, $value): void
    {
        self::$parameter[$name] = $value;
    }

    public static function import(array $additionalParameter): void
    {
        self::$parameter = array_replace_recursive(self::$parameter, $additionalParameter);
    }
}
