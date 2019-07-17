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

use eArc\DI\Exceptions\NotFoundDIException;
use eArc\DI\Exceptions\ExecuteCallableDIException;
use eArc\DI\Exceptions\MakeClassDIException;
use eArc\DI\Interfaces\DICallableInterface;
use eArc\DI\Interfaces\ResolverInterface;
use Exception;

abstract class DependencyResolver implements ResolverInterface
{
    protected static $instance = [];
    protected static $decorator = [];
    protected static $callables = [];
    protected static $mock = [];

    public static function get(string $fQCN): object
    {
        if (isset(self::$decorator[$fQCN])) {
            return self::get(self::$decorator[$fQCN]);
        }

        if (isset(self::$mock[$fQCN])) {
            return self::$mock[$fQCN];
        }

        if (!isset(self::$instance[$fQCN])) {
            self::$instance[$fQCN] = self::make($fQCN);
        }

        return self::$instance[$fQCN];
    }

    public static function make(string $fQCN): object
    {
        if (isset(self::$decorator[$fQCN])) {
            return self::make(self::$decorator[$fQCN]);
        }

        if (!self::has($fQCN)) {
            throw new NotFoundDIException(sprintf('%s is no fully qualified class name.', $fQCN));
        }

        if (isset(self::$mock[$fQCN])) {
            return self::$mock[$fQCN];
        }

        try {
            $class = new $fQCN();
        } catch (Exception $e) {
            throw new MakeClassDIException($e->getMessage(), $e->getCode(), $e);
        }
            self::executeCallables($class, $fQCN);

            return $class;
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
        if (!self::has($fQCN)) {
            throw new NotFoundDIException(sprintf('%s is no fully qualified class name.', $fQCN));
        }

        if (!self::has($fQCNReplacement)) {
            throw new NotFoundDIException(sprintf('%s is no fully qualified class name.', $fQCNReplacement));
        }

        self::$decorator[$fQCN] = $fQCNReplacement;
        unset(self::$instance[$fQCN]);
    }

    public static function isDecorated(string $fQCN): bool
    {
        if (!self::has($fQCN)) {
            throw new NotFoundDIException(sprintf('%s is no fully qualified class name.', $fQCN));
        }

        return isset(self::$decorator[$fQCN]);
    }

    public static function getDecorator(string $fQCN): string
    {
        if (!self::isDecorated($fQCN)) {
            throw new NotFoundDIException(sprintf('%s is not a decorated class.', $fQCN));
        }

        return self::$decorator[$fQCN];
    }

    public static function registerCallable(DICallableInterface $callable): void
    {
        self::$callables[$callable->getClassName()][] = $callable;
    }

    public static function hasRegisteredCallables(string $fQCN=null, array $tags=[]): bool
    {
        if (empty($tags)) {
            return null !== $fQCN ? isset(self::$callables[$fQCN]) : !empty(self::$callables);
        }

        $callables = null !== $fQCN ? [self::$callables[$fQCN]] : self::$callables;

        foreach ($callables as $classCallables) {
            foreach ($classCallables as $callable) {
                if ($callable->isTaggedBy($tags)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function executeCallables(object $class, string $fQCN, array $tags=[]): void
    {
        if (!isset(self::$callables[$fQCN])) {
            return;
        }

        try {
            foreach (self::$callables[$fQCN] as $callable) {
                if ($callable->isTaggedBy($tags)) {
                    call_user_func($callable, $class, ...$callable->getArguments());
                }
            }
        } catch (Exception $e) {
            throw new ExecuteCallableDIException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public static function getIterableForRegisteredCallables(string $fQCN=null, array $tags=[]): iterable
    {
        $callables = null !== $fQCN ? [self::$callables[$fQCN]] : self::$callables;

        foreach ($callables as $classCallables) {
            foreach ($classCallables as $callable) {
                if ($callable->isTaggedBy($tags)) {
                        yield $callable;
                }
            }
        }
    }

    public static function clearRegisteredCallables(string $fQCN=null, array $tags=[]): void
    {
        if (empty($tags)) {
            if (null !== $fQCN) {
                unset(self::$callables[$fQCN]);

                return;
            }

            self::$callables = [];

            return;
        }

        $callables = null !== $fQCN ? [self::$callables[$fQCN]] : self::$callables;

        foreach ($callables as $fQCN => $classCallables) {
            $stillValidCallables = [];
            foreach ($classCallables as $callable) {
                if (!$callable->isTaggedBy($tags)) {
                    $stillValidCallables[] = $callable;
                }
            }
            self::$callables[$fQCN] = $stillValidCallables;
        }
    }

    public static function mock(string $fQCN, object $mock): void
    {
        self::$mock[$fQCN] = $mock;
    }

    public static function clearMock(string $fQCN): void
    {
        unset(self::$mock[$fQCN]);
    }
}
