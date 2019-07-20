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

namespace eArc\DI\bridge;

use eArc\DI\CoObjects\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class SymfonyParameterBag extends ParameterBag
{
    /** @var ContainerInterface */
    protected static $container;

    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }

    public static function has(string $key): bool
    {
        if (self::$container->hasParameter($key)) {
            return true;
        }

        return parent::has($key);
    }

    public static function get(string $key)
    {
        if (self::$container->hasParameter($key)) {
            return self::$container->getParameter($key);
        }
        return parent::get($key);
    }
}