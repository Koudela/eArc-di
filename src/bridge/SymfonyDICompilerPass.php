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

use eArc\DI\CoObjects\SymfonyResolver;
use eArc\DI\DI;
use eArc\DI\Exceptions\DIException;
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
     * @throws DIException
     */
    public function process(ContainerBuilder $container)
    {
        SymfonyResolver::setContainer($container);

        SymfonyParameterBag::setContainer($container);

        DI::init(SymfonyResolver::class, SymfonyParameterBag::class);
    }
}
