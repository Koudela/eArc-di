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

namespace eArc\DI {

    abstract class DI
    {
        public static function init()
        {
        }
    }
}

namespace {

    use eArc\DI\CoObjects\DependencyResolver;
    use eArc\DI\Interfaces\DICallableInterface;
    use eArc\DI\CoObjects\ParameterBag;

    function di_get(string $fQCN): object
    {
        return DependencyResolver::get($fQCN);
    }

    function di_make(string $fQCN): object
    {
        return DependencyResolver::make($fQCN);
    }

    function di_has(string $fQCN): bool
    {
        return DependencyResolver::has($fQCN);
    }

    function di_clear_cache(string $fQCN = null): void
    {
        DependencyResolver::clearCache($fQCN);
    }

    function di_decorate(string $fQCN, string $fQCNReplacement): void
    {
        DependencyResolver::decorate($fQCN, $fQCNReplacement);
    }

    function di_is_decorated(string $fQCN): bool
    {
        return DependencyResolver::isDecorated($fQCN);
    }

    function di_get_decorator(string $fQCN): string
    {
        return DependencyResolver::getDecorator($fQCN);
    }

    function di_register_callable(DICallableInterface $callable): void
    {
        DependencyResolver::registerCallable($callable);
    }

    function di_has_registered_callables(string $fQCN = null, array $tags = []): bool
    {
        return DependencyResolver::hasRegisteredCallables($fQCN, $tags);
    }

    function di_execute_callables(object $class, string $fQCN, array $tags = []): void
    {
        DependencyResolver::executeCallables($class, $fQCN, $tags);
    }

    function di_get_iterable_for_registered_callables(string $fQCN=null, array $tags=[]): iterable
    {
        return DependencyResolver::getIterableForRegisteredCallables($fQCN, $tags);
    }

    function di_clear_registered_callables(string $fQCN=null, array $tags=[]): void
    {
        DependencyResolver::clearRegisteredCallables($fQCN, $tags);
    }

    function di_mock(string $fQCN, object $mock): void
    {
        DependencyResolver::mock($fQCN, $mock);
    }

    function di_clear_mock(string $fQCN): void
    {
        DependencyResolver::clearMock($fQCN);
    }

    function di_param(string $key)
    {
        return ParameterBag::get($key);
    }

    function di_set_param(string $key, $value): void
    {
        ParameterBag::set($key, $value);
    }

    function di_has_param(string $key): bool
    {
        return ParameterBag::has($key);
    }

    function di_import_param(array $additionalParameter): void
    {
        ParameterBag::import($additionalParameter);
    }
}
