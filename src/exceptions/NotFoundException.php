<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/di
 * @link https://github.com/Koudela/earc-di/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\di\exceptions;

/**
 * No entry was found for the identifier in the container.
 */
class NotFoundException extends \Exception implements \Psr\Container\NotFoundExceptionInterface {}
