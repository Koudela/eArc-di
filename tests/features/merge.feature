Feature: earc/di merge

    Background:
        Given earc-di is bootstrapped
        And class BasicClass is configured by ['param1']
        And set int item param1 as 42
        And the earc-di container is merged into a new one

    Scenario: Dependencies after construction time only
        Given class SomeClass is configured by ['08', 15]
        And class SomeOtherClass is configured by ['param1', 23, 'eArc\\DITests\\env\\BasicClass', 'eArc\\DITests\\env\\SomeClass']
        Then get class SomeOtherClass returns an object of type SomeOtherClass
        And get class BasicClass returns an object of type BasicClass
        And get param1 returns int 42
        And get class SomeOtherClass returns an object configured with [string param1,int 23,string eArc\DITests\env\BasicClass,class SomeClass]
