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

namespace eArc\DITests\env;

use eArc\DI\DI;
use eArc\DI\Exceptions\InvalidArgumentException;
use eArc\DI\Exceptions\MakeClassException;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * This is no unit test. It is an integration test.
 */
class RegisterFactoryTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testIntegration()
    {
        $this->bootstrap();
        $this->runRegisterFactoryAssertions();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function bootstrap()
    {
        DI::init();
    }

    public function runRegisterFactoryAssertions()
    {
        di_register_factory(BasicClass::class, function() {
            return new BasicClass();
        });

        $this->assertTrue(di_make(BasicClass::class) instanceof BasicClass);

        di_register_factory(BasicClass::class, function() {
            return new SomeOtherClass();
        });

        try {
            di_make(BasicClass::class);
            throw new Exception();
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof MakeClassException);
        }

        di_register_factory(SomeInterface::class, function() {
            return new SomeOtherClass();
        });

        $this->assertTrue(di_make(SomeInterface::class) instanceof SomeOtherClass);

        di_register_factory(SomeInterface::class, null);

        try {
            di_make(SomeInterface::class);
            throw new Exception();
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof MakeClassException);
        }

        di_decorate(SomeInterface::class, SomeOtherClass::class);
        di_register_factory(SomeOtherClass::class, function() {
            return new SomeOtherClass();
        });

        $this->assertTrue(di_make(SomeInterface::class) instanceof SomeOtherClass);
    }
}
