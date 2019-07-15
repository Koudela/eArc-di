Feature: earc/di circular dependency injection

    Background: load a circular dependency
        Given earc-di is bootstrapped
        Given BasicClass depends on SomeClass
        Given SomeClass depends on SomeOtherClass
        Given SomeOtherClass depends on BasicClass

    Scenario Outline: try to get/make objects from the circular dependency
        Then get <class> throws a CircularDependencyException
        Then make <class> throws a CircularDependencyException

        Examples:
            | class             |
            | BasicClass        |
            | SomeClass         |
            | SomeOtherClass    |

