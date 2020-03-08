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

namespace eArc\DI\bridge;

use eArc\DI\DI;
use eArc\DI\Exceptions\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register this compiler pass to integrate the symfony container into the DI resolver.
 */
class SymfonyDICompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        SymfonyResolver::setContainer($container);

        SymfonyParameterBag::setContainer($container);

        DI::init(SymfonyResolver::class, SymfonyParameterBag::class);

        di_set_param(DI::PARAM_KEY_DECORATION_CLASS, $container->getParameter(DI::PARAM_KEY_DECORATION_CLASS));
        di_set_param(DI::PARAM_KEY_DECORATION_NAMESPACE, $container->getParameter(DI::PARAM_KEY_DECORATION_NAMESPACE));

        DI::importParameter();
    }
}
