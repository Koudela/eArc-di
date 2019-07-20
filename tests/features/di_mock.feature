Feature: earc/di - base functionality - di_mock, di_is_mocked, di_clear_mock

    Background:
        Given earc-di is bootstrapped

    Scenario: not mocked
        Then di_is_mocked with parameter SomeClass returns false
        Then di_get with parameter SomeClass returns SomeClass object
        Then di_make with parameter SomeClass returns SomeClass object

    Scenario: is mocked
        Given di_mock with parameter SomeInterface and new SomeClass is called
        Then di_is_mocked with parameter SomeInterface returns true
        Then di_get with parameter SomeInterface returns SomeClass object
        Then di_make with parameter SomeInterface returns SomeClass object

    Scenario: mocked and mocked cleared
        Given di_mock with parameter SomeClass and new SomeOtherClass is called
        Given di_clear_mock with parameter SomeClass is called
        Then di_is_mocked with parameter SomeClass returns false
        Then di_get with parameter SomeClass returns SomeClass object
        Then di_make with parameter SomeClass returns SomeClass object

    Scenario: mocked and all mocked cleared
        Given di_mock with parameter SomeClass and new SomeOtherClass is called
        Given di_clear_mock with parameter null is called
        Then di_is_mocked with parameter SomeClass returns false
        Then di_get with parameter SomeClass returns SomeClass object
        Then di_make with parameter SomeClass returns SomeClass object

    Scenario: is mocked and decorated
        Given di_mock with parameter SomeInterface and new SomeClass is called
        Given di_decorate is called with parameter SomeInterface and SomeOtherClass
        Then di_is_mocked with parameter SomeInterface returns true
        Then di_get with parameter SomeInterface returns SomeOtherClass object
        Then di_make with parameter SomeInterface returns SomeOtherClass object

    Scenario: decorator is mocked
        Given di_mock with parameter SomeOtherClass and new SomeClass is called
        Given di_decorate is called with parameter SomeInterface and SomeOtherClass
        Then di_is_mocked with parameter SomeOtherClass returns true
        Then di_get with parameter SomeInterface returns SomeClass object
        Then di_make with parameter SomeInterface returns SomeClass object
