<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * dependency injection component
 *
 * @package earc/di
 * @link https://github.com/Koudela/eArc-di/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\DI {

    use eArc\DI\CoObjects\Resolver;
    use eArc\DI\CoObjects\ParameterBag;
    use eArc\DI\Exceptions\InvalidArgumentException;
    use BootstrapEArcDI;
    use eArc\DI\Exceptions\NotFoundException;

    abstract class DI
    {
        const PARAM_KEY_DECORATION_CLASS = 'earc.di.class_decoration';
        const PARAM_KEY_DECORATION_NAMESPACE = 'earc.di.namespace_decoration';

        /**
         * @param string $resolver
         * @param string $parameterBag
         *
         * @throws InvalidArgumentException
         */
        public static function init(string $resolver=Resolver::class, string $parameterBag=ParameterBag::class): void
        {
            BootstrapEArcDI::init($resolver, $parameterBag);
        }

        public static function importParameter(): void
        {
            try {
                BootstrapEArcDI::getResolver()::batchDecorate(
                    BootstrapEArcDI::getParameterBag()::get(DI::PARAM_KEY_DECORATION_CLASS)
                );
            } catch (NotFoundException $exception) {
                // do nothing
            }

            try {
                BootstrapEArcDI::getResolver()::addNamespaceDecoration(
                    BootstrapEArcDI::getParameterBag()::get(DI::PARAM_KEY_DECORATION_NAMESPACE)
                );
            } catch (NotFoundException $exception) {
                // do nothing
            }
        }
    }
}

namespace {

    use eArc\DI\CoObjects\Resolver;
    use eArc\DI\Exceptions\InvalidArgumentException;
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
         * @throws InvalidArgumentException
         */
        public static function init(string $resolver=Resolver::class, string $parameterBag=ParameterBag::class): void
        {
            if (!is_subclass_of($resolver, ResolverInterface::class)) {
                throw new InvalidArgumentException(sprintf('Resolver %s has to implement %s.', $resolver, ResolverInterface::class));
            }

            self::$resolver = $resolver;

            if (!is_subclass_of($parameterBag, ParameterBagInterface::class)) {
                throw new InvalidArgumentException(sprintf('ParameterBag %s has to implement %s.', $parameterBag, ParameterBagInterface::class));
            }

            self::$parameterBag = $parameterBag;

            if (!function_exists('di_get')) {
                function di_get(string $fQCN): object
                {
                    return BootstrapEArcDI::getResolver()::get($fQCN);
                }
            }

            if (!function_exists('di_make')) {
                function di_make(string $fQCn): object
                {
                    return BootstrapEArcDI::getResolver()::make($fQCn);
                }
            }

            if (!function_exists('di_static')) {
                function di_static(string $fQCn): string
                {
                    return BootstrapEArcDI::getResolver()::resolve($fQCn);
                }
            }
            if (!function_exists('di_has')) {
                function di_has(string $fQCN): bool
                {
                    return BootstrapEArcDI::getResolver()::has($fQCN);
                }
            }

            if (!function_exists('di_clear_cache')) {
                function di_clear_cache(string $fQCN = null): void
                {
                    BootstrapEArcDI::getResolver()::clearCache($fQCN);
                }
            }

            if (!function_exists('di_decorate')) {
                function di_decorate(string $fQCN, string $fQCNReplacement): void
                {
                    BootstrapEArcDI::getResolver()::decorate($fQCN, $fQCNReplacement);
                }
            }

            if (!function_exists('di_is_decorated')) {
                function di_is_decorated(string $fQCN): bool
                {
                    return BootstrapEArcDI::getResolver()::isDecorated($fQCN);
                }
            }

            if (!function_exists('di_get_decorator')) {
                function di_get_decorator(string $fQCN): ?string
                {
                    return BootstrapEArcDI::getResolver()::getDecorator($fQCN);
                }
            }

            if (!function_exists('di_register_factory')) {
                function di_register_factory(string $fQCN, ?callable $factory): void
                {
                    BootstrapEArcDI::getResolver()::registerFactory($fQCN, $factory);
                }
            }

            if (!function_exists('di_tag')) {
                function di_tag(string $name, string $fQCN, $argument=null): void
                {
                    BootstrapEArcDI::getResolver()::tag($name, $fQCN, $argument);
                }
            }

            if (!function_exists('di_get_tagged')) {
                function di_get_tagged(string $name): iterable
                {
                    return BootstrapEArcDI::getResolver()::getTagged($name);
                }
            }

            if (!function_exists('di_clear_tags')) {
                function di_clear_tags(string $name, string $fQCN=null): void
                {
                    BootstrapEArcDI::getResolver()::clearTags($name, $fQCN);
                }
            }

            if (!function_exists('di_mock')) {
                function di_mock(string $fQCN, object $mock): void
                {
                    BootstrapEArcDI::getResolver()::mock($fQCN, $mock);
                }
            }

            if (!function_exists('di_is_mocked')) {
                function di_is_mocked(string $fQCN): bool
                {
                    return BootstrapEArcDI::getResolver()::isMocked($fQCN);
                }
            }

            if (!function_exists('di_clear_mock')) {
                function di_clear_mock(string $fQCN=null): void
                {
                    BootstrapEArcDI::getResolver()::clearMock($fQCN);
                }
            }

            if (!function_exists('di_param')) {
                function di_param(string $key, $default=null)
                {
                    return BootstrapEArcDI::getParameterBag()::get($key, $default);
                }
            }

            if (!function_exists('di_set_param')) {
                function di_set_param(string $key, $value): void
                {
                    BootstrapEArcDI::getParameterBag()::set($key, $value);
                }
            }

            if (!function_exists('di_has_param')) {
                function di_has_param(string $key): bool
                {
                    return BootstrapEArcDI::getParameterBag()::has($key);
                }
            }

            if (!function_exists('di_import_param')) {
                function di_import_param(array $additionalParameter): void
                {
                    BootstrapEArcDI::getParameterBag()::import($additionalParameter);
                }
            }
        }
    }
}
