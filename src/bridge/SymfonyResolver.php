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
class SymfonyResolver extends Resolver
{
    /** @var ContainerInterface */
    protected static $container;

    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }

    public static function get(string $fQCN): object
    {
        $decorator = static::resolveDecoratorChain($fQCN);

        if (isset(self::$mock[$decorator])) {
            return self::$mock[$decorator];
        }

        static::checkTypeHint($fQCN, $decorator);

        if (self::$container->has($decorator)) {
            return self::$container->get($decorator);
        }

        if (!isset(self::$instance[$decorator])) {
            self::$instance[$decorator] = static::makeDirect($decorator);
        }

        return self::$instance[$decorator];
    }

    public static function has(string $fQCN): bool
    {
        if (self::$container->has($fQCN)) {
            return true;
        }

        return parent::has($fQCN);
    }
}