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

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Hint: Define your services public to make this work.
 */
class SymfonyResolver extends DependencyResolver
{
    /** @var ContainerInterface */
    protected static $container;

    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }

    public static function get(string $fQCN): object
    {
        if (isset(self::$decorator[$fQCN])) {
            return static::get(self::$decorator[$fQCN]);
        }

        if (isset(self::$mock[$fQCN])) {
            return self::$mock[$fQCN];
        }

        if (self::$container->has($fQCN)) {
            return self::$container->get($fQCN);
        }

        if (!isset(self::$instance[$fQCN])) {
            self::$instance[$fQCN] = static::make($fQCN);
        }

        return self::$instance[$fQCN];
    }

    public static function has(string $fQCN): bool
    {
        if (self::$container->has($fQCN)) {
            return true;
        }

        return parent::has($fQCN);
    }
}