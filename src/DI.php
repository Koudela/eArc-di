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

    use eArc\DI\CoObjects\DependencyResolver;
    use eArc\DI\CoObjects\ParameterBag;
    use eArc\DI\Exceptions\DIException;
    use BootstrapEArcDI;

    abstract class DI
    {
        /**
         * @param string $resolver
         * @param string $parameterBag
         *
         * @throws DIException
         */
        public static function init(string $resolver=DependencyResolver::class, string $parameterBag=ParameterBag::class): void
        {
            BootstrapEArcDI::init($resolver, $parameterBag);
        }
    }
}

namespace {

    use eArc\DI\CoObjects\DependencyResolver;
    use eArc\DI\Exceptions\DIException;
    use eArc\DI\Interfaces\DICallableInterface;
    use eArc\DI\CoObjects\ParameterBag;
    use eArc\DI\Interfaces\ParameterBagInterface;
    use eArc\DI\Interfaces\ResolverInterface;

    abstract class BootstrapEArcDI
    {
        /** @var ResolverInterface */
        protected static $resolver;

        /** @var ParameterBagInterface */
        protected static $parameterBag;

        public static function getResolver()
        {
            return self::$resolver;
        }

        public static function getParameterBag()
        {
            return self::$parameterBag;
        }

        /**
         * @param string $resolver
         * @param string $parameterBag
         *
         * @throws DIException
         */
        public static function init(string $resolver=DependencyResolver::class, string $parameterBag=ParameterBag::class): void
        {
            if (!is_subclass_of($resolver, ResolverInterface::class)) {
                throw new DIException(sprintf('Resolver has to implement %s.', ResolverInterface::class));
            }

            self::$resolver = $resolver;

            if (!is_subclass_of($parameterBag, ParameterBagInterface::class)) {
                throw new DIException(sprintf('ParameterBag has to implement %s.', ParameterBagInterface::class));
            }

            self::$parameterBag = $parameterBag;

            if (!function_exists('di_get')) {
                /**
                 * @param string $fQCN
                 * @return object
                 */
                function di_get(string $fQCN): object
                {
                    return BootstrapEArcDI::getResolver()::get($fQCN);
                }
            }

            if (!function_exists('di_make')) {
                /**
                 * @param string $fQCN
                 * @return object
                 */
                function di_make(string $fQCN): object
                {
                    return BootstrapEArcDI::getResolver()::make($fQCN);
                }
            }

            if (!function_exists('di_has')) {
                /**
                 * @param string $fQCN
                 * @return bool
                 */
                function di_has(string $fQCN): bool
                {
                    return BootstrapEArcDI::getResolver()::has($fQCN);
                }
            }

            if (!function_exists('di_clear_cache')) {
                /**
                 * @param string|null $fQCN
                 */
                function di_clear_cache(string $fQCN = null): void
                {
                    BootstrapEArcDI::getResolver()::clearCache($fQCN);
                }
            }

            if (!function_exists('di_decorate')) {
                /**
                 * @param string $fQCN
                 * @param string $fQCNReplacement
                 */
                function di_decorate(string $fQCN, string $fQCNReplacement): void
                {
                    BootstrapEArcDI::getResolver()::decorate($fQCN, $fQCNReplacement);
                }
            }

            if (!function_exists('di_is_decorated')) {
                /**
                 * @param string $fQCN
                 * @return bool
                 */
                function di_is_decorated(string $fQCN): bool
                {
                    return BootstrapEArcDI::getResolver()::isDecorated($fQCN);
                }
            }

            if (!function_exists('di_get_decorator')) {
                /**
                 * @param string $fQCN
                 * @return string
                 */
                function di_get_decorator(string $fQCN): string
                {
                    return BootstrapEArcDI::getResolver()::getDecorator($fQCN);
                }
            }

            if (!function_exists('di_register_callable')) {
                /**
                 * @param DICallableInterface $callable
                 */
                function di_register_callable(DICallableInterface $callable): void
                {
                    BootstrapEArcDI::getResolver()::registerCallable($callable);
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
                    return BootstrapEArcDI::getResolver()::hasRegisteredCallables($fQCN, $tags);
                }
            }

            if (!function_exists('di_execute_callables')) {
                /**
                 * @param object $class
                 * @param string $fQCN
                 * @param array $tags
                 */
                function di_execute_callables(object $class, string $fQCN, array $tags = []): void
                {
                    BootstrapEArcDI::getResolver()::executeCallables($class, $fQCN, $tags);
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
                    return BootstrapEArcDI::getResolver()::getIterableForRegisteredCallables($fQCN, $tags);
                }
            }

            if (!function_exists('di_clear_registered_callables')) {
                /**
                 * @param string|null $fQCN
                 * @param array $tags
                 */
                function di_clear_registered_callables(string $fQCN = null, array $tags = []): void
                {
                    BootstrapEArcDI::getResolver()::clearRegisteredCallables($fQCN, $tags);
                }
            }

            if (!function_exists('di_mock')) {
                /**
                 * @param string $fQCN
                 * @param object $mock
                 */
                function di_mock(string $fQCN, object $mock): void
                {
                    BootstrapEArcDI::getResolver()::mock($fQCN, $mock);
                }
            }

            if (!function_exists('di_clear_mock')) {
                /**
                 * @param string $fQCN
                 */
                function di_clear_mock(string $fQCN): void
                {
                    BootstrapEArcDI::getResolver()::clearMock($fQCN);
                }
            }

            if (!function_exists('di_param')) {
                /**
                 * @param string $key
                 * @return mixed
                 */
                function di_param(string $key)
                {
                    return BootstrapEArcDI::getParameterBag()::get($key);
                }
            }

            if (!function_exists('di_set_param')) {
                /**
                 * @param string $key
                 * @param $value
                 */
                function di_set_param(string $key, $value): void
                {
                    BootstrapEArcDI::getParameterBag()::set($key, $value);
                }
            }

            if (!function_exists('di_has_param')) {
                /**
                 * @param string $key
                 * @return bool
                 */
                function di_has_param(string $key): bool
                {
                    return BootstrapEArcDI::getParameterBag()::has($key);
                }
            }

            if (!function_exists('di_import_param')) {
                /**
                 * @param array $additionalParameter
                 */
                function di_import_param(array $additionalParameter): void
                {
                    BootstrapEArcDI::getParameterBag()::import($additionalParameter);
                }
            }
        }
    }
}
