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

use eArc\DI\Exceptions\MakeClassException;
use eArc\DI\Interfaces\ResolverInterface;
use Exception;

abstract class DependencyResolver implements ResolverInterface
{
    protected static $instance = [];
    protected static $decorator = [];
    protected static $tags = [];
    protected static $mock = [];

    public static function get(string $fQCN): object
    {
        if (isset(self::$decorator[$fQCN])) {
            return static::get(self::$decorator[$fQCN]);
        }

        if (isset(self::$mock[$fQCN])) {
            return self::$mock[$fQCN];
        }

        if (!isset(self::$instance[$fQCN])) {
            self::$instance[$fQCN] = static::make($fQCN);
        }

        return self::$instance[$fQCN];
    }

    public static function make(string $fQCN): object
    {
        if (isset(self::$decorator[$fQCN])) {
            return static::make(self::$decorator[$fQCN]);
        }

        if (isset(self::$mock[$fQCN])) {
            return self::$mock[$fQCN];
        }

        try {
            return new $fQCN();
        } catch (Exception $e) {
            throw new MakeClassException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public static function has(string $fQCN): bool
    {
        return class_exists($fQCN);
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
        return @self::$decorator[$fQCN];
    }

    public static function tag(string $fQCN, string $name): void
    {
        self::$tags[$name][$fQCN] = true;
    }

    public static function getTagged(string $name): iterable
    {
        foreach (self::$tags[$name] as $fQCN => $value) {
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
