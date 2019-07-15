Feature: earc/di get on construction time only

    Background:
        Given earc-di is bootstrapped
        And class BasicClass is configured by ['param1']
        And set int item param1 as 42
        And a second earc-di container is bootstrapped

    Scenario: Dependencies on construction time only
        Given class SomeClass is configured by ['08', 15]
        And class SomeOtherClass is configured by ['param1', 23, 'eArc\\DITests\\env\\BasicClass', 'eArc\\DITests\\env\\SomeClass']
        Then get class SomeOtherClass returns an object of type SomeOtherClass
        And get class BasicClass throws NotFoundExceptionInterface
        And get param1 throws NotFoundExceptionInterface
        And get class SomeOtherClass returns an object configured with [int 42,int 23,class BasicClass,class SomeClass]
