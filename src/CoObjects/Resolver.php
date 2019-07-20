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
use eArc\DI\Exceptions\MakeClassException;
use eArc\DI\Interfaces\ResolverInterface;
use Exception;

abstract class Resolver implements ResolverInterface
{
    protected static $instance = [];
    protected static $decorator = [];
    protected static $tags = [];
    protected static $mock = [];

    public static function get(string $fQCN): object
    {
        $decorator = static::resolve($fQCN);

        if (isset(self::$mock[$decorator])) {
            return self::$mock[$decorator];
        }

        static::checkTypeHint($fQCN, $decorator);

        if (!isset(self::$instance[$decorator])) {
            self::$instance[$decorator] = static::makeDirect($decorator);
        }

        return self::$instance[$decorator];
    }

    public static function make(string $fQCN): object
    {
        $decorator = static::resolve($fQCN);

        if (isset(self::$mock[$decorator])) {
            return self::$mock[$decorator];
        }

        static::checkTypeHint($fQCN, $decorator);

        return static::makeDirect($decorator);
    }

    /**
     * @param string $typeHint
     * @param string $decorator
     * @throws InvalidArgumentException
     */
    protected static function checkTypeHint(string $typeHint, string $decorator)
    {
        if (self::isDecorated($typeHint) &&!is_subclass_of($decorator, $typeHint)) {
            throw new InvalidArgumentException(sprintf('Decorator %s violates type hint %s,', $decorator, $typeHint));
        }
    }

    /**
     * @param string $fQCN
     * @return mixed
     * @throws MakeClassException
     */
    protected static function makeDirect(string $fQCN)
    {
        if (!class_exists($fQCN)) {
            throw new MakeClassException(sprintf('No class found for %s.', $fQCN));
        }
        try {
            return new $fQCN();
        } catch (Exception $e) {
            throw new MakeClassException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public static function resolve(string $fQCN): string
    {
        return isset(self::$decorator[$fQCN]) ? static::resolve(self::$decorator[$fQCN]) : $fQCN;
    }

    public static function has(string $fQCN): bool
    {
        return class_exists($fQCN) || interface_exists($fQCN);
    }

    public static function clearCache(string $fQCN=null): void
    {
        if (null === $fQCN) {
            self::$instance = [];
        } else {
            unset(self::$instance[$fQCN]);
        }
    }

    public static function decorate(string $fQCN, string $fQCNReplacement): void
    {
        if ($fQCN === $fQCNReplacement) {
            unset(self::$decorator[$fQCN]);

            return;
        }

        self::$decorator[$fQCN] = $fQCNReplacement;
    }

    public static function isDecorated(string $fQCN): bool
    {
        return isset(self::$decorator[$fQCN]);
    }

    public static function getDecorator(string $fQCN): ?string
    {
        return isset(self::$decorator[$fQCN]) ? self::$decorator[$fQCN] : null;
    }

    public static function tag(string $fQCN, string $name): void
    {
        self::$tags[$name][$fQCN] = true;
    }

    public static function getTagged(string $name): iterable
    {
        $iterate = self::$tags[$name] ?? [];

        foreach ($iterate as $fQCN => $value) {
            yield $fQCN;
        }
    }

    public static function clearTags(string $name, string $fQCN=null): void
    {
        if (null === $fQCN) {
            unset(self::$tags[$name]);
        } else {
            unset(self::$tags[$name][$fQCN]);
        }
    }

    public static function mock(string $fQCN, object $mock): void
    {
        self::$mock[$fQCN] = $mock;
    }

    public static function isMocked(string $fQCN): bool
    {
        return isset(self::$mock[$fQCN]);
    }

    public static function clearMock(string $fQCN=null): void
    {
        if (null === $fQCN) {
            self::$mock = [];
        } else {
            unset(self::$mock[$fQCN]);
        }
    }
}
