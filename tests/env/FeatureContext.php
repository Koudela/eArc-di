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
use eArc\Container\Exceptions\ItemNotFoundException;
use eArc\DI\DependencyContainer;
use eArc\DI\DependencyResolver;
use eArc\DI\Exceptions\CircularDependencyException;
use eArc\DI\Exceptions\InvalidFactoryException;
use eArc\DI\Exceptions\InvalidObjectConfigurationException;
use eArc\DI\Interfaces\Flags;
use eArc\DI\Support\ContainerCollection;
use Psr\Container\NotFoundExceptionInterface;

require_once __DIR__ . '/../../vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    const CLASS_NAMESPACE = 'eArc\\DITests\\env\\';

    /** @var mixed|null */
    static protected $returnFromCallableStatic;

    /** @var mixed|null */
    protected $returnFromCallable;

    /** @var DependencyContainer */
    protected $dic;

    /** @var DependencyResolver */
    protected $depRes;

    /** @var ContainerCollection */
    protected $containerCollection;

    /** @var Callable[] */
    protected $callableCollection = [];


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
     *
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     * @throws ItemNotFoundException
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
                if ($this->dic->has(self::CLASS_NAMESPACE . $str)) {
                    return $this->dic->get(self::CLASS_NAMESPACE . $str);
                }
                if ($this->containerCollection->has(self::CLASS_NAMESPACE . $str)) {
                    return $this->containerCollection->get(self::CLASS_NAMESPACE . $str);
                }
            case 'string':
            default: return $str;
        }
    }

    /**
     * @Given earc-di is bootstrapped
     *
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     * @throws InvalidFactoryException
     */
    public function earcDiIsBootstrapped()
    {
        $this->depRes = new DependencyResolver();
        $this->dic = new DependencyContainer($this->depRes);
    }

    /**
     * @Given /^a second earc-di container is bootstrapped$/
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function aSecondEarcDiIsBootstrapped()
    {
        $this->containerCollection = new ContainerCollection();
        $this->containerCollection->merge($this->dic);
        $this->dic = new DependencyContainer(
            new DependencyResolver([], null, $this->containerCollection)
        );
    }

    /**
     * @Given /^the earc-di container is merged into a new one$/
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function theEarcDiContainerIsMergedIntoANewOne()
    {
        $dic = new DependencyContainer();
        $dic->merge($this->dic);
        $this->dic = $dic;
    }

    /**
     * @Then /^has (.*) returns (.*)$/
     *
     * @param string $name
     * @param string $return
     */
    public function hasReturns(string $name, string $return)
    {
        assertSame($this->transformString($return), $this->dic->has($name));
    }

    /**
     * @Then get :name throws NotFoundExceptionInterface
     *
     * @param string $name
     */
    public function getThrowsNotFoundExceptionInterface(string $name)
    {
        $assertion = false;

        try {
            $this->dic->get($name);
        } catch (\Exception $exception) {
            $assertion = is_subclass_of($exception, NotFoundExceptionInterface::class);
        }

        assertTrue($assertion);
    }

    /**
     * @Then /^get class (.*) throws NotFoundExceptionInterface$/
     *
     * @param string $className
     */
    public function getClassThrowsNotFoundExceptionInterface(string $className)
    {
        $assertion = false;

        try {
            $this->dic->get(self::CLASS_NAMESPACE . $className);
        } catch (\Exception $exception) {
            $assertion = is_subclass_of($exception, NotFoundExceptionInterface::class);
        }

        assertTrue($assertion);
    }

    /**
     * @Given /^set (.*) item (.*) as (.*)$/
     *
     * @param string $type
     * @param string $name
     * @param string $value
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     * @throws ItemNotFoundException
     */
    public function setItemAs(string $type, string $name, string $value)
    {
        $this->dic->set($name, $this->castString($type, $value));
    }

    /**
     * @Then /^get (.*) returns ([a-zA-Z0-9.\\_]+)+ ([a-zA-Z0-9.\\_]+)$/
     *
     * @param string $name
     * @param string $type
     * @param string $value
     *
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     * @throws ItemNotFoundException
     */
    public function getReturns(string $name, string $type, string $value)
    {
        if ($type === 'class') {
            assertInstanceOf(self::CLASS_NAMESPACE . $value, $this->dic->get($name));
            return;
        }
        assertSame($this->castString($type, $value), $this->dic->get($name));
    }

    /**
     * @Given /^there exists a function callable named (.*) returning (.*)$/
     *
     * @param string $name
     * @param string $return
     */
    public function thereExistsAFunctionCallableNamedReturning(string $name, string $return)
    {
        global $basicFunctionCallableReturn;

        $basicFunctionCallableReturn = $return;

        if (!function_exists(self::CLASS_NAMESPACE . 'basicFunctionCallable'))
        {
            function basicFunctionCallable()
            {
                global $basicFunctionCallableReturn;

                return $basicFunctionCallableReturn;
            }
        }
        $this->callableCollection[$name] = self::CLASS_NAMESPACE . 'basicFunctionCallable';
    }

    /**
     * @Given /^there exists a closure callable named (.*) returning (.*)$/
     *
     * @param string $name
     * @param string $return
     */
    public function thereExistsAClosureCallableNamedReturning(string $name, string $return)
    {
        $this->callableCollection[$name] = function() use ($return) {
            return $return;
        };
    }

    static public function callableStatic() {
        return self::$returnFromCallableStatic;
    }

    /**
     * @Given /^there exists a static callable named (.*) returning (.*)$/
     *
     * @param string $name
     * @param string $return
     */
    public function thereExistsAStaticCallableNamedReturning(string $name, string $return)
    {
        self::$returnFromCallableStatic = $return;
        $this->callableCollection[$name] = [FeatureContext::class, 'callableStatic'];
    }

    public function callableFromObject() {
        return $this->returnFromCallable;
    }

    /**
     * @Given /^there exists a object callable named (.*) returning (.*)$/
     *
     * @param string $name
     * @param string $return
     */
    public function thereExistsAObjectCallableNamedReturning(string $name, string $return)
    {
        $this->returnFromCallable = $return;
        $this->callableCollection[$name] = [$this, 'callableFromObject'];
    }

    /**
     * @Given /^(.*) is set with callable (.*)$/
     *
     * @param string $containerKey
     * @param string $callableName
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function isSetWithCallable(string $containerKey, string $callableName)
    {
        $this->dic->set($containerKey, $this->callableCollection[$callableName]);
    }

    /**
     * @Given /^(.*) depends on (.*)$/
     *
     * @param string $class1
     * @param string $class2
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function dependsOn(string $class1, string $class2)
    {
        $this->dic->set(self::CLASS_NAMESPACE . $class1, [self::CLASS_NAMESPACE . $class2]);
    }

    /**
     * @Then /^get (.*) throws a CircularDependencyException$/
     *
     * @param string $name
     *
     * @throws InvalidObjectConfigurationException
     */
    public function GetThrowsACircularDependencyException(string $name)
    {
        $isThrown = false;

        try {
            $this->dic->get(self::CLASS_NAMESPACE . $name);
        } catch (CircularDependencyException $exception) {
            $isThrown = true;
        }

        assertTrue($isThrown);
    }

    /**
     * @Then /^make (.*) throws a CircularDependencyException$/
     *
     * @param string $name
     *
     * @throws InvalidObjectConfigurationException
     * @throws ItemNotFoundException
     */
    public function MakeThrowsACircularDependencyException(string $name)
    {
        $isThrown = false;

        try {
            $this->dic->make(self::CLASS_NAMESPACE . $name);
        } catch (CircularDependencyException $exception) {
            $isThrown = true;
        }

        assertTrue($isThrown);
    }

    /**
     * @Then /^make (.*) throws an InvalidObjectConfigurationException$/
     *
     * @param string $name
     *
     * @throws CircularDependencyException
     * @throws ItemNotFoundException
     */
    public function makeThrowsAnInvalidObjectConfigurationException(string $name)
    {
        $isThrown = false;

        try {
            $this->dic->make(self::CLASS_NAMESPACE . $name);
        } catch (InvalidObjectConfigurationException $exception) {
            $isThrown = true;
        }

        assertTrue($isThrown);
    }

    /**
     * @Given /^class (.*) is configured without parameter and dependencies$/
     *
     * @param string $className
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function classIsConfiguredWithoutParameterAndDependencies(string $className)
    {
        $this->dic->set(self::CLASS_NAMESPACE . $className, []);
    }

    /**
     * @Then /^get class (.*) returns an object of type ([a-zA-Z0-9.\\_]+)$/
     *
     * @param string $classKey
     * @param string $className
     *
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     */
    public function getReturnsAnObjectOfType(string $classKey, string $className)
    {
        assertInstanceOf(
            1 === preg_match('/^.*[\\\\].*$/', $className) ? $className : self::CLASS_NAMESPACE . $className,
            $this->dic->get(self::CLASS_NAMESPACE .$classKey)
        );
    }

    /**
     * @Then /^make class (.*) returns an object of type ([a-zA-Z0-9.\\_]+)$/
     *
     * @param string $classKey
     * @param string $className
     *
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     * @throws ItemNotFoundException
     */
    public function makeReturnsAnObjectOfType(string $classKey, string $className)
    {
        assertInstanceOf(
            1 === preg_match('/^.*[\\\\].*$/', $className) ? $className : self::CLASS_NAMESPACE . $className,
            $this->dic->make(self::CLASS_NAMESPACE .$classKey)
        );
    }

    /**
     * @Then /^get class (.*) returns an object configured without parameter and dependencies$/
     *
     * @param string $classKey
     *
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     */
    public function getClassReturnsAnObjectConfiguredWithoutParameterAndDependencies(string $classKey)
    {
        assertSame([], $this->dic->get(self::CLASS_NAMESPACE . $classKey)->getInitialArguments());
    }

    /**
     * @Given /^class (.*) is configured by (.*)$/
     *
     * @param string $className
     * @param string $config
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function classIsConfiguredBy(string $className, string $config)
    {
        $conf = '';
        eval('$conf = ' . $config . ';');
        $this->dic->set(self::CLASS_NAMESPACE . $className, $conf);
    }

    /**
     * @Then /^get class (.*) returns an object configured with \[(.*)\]$/
     *
     * @param string $className
     * @param string $config
     *
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     * @throws ItemNotFoundException
     */
    public function getClassReturnsAnObjectConfiguredWith(string $className, string $config)
    {
        $arr = explode(',', $config);
        $config = [];
        foreach ($arr as $part) {
            $parts = explode(' ',$part);
            $config[] = $this->castString($parts[0], $parts[1]);
        }

        assertSame($config, $this->dic->get(self::CLASS_NAMESPACE . $className)->getInitialArguments());
    }

    /**
     * @Then /^get class (.*) returns obj configured with obj configured with obj of class (.*)$/
     *
     * @param string $classNameKey
     * @param string $classNameEndOfChain
     *
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     */
    public function getClassReturnsObjConfiguredWithObjConfiguredWithObjOfClass(string $classNameKey, string $classNameEndOfChain)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $obj = $this->dic->get(self::CLASS_NAMESPACE . $classNameKey)->getInitialArguments()[0]->getInitialArguments()[0];
        assertInstanceOf(self::CLASS_NAMESPACE . $classNameEndOfChain, $obj);
    }

    /**
     * @Then /^get class (.*) returns an array with \[(.*)\]$/
     *
     * @param string $className
     * @param string $config
     *
     * @throws CircularDependencyException
     * @throws InvalidObjectConfigurationException
     * @throws ItemNotFoundException
     */
    public function getClassReturnsAnArrayWith(string $className, string $config)
    {
        $arr = explode(',', $config);
        $config = [];
        foreach ($arr as $part) {
            $parts = explode(' ',$part);
            $config[] = $this->castString($parts[0], $parts[1]);
        }

        assertSame($config, $this->dic->get(self::CLASS_NAMESPACE . $className));
    }

    /**
     * @Then /^CountInstantiations is instantiated ([0-9]+) times$/
     *
     * @param string $times
     */
    public function countInstantiationsIsInstantiatedTimes(string $times)
    {
        assertSame(CountInstantiations::$count, intval($times));
    }

    /**
     * @Given /^CountInstantiations has been reset$/
     */
    public function countInstantiationsHasBeenReset()
    {
        CountInstantiations::$count = 0;
    }

    /**
     * @Given /^class (.*) is configured with (.*) and flag FACTORY with a function callable$/
     *
     * @param string $className
     * @param string $config
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function classIsConfiguredWithFlagFACTORYAndAFunctionCallable(string $className, string $config)
    {
        if (!function_exists(self::CLASS_NAMESPACE . 'someFunctionCallable')) {
            function someFunctionCallable(...$args)
            {
                return new BasicClass(...$args);
            }
        }

        $conf = [];
        eval('$conf = ' . $config . ';');
        $conf[Flags::class] =  [
            Flags::FACTORY => self::CLASS_NAMESPACE . 'someFunctionCallable'
        ];

        var_dump(is_callable(self::CLASS_NAMESPACE . 'someFunctionCallable'));
        $this->dic->set(self::CLASS_NAMESPACE . $className, $conf);
    }

    /**
     * @Given /^class (.*) is configured with (.*) and flag FACTORY with a closure callable$/
     *
     * @param string $className
     * @param string $config
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function classIsConfiguredWithFlagFACTORYAndAClosureCallable(string $className, string $config)
    {
        $conf = [];
        eval('$conf = ' . $config . ';');
        $conf[Flags::class] =  [
            Flags::FACTORY => function(...$args) {return new BasicClass(...$args);}
        ];

        $this->dic->set(self::CLASS_NAMESPACE . $className, $conf);
    }

    /**
     * @Given /^class (.*) is configured with (.*) and flag FACTORY with a static callable$/
     *
     * @param string $className
     * @param string $config
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function classIsConfiguredWithFlagFACTORYAndAStaticCallable(string $className, string $config)
    {
        $conf = [];
        eval('$conf = ' . $config . ';');
        $conf[Flags::class] =  [
            Flags::FACTORY => [FeatureContext::class, 'someStaticCallable']
        ];

        $this->dic->set(self::CLASS_NAMESPACE . $className, $conf);
    }

    /**
     * @param mixed ...$args
     *
     * @return BasicClass
     */
    public static function someStaticCallable(...$args)
    {
        return new BasicClass(...$args);
    }

    /**
     * @Given /^class (.*) is configured with (.*) and flag FACTORY with a object callable$/
     *
     * @param string $className
     * @param string $config
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function classIsConfiguredWithFlagFACTORYAndAObjectCallable(string $className, string $config)
    {
        $conf = [];
        eval('$conf = ' . $config . ';');
        $conf[Flags::class] =  [
            Flags::FACTORY => [$this, 'someObjectCallable']
        ];

        $this->dic->set(self::CLASS_NAMESPACE . $className, $conf);
    }

    /**
     * @param mixed ...$args
     *
     * @return BasicClass
     */
    public function someObjectCallable(...$args)
    {
        return new BasicClass(...$args);
    }

    /**
     * @Then dependencies are accessible from beneath
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function dependenciesAreAccessibleFromBeneath()
    {
        $this->dic->load([
            'param1' => 42,
            BasicClass::class => [],
            SomeClass::class => [
                'param2' => 23,
                SomeOtherClass::class => [
                    SomeClass::class => [
                        BasicClass::class,
                        'param1',
                        'param2'
                    ]
                ]
            ]
        ]);

        $this->getReturnsAnObjectOfType('SomeClass', SomeClass::class);
        /** @var SomeClass $class */
        $class = $this->dic->get(SomeClass::class);
        $args = $class->getInitialArguments();
        assertSame(23, $args[0]);
        assertInstanceOf(SomeOtherClass::class, $args[1]);
        $class = $args[1];
        $args = $class->getInitialArguments();
        assertInstanceOf(SomeClass::class, $args[0]);
        $class = $args[0];
        $args = $class->getInitialArguments();
        assertInstanceOf(BasicClass::class, $args[0]);
        assertSame(42, $args[1]);
        assertSame(23, $args[2]);
    }

    /**
     * @Then dependencies are accessible from beside
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function dependenciesAreAccessibleFromBeside()
    {
        $this->dic->load([
            SomeClass::class => [
                'param2' => 23,
                SomeOtherClass::class => [
                    'param1',
                    'param2',
                    BasicClass::class
                ],
                BasicClass::class => [
                    'param1'
                ],
                'param1' => 42,
            ]
        ]);

        $this->getReturnsAnObjectOfType('SomeClass', SomeClass::class);
        /** @var SomeClass $class */
        $class = $this->dic->get(SomeClass::class);
        $args = $class->getInitialArguments();
        assertSame( 23, $args[0]);
        assertInstanceOf(SomeOtherClass::class, $args[1]);
        assertInstanceOf(BasicClass::class, $args[2]);
        assertSame(42, $args[3]);
        $class = $args[1];
        $args = $class->getInitialArguments();
        assertSame(42, $args[0]);
        assertSame( 23, $args[1]);
        assertInstanceOf(BasicClass::class, $args[2]);
        $class = $args[2];
        $args = $class->getInitialArguments();
        assertSame(42, $args[0]);
    }

    /**
     * @Then dependencies are not accessible from above
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */

    public function dependenciesAreNotAccessibleFromAbove()
    {
        $this->dic->load([
            'param1' => 42,
            SomeClass::class => [
                'param2',
                BasicClass::class,
                SomeOtherClass::class => [
                    BasicClass::class => [11],
                    'param1',
                    'param2' => 23
                ]
            ]
        ]);

        $this->getReturnsAnObjectOfType('SomeClass', SomeClass::class);
        /** @var SomeClass $class */
        $class = $this->dic->get(SomeClass::class);
        $args = $class->getInitialArguments();
        assertSame('param2', $args[0]);
        assertSame(BasicClass::class, $args[1]);
        assertTrue(is_string($args[1]));
        assertInstanceOf(SomeOtherClass::class, $args[2]);
        $class = $args[2];
        $args = $class->getInitialArguments();
        assertInstanceOf(BasicClass::class, $args[0]);
        assertSame(42, $args[1]);
        assertSame(23, $args[2]);
        $class = $args[0];
        $args = $class->getInitialArguments();
        assertSame(11, $args[0]);
    }

    /**
     * @Then dependencies are not accessible from another sub-tree
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function dependenciesAreNotAccessibleFromAnotherSubTree()
    {
        $this->dic->load([
            SomeClass::class => [
                'param1' => 42,
                BasicClass::class => [
                    'param2'
                ],
            ],
            SomeOtherClass::class => [
                BasicClass::class,
                'param2' => 23,
                'param1'
            ]
        ]);

        /** @var SomeClass $class */
        $class = $this->dic->get(SomeClass::class);
        $args = $class->getInitialArguments();
        assertSame(42, $args[0]);
        assertInstanceOf(BasicClass::class, $args[1]);
        $class = $args[1];
        $args = $class->getInitialArguments();
        assertSame('param2', $args[0]);
        $class = $this->dic->get(SomeOtherClass::class);
        $args = $class->getInitialArguments();
        assertSame(BasicClass::class, $args[0]);
        assertTrue(is_string($args[0]));
        assertSame(23, $args[1]);
        assertSame('param1', $args[2]);
    }

    /**
     * @Then dependencies defined in a near level are preferred over dependencies in a far level
     *
     * @throws CircularDependencyException
     * @throws InvalidFactoryException
     * @throws InvalidObjectConfigurationException
     */
    public function dependenciesDefinedInANearLevelArePreferredOverDependenciesInAFarLevel()
    {
        $this->dic->load([
            'param1' => 23,
            SomeClass::class => [
                'param1' => 42,
                SomeOtherClass::class => ['param1'],
                BasicClass::class => [
                    SomeOtherClass::class,
                    'param1'
                ],
            ],
            SomeOtherClass::class => []
        ]);

        /** @var SomeClass $class */
        $class = $this->dic->get(SomeClass::class);
        $args = $class->getInitialArguments();
        assertSame(42, $args[0]);
        assertInstanceOf(SomeOtherClass::class, $args[1]);
        $someOtherClass = $args[1];
        assertInstanceOf(BasicClass::class, $args[2]);
        $class = $args[2];
        $args = $class->getInitialArguments();
        assertSame($args[0], $someOtherClass);
        assertSame(42, $args[1]);
    }
}
