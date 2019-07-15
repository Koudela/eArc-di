Feature: earc/di di base functionality

    Background:
        Given earc-di is bootstrapped

    Scenario: Class without parameter, dependencies
        Given class BasicClass is configured without parameter and dependencies
        Then get class BasicClass returns an object of type BasicClass
        Then get class BasicClass returns an object configured without parameter and dependencies

    Scenario: Class with parameter without dependencies
        Given class SomeClass is configured by ['a_string', 2.4]
        Then get class SomeClass returns an object configured with [string a_string,float 2.4]
        Given set string item a as a_string
        Given set float item b as 2.4
        Given class BasicClass is configured by ['a', 'b']
        Then get class BasicClass returns an object configured with [string a_string,float 2.4]

    Scenario: Class with chained dependencies in reversed order
        Given class SomeOtherClass is configured by ['eArc\\DITests\\env\\SomeClass']
        Given class SomeClass is configured by ['eArc\\DITests\\env\\BasicClass']
        Given class BasicClass is configured without parameter and dependencies
        Then get class SomeOtherClass returns obj configured with obj configured with obj of class BasicClass

    Scenario: Class with not configured dependency
        Given class BasicClass is configured by ['eArc\\DITests\\env\\SomeClass']
        Then get class BasicClass returns an object configured with [string eArc\DITests\env\SomeClass]