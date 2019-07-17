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
    use eArc\DI\Exceptions\DIException;
    use eArc\DI\Interfaces\DICallableInterface;
    use eArc\DI\CoObjects\ParameterBag;

    if (!function_exists('di_get')) {
        /**
         * @param string $fQCN
         * @return object
         * @throws DIException
         */
        function di_get(string $fQCN): object
        {
            return DependencyResolver::get($fQCN);
        }
    }

    if (!function_exists('di_make')) {
        /**
         * @param string $fQCN
         * @return object
         * @throws DIException
         */
        function di_make(string $fQCN): object
        {
            return DependencyResolver::make($fQCN);
        }
    }

    if (!function_exists('di_has')) {
        /**
         * @param string $fQCN
         * @return bool
         */
        function di_has(string $fQCN): bool
        {
            return DependencyResolver::has($fQCN);
        }
    }

    if (!function_exists('di_clear_cache')) {
        /**
         * @param string|null $fQCN
         */
        function di_clear_cache(string $fQCN = null): void
        {
            DependencyResolver::clearCache($fQCN);
        }
    }

    if (!function_exists('di_decorate')) {
        /**
         * @param string $fQCN
         * @param string $fQCNReplacement
         * @throws DIException
         */
        function di_decorate(string $fQCN, string $fQCNReplacement): void
        {
            DependencyResolver::decorate($fQCN, $fQCNReplacement);
        }
    }

    if (!function_exists('di_is_decorated')) {
        /**
         * @param string $fQCN
         * @return bool
         * @throws DIException
         */
        function di_is_decorated(string $fQCN): bool
        {
            return DependencyResolver::isDecorated($fQCN);
        }
    }

    if (!function_exists('di_get_decorator')) {
        /**
         * @param string $fQCN
         * @return string
         * @throws DIException
         */
        function di_get_decorator(string $fQCN): string
        {
            return DependencyResolver::getDecorator($fQCN);
        }
    }

    if (!function_exists('di_register_callable')) {
        /**
         * @param DICallableInterface $callable
         */
        function di_register_callable(DICallableInterface $callable): void
        {
            DependencyResolver::registerCallable($callable);
        }
    }

    if (!function_exists('di_has_registered_callables')) {
        /**
         * @param string|null $fQCN
         * @param array $tags
         * @return bool
         */
        function di_has_registered_callables(string $fQCN = null, array $tags = []): bool
        {
            return DependencyResolver::hasRegisteredCallables($fQCN, $tags);
        }
    }

    if (!function_exists('di_execute_callables')) {
        /**
         * @param object $class
         * @param string $fQCN
         * @param array $tags
         * @throws DIException
         */
        function di_execute_callables(object $class, string $fQCN, array $tags = []): void
        {
            DependencyResolver::executeCallables($class, $fQCN, $tags);
        }
    }

    if (!function_exists('di_get_iterable_for_registered_callables')) {
        /**
         * @param string|null $fQCN
         * @param array $tags
         * @return iterable
         */
        function di_get_iterable_for_registered_callables(string $fQCN = null, array $tags = []): iterable
        {
            return DependencyResolver::getIterableForRegisteredCallables($fQCN, $tags);
        }
    }

    if (!function_exists('di_clear_registered_callables')) {
        /**
         * @param string|null $fQCN
         * @param array $tags
         */
        function di_clear_registered_callables(string $fQCN = null, array $tags = []): void
        {
            DependencyResolver::clearRegisteredCallables($fQCN, $tags);
        }
    }

    if (!function_exists('di_mock')) {
        /**
         * @param string $fQCN
         * @param object $mock
         */
        function di_mock(string $fQCN, object $mock): void
        {
            DependencyResolver::mock($fQCN, $mock);
        }
    }

    if (!function_exists('di_clear_mock')) {
        /**
         * @param string $fQCN
         */
        function di_clear_mock(string $fQCN): void
        {
            DependencyResolver::clearMock($fQCN);
        }
    }

    if (!function_exists('di_param')) {
        /**
         * @param string $key
         * @return mixed
         * @throws DIException
         */
        function di_param(string $key)
        {
            return ParameterBag::get($key);
        }
    }

    if (!function_exists('di_set_param')) {
        /**
         * @param string $key
         * @param $value
         */
        function di_set_param(string $key, $value): void
        {
            ParameterBag::set($key, $value);
        }
    }

    if (!function_exists('di_has_param')) {
        /**
         * @param string $key
         * @return bool
         */
        function di_has_param(string $key): bool
        {
            return ParameterBag::has($key);
        }
    }

    if (!function_exists('di_import_param')) {
        /**
         * @param array $additionalParameter
         */
        function di_import_param(array $additionalParameter): void
        {
            ParameterBag::import($additionalParameter);
        }
    }
}
