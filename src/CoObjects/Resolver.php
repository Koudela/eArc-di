<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * dependency injection component
 *
 * @package earc/di
 * @link https://github.com/Koudela/eArc-di/
 * @copyright Copyright (c) 2018-2020 Thomas Koudela
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
    protected static $namespaceDecorators;

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
        if (self::isDecorated($typeHint) && !is_subclass_of($decorator, $typeHint)) {
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
        if ($decorator = static::getDecorator($fQCN))
        {
            return static::resolve($decorator);
        }

        return $fQCN;
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
        if (isset(self::$decorator[$fQCN])) {
            return true;
        }

        if (null != self::$namespaceDecorators) {
            return null !== static::matchDecoratedNamespace($fQCN);
        }

        return false;
    }

    public static function getDecorator(string $fQCN): ?string
    {
        if (isset(self::$decorator[$fQCN])) {
            return self::$decorator[$fQCN];
        }

        if (null != self::$namespaceDecorators) {
            return static::matchDecoratedNamespace($fQCN);
        }

        return null;
    }

    protected static function matchDecoratedNamespace(string $fQCN): ?string
    {
        foreach (self::$namespaceDecorators as list($namespaceKey, $namespaceValue)) {
            if (0 === strpos($fQCN, $namespaceKey)) {
                $className = $namespaceValue.substr($fQCN, strlen($namespaceKey));

                if (class_exists($className)) {
                    return $className;
                }
            }
        }

        return null;
    }

    public static function batchDecorate(array $settings): void
    {
        self::$decorator += $settings;
    }

    public static function addNamespaceDecoration(array $settings): void
    {
        if (null === self::$namespaceDecorators) {
            self::$namespaceDecorators = [];
        }

        self::$namespaceDecorators += $settings;
    }

    public static function tag(string $fQCN, string $name, $argument=null): void
    {
        self::$tags[$name][$fQCN] = $argument;
    }

    public static function getTagged(string $name): iterable
    {
        $iterate = self::$tags[$name] ?? [];

        foreach ($iterate as $fQCN => $argument) {
            yield $fQCN => $argument;
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
