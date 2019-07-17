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

use Psr\Container\ContainerExceptionInterface;
use Exception;

/**
 * Generic dependency injection exception
 */
class DIException extends Exception implements ContainerExceptionInterface
{
}
