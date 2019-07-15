Feature: earc/di flags

    Background:
        Given earc-di is bootstrapped
        Given CountInstantiations has been reset

    Scenario: flag ITEM_KEY
        Given class SomeClass is configured by ['eArc\\DI\\Interfaces\\Flags' => [eArc\DI\Interfaces\Flags::ITEM_KEY => 'key1']]
        Then get key1 returns class SomeClass

    Scenario: flag CLASS_NAME
        Given class SomeClass is configured by ['eArc\\DI\\Interfaces\\Flags' => [eArc\DI\Interfaces\Flags::CLASS_NAME => 'eArc\\DITests\\env\\SomeOtherClass']]
        Then get class SomeClass returns an object of type SomeOtherClass


    Scenario: flag DO_NOT_RESOLVE
        Given class BasicClass is configured without parameter and dependencies
        Given class SomeClass is configured by ['eArc\\DITests\\env\\BasicClass', 'eArc\\DI\\Interfaces\\Flags' => [eArc\DI\Interfaces\Flags::DO_NOT_RESOLVE => false]]
        Then get class SomeClass returns an object of type SomeClass
        And get class SomeClass returns an object configured with [class BasicClass]
        Given class SomeOtherClass is configured by ['eArc\\DITests\\env\\BasicClass', 'eArc\\DI\\Interfaces\\Flags' => [eArc\DI\Interfaces\Flags::DO_NOT_RESOLVE => true]]
        Then get class SomeOtherClass returns an array with [string eArc\DITests\env\BasicClass]

    Scenario: flag INSTANT_MAKE
        Given class CountInstantiations is configured without parameter and dependencies
        Then CountInstantiations is instantiated 0 times
        Given class CountInstantiations is configured by ['eArc\\DI\\Interfaces\\Flags' => [eArc\DI\Interfaces\Flags::INSTANT_MAKE => false]]
        Then CountInstantiations is instantiated 0 times
        Given class CountInstantiations is configured by ['eArc\\DI\\Interfaces\\Flags' => [eArc\DI\Interfaces\Flags::INSTANT_MAKE => true]]
        Then CountInstantiations is instantiated 1 times
        Given get class CountInstantiations returns an object of type CountInstantiations
        Then CountInstantiations is instantiated 1 times
        Given make class CountInstantiations returns an object of type CountInstantiations
        Then CountInstantiations is instantiated 2 times

    Scenario: flag SAVE_NO_REFERENCE
        Given class SomeClass is configured by ['eArc\\DI\\Interfaces\\Flags' => [eArc\DI\Interfaces\Flags::SAVE_NO_REFERENCE => true]]
        Then get class SomeClass throws NotFoundExceptionInterface
        Given class SomeClass is configured by ['eArc\\DI\\Interfaces\\Flags' => [eArc\DI\Interfaces\Flags::SAVE_NO_REFERENCE => false]]
        Then get class SomeClass returns an object of type SomeClass

    Scenario: flag FACTORY
        Given class SomeClass is configured without parameter and dependencies
        Given set string item p1 as param1
        Given class SomeOtherClass is configured with [42, 'eArc\\DITests\\env\\SomeClass', 'value'] and flag FACTORY with a function callable
        Then get class SomeOtherClass returns an object of type BasicClass
        Then get class SomeOtherClass returns an object configured with [int 42,class SomeClass,string value]
        Given class SomeOtherClass is configured with [23, 'p1', 'eArc\\DITests\\env\\SomeClass'] and flag FACTORY with a closure callable
        Then get class SomeOtherClass returns an object of type BasicClass
        Then get class SomeOtherClass returns an object configured with [int 23,string param1,class SomeClass]
        Given class SomeOtherClass is configured with [23, 'val', 'eArc\\DITests\\env\\SomeClass'] and flag FACTORY with a static callable
        Then get class SomeOtherClass returns an object of type BasicClass
        Then get class SomeOtherClass returns an object configured with [int 23,string val,class SomeClass]
        Given class SomeOtherClass is configured with ['eArc\\DITests\\env\\SomeClass', 'p1'] and flag FACTORY with a object callable
        Then get class SomeOtherClass returns an object of type BasicClass
        Then get class SomeOtherClass returns an object configured with [class SomeClass,string param1]
