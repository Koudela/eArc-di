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

namespace eArc\DITests\env;

class CountInstantiations
{
    static $count = 0;

    /** @var mixed[] */
    protected $args;

    public function __construct(...$args)
    {
        $this->args = $args;
        self::$count++;
    }

    public function getInitialArguments()
    {
        return $this->args;
    }
}