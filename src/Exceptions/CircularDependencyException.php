<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 * dependency injection component
 *
 * @package earc/di
 * @link https://github.com/Koudela/eArc-di/
 * @copyright Copyright (c) 2018-2019 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\DI\Exceptions;

/**
 * The configuration for the objects the resolved object depends on depends on
 * the object itself.
 */
class CircularDependencyException extends DependencyContainerException
{
}
