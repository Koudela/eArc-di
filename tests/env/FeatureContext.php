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

namespace eArc\DITests\env;

use Behat\Behat\Context\Context;
use eArc\DI\CoObjects\ParameterBag;
use eArc\DI\CoObjects\Resolver;
use eArc\DI\DI;
use eArc\DI\Exceptions\InvalidArgumentException;
use eArc\DI\Exceptions\MakeClassException;
use eArc\DI\Exceptions\NotFoundException;
use Exception;

require_once __DIR__ . '/../../vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    const CLASS_NAMESPACE = 'eArc\\DITests\\env\\';

    /**
     * @param string $str
     *
     * @return bool|string|null
     */
    protected function transformString(string $str)
    {
        switch ($str) {
            case 'null': return null;
            case 'false': return false;
            case 'true': return true;
            default: return $str;
        }
    }

    /**
     * @param string $type
     * @param string $str
     *
     * @return bool|float|int|string|null
     */
    protected function castString(string $type, string $str)
    {
        static $objects = [];

        switch ($type) {
            case 'int': return (int) $str;
            case 'float': return (float) $str;
            case 'bool': return (bool) $str;
            case 'null': return null;
            case 'object':
                if (!isset($objects[$str])) {
                    $objects[$str] = new $str();
                }
                return $objects[$str];
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'class':
            case 'string':
            default: return $str;
        }
    }

    /**
     * @Given earc-di is bootstrapped
     *
     * @throws InvalidArgumentException
     */
    public function earcDiIsBootstrapped()
    {
        switch (rand(0, 2)) {
            case 0:
                DI::init(Resolver::class, ParameterBag::class);
                break;
            case 1:
                DI::init();
                break;
            case 2:
                DI::init(Resolver::class);
                break;
        }
        di_clear_cache();
        di_clear_mock();
        di_decorate(SomeInterface::class, SomeInterface::class);
        di_decorate(SomeClass::class, SomeClass::class);
        di_decorate(SomeOtherClass::class, SomeOtherClass::class);
    }

    /**
     * @Given /^class (.*) does not exist$/
     *
     * @param string $className
     */
    public function classDoesNotExist(string $className)
    {
        assertNotTrue(class_exists(self::CLASS_NAMESPACE.$className));
    }

    /**
     * @Given /^class (.*) does exist$/
     *
     * @param string $className
     */
    public function classDoesExist(string $className)
    {
        assertTrue(class_exists(self::CLASS_NAMESPACE.$className));
    }

    /**
     * @Given /^di_has with parameter (.*) returns (.*)$/
     *
     * @param string $className
     * @param string $value
     */
    public function diHasWithParameterReturns(string $className, string $value)
    {
        assertSame($this->transformString($value), class_exists(self::CLASS_NAMESPACE.$className));
    }

    /**
     * @Then /^di_is_decorated with parameter (.*) returns (.*)$/
     *
     * @param string $className
     * @param string $value
     */
    public function diIsDecoratedWithParameterReturns(string $className, string $value)
    {
        assertSame($this->transformString($value), di_is_decorated(self::CLASS_NAMESPACE.$className));
    }

    /**
     * @Then /^di_get_decorator with parameter (.*) returns (.*)$/
     *
     * @param string $className
     * @param string $value
     */
    public function diGetDecoratorWithParameterReturns(string $className, string $value)
    {
        $value = $this->transformString($value);

        assertSame(
            is_string($value) ? self::CLASS_NAMESPACE.$value : $value,
            di_get_decorator(self::CLASS_NAMESPACE.$className)
        );
    }


    /**
     * @Given /^di_decorate is called with parameter (.*) and (.*)$/
     *
     * @param string $className1
     * @param string $className2
     */
    public function diDecorateIsCalledWithParameterAnd(string $className1, string $className2)
    {
        di_decorate(self::CLASS_NAMESPACE.$className1, self::CLASS_NAMESPACE.$className2);
    }

    /**
     * @Then /^di_static with parameter (.*) returns (.*)$/
     *
     * @param string $className
     * @param string $value
     */
    public function diStaticWithParameterReturns(string $className, string $value)
    {
        assertSame(self::CLASS_NAMESPACE.$value, di_static(self::CLASS_NAMESPACE.$className));
    }

    /**
     * @Given /^di_clear_tags with parameter (.*) and (.*) is called$/
     *
     * @param string $tagName
     * @param string $className
     */
    public function diClearTagsWithParameterIsCalled(string $tagName, string $className)
    {
        $className = $this->transformString($className);

        di_clear_tags(
            $tagName,
            is_string($className) ? self::CLASS_NAMESPACE.$className : $className
        );
    }

    /**
     * @Then /^di_get_tagged with parameter (.*) returns \[(.*)\]$/
     *
     * @param string $tagName
     * @param string $array
     */
    public function diGetTaggedWithParameterReturns(string $tagName, string $array)
    {
        $array = '' !== $array ? explode(',', $array) : [];
        $cnt = 0;
        foreach (di_get_tagged($tagName) as $value) {
            assertSame($value, self::CLASS_NAMESPACE.trim($array[$cnt++]));
        }

        assertSame($cnt, count($array));
    }

    /**
     * @Given /^di_tag with parameter (.*) and (.*) is called$/
     *
     * @param string $className
     * @param string $tagName
     */
    public function diTagWithParameterAndIsCalled(string $className, string $tagName)
    {
        di_tag(self::CLASS_NAMESPACE.$className, $tagName);
    }

    /**
     * @Then /^di_is_mocked with parameter (.*) returns (.*)$/
     *
     * @param string $className
     * @param string $value
     */
    public function diIsMockedWithParameterReturns(string $className, string $value)
    {
        assertSame($this->transformString($value), di_is_mocked(self::CLASS_NAMESPACE.$className));
    }

    /**
     * @Then /^di_get with parameter (.*) returns (.*) object$/
     *
     * @param string $className
     * @param string $objectClassName
     */
    public function diGetWithParameterReturns(string $className, string $objectClassName)
    {
        assertSame(
            self::CLASS_NAMESPACE.$objectClassName,
            get_class(di_get(self::CLASS_NAMESPACE.$className))
        );
    }

    /**
     * @Then /^di_make with parameter (.*) returns (.*) object$/
     *
     * @param string $className
     * @param string $objectClassName
     */
    public function diMakeWithParameterReturns(string $className, string $objectClassName)
    {
        assertSame(
            self::CLASS_NAMESPACE.$objectClassName,
            get_class(di_make(self::CLASS_NAMESPACE.$className))
        );
    }

    /**
     * @Given /^di_mock with parameter (.*) and new (.*) is called$/
     *
     * @param string $className
     * @param string $objectClassName
     */
    public function diMockWithParameterIsCalled(string $className, string $objectClassName)
    {
        $mock = self::CLASS_NAMESPACE.$objectClassName;
        di_mock(self::CLASS_NAMESPACE.$className, new $mock);
    }

    /**
     * @Given /^di_clear_mock with parameter (.*) is called$/
     *
     * @param string $className
     */
    public function diClearMockWithParameterIsCalled(string $className)
    {
        $className = $this->transformString($className);

        di_clear_mock(is_string($className) ? self::CLASS_NAMESPACE.$className : $className);
    }

    /**
     * @Then /^di_param with parameter (.*) throws NotFoundException$/
     *
     * @param string $key
     */
    public function diParamWithParameterThrowsNotFoundException(string $key)
    {
        try {
            di_param($key);
            assertSame(true, false);
        } catch (Exception $e) {
            assertSame(NotFoundException::class, get_class($e));
        }
    }

    /**
     * @Then /^di_has_param with parameter (.*) returns (.*)$/
     *
     * @param string $key
     * @param string $value
     */
    public function diHasParamWithParameterReturns(string $key, string $value)
    {
        assertSame($this->transformString($value), di_has_param($key));
    }

    /**
     * @Given /^di_set_param with parameter (.*) and (.*) is called$/
     *
     * @param string $key
     * @param string $value
     */
    public function diSetParamWithParameterAndIsCalled(string $key, string $value)
    {
        di_set_param($key, $this->transformString($value));
    }

    /**
     * @Then /^di_param with parameter (.*) returns (.*)$/
     *
     * @param string $key
     * @param string $value
     */
    public function diParamWithParameterReturns(string $key, string $value)
    {
        assertSame($this->transformString($value), di_param($key));
    }

    /**
     * @Given /^di_set_param with parameter (.*) and (.*) throws an InvalidArgumentException$/
     *
     * @param string $key
     * @param string $value
     */
    public function diSetParamWithParameterAndThrowsAnInvalidArgumentException(string $key, string $value)
    {
        try {
            di_set_param($key, $this->transformString($value));
            assertSame(true, false);
        } catch (Exception $e) {
            assertSame(InvalidArgumentException::class, get_class($e));
        }
    }

    /**
     * @Given /^di_import_param with plain parameter is called$/
     */
    public function diImportParamWithPlainParameterAndIsCalled()
    {
        di_import_param(['plain_parameter' => 'My name is bunny. I do not know a thing.']);
    }

    /**
     * @Given /^di_import_param with nested parameter is called$/
     */
    public function diImportParamWithNestedParameterAndIsCalled()
    {
        di_import_param(['this' => ['parameter' => ['is' => 'nested']]]);
    }

    /**
     * @Then /^di_get with parameter (.*) throws MakeClassException$/
     *
     * @param string $className
     */
    public function diGetWithParameterThrowsMakeClassException(string $className)
    {
        try {
            di_get(self::CLASS_NAMESPACE.$className);
            assertSame(true, false);
        } catch (Exception $e) {
            assertSame(MakeClassException::class, get_class($e));
        }
    }

    /**
     * @Then /^di_make with parameter (.*) throws MakeClassException$/
     *
     * @param string $className
     */
    public function diMakeWithParameterThrowsMakeClassException(string $className)
    {
        try {
            di_make(self::CLASS_NAMESPACE.$className);
            assertSame(true, false);
        } catch (Exception $e) {
            assertSame(MakeClassException::class, get_class($e));
        }
    }

    /**
     * @Then /^successive di_make (.*) calls result in different objects$/
     *
     * @param string $className
     */
    public function successiveDiMakeCallsResultInDifferentObjects(string $className)
    {
        $class = self::CLASS_NAMESPACE.$className;

        assertNotSame(di_make($class), di_make($class));
    }

    /**
     * @Then /^successive di_get (.*) calls result in different objects after di_clear_cache only$/
     *
     * @param string $className
     */
    public function successiveDiGetCallsResultInDifferentObjectsAfterDiClearCacheOnly(string $className)
    {
        $class = self::CLASS_NAMESPACE.$className;

        assertSame(di_get($class), di_get($class));

        $obj1 = di_get($class);
        di_clear_cache($class);
        $obj2 = di_get($class);

        assertNotSame($obj1, $obj2);

        $obj1 = di_get($class);
        di_clear_cache(null);
        $obj2 = di_get($class);

        assertNotSame($obj1, $obj2);
    }

    /**
     * @Then /^(.*) implements ([not ]*)(.*)$/
     *
     * @param string $className
     * @param string $not
     * @param string $interfaceName
     */
    public function implementsOrNotThe(string $className, string $not, string $interfaceName)
    {
        $is = is_subclass_of(self::CLASS_NAMESPACE.$className, self::CLASS_NAMESPACE.$interfaceName);
        assertTrue(($not !== '') ? !$is : $is);
    }

    /**
     * @Then /^di_get with parameter (.*) throws InvalidArgumentException/
     *
     * @param string $className
     */
    public function diGetWithParameterThrowsInvalidArgumentException(string $className)
    {
        try {
            di_get(self::CLASS_NAMESPACE.$className);
            assertSame(true, false);
        } catch (Exception $e) {
            assertSame(InvalidArgumentException::class, get_class($e));
        }
    }

    /**
     * @Then /^di_make with parameter (.*) throws InvalidArgumentException/
     *
     * @param string $className
     */
    public function diMakeWithParameterThrowsInvalidArgumentException(string $className)
    {
        try {
            di_make(self::CLASS_NAMESPACE.$className);
            assertSame(true, false);
        } catch (Exception $e) {
            assertSame(InvalidArgumentException::class, get_class($e));
        }
    }
}
