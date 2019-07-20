Feature: earc/di - base functionality - di_has, di_get, di_make, di_clear_cache

    Background:
        Given earc-di is bootstrapped

    Scenario: class does not exists
        Given class NotExistingClass does not exist
        Then di_has with parameter NotExistingClass returns false
        Then di_make with parameter NotExistingClass throws MakeClassException
        Then di_get with parameter NotExistingClass throws MakeClassException

    Scenario: class exists
        Given class SomeClass does exist
        Then di_has with parameter SomeClass returns true
        Then di_make with parameter SomeClass returns SomeClass object
        Then di_get with parameter SomeClass returns SomeClass object
        Then successive di_make SomeClass calls result in different objects
        Then successive di_get SomeClass calls result in different objects after di_clear_cache only

    Scenario: type hint violation
        Given di_decorate is called with parameter SomeInterface and SomeClass
        Given SomeClass implements not SomeInterface
        Then di_make with parameter SomeInterface throws InvalidArgumentException
        Then di_get with parameter SomeInterface throws InvalidArgumentException
        Given di_decorate is called with parameter SomeInterface and SomeOtherClass
        Given SomeOtherClass implements SomeInterface
        Then di_make with parameter SomeInterface returns SomeOtherClass object
        Then di_get with parameter SomeInterface returns SomeOtherClass object
