Feature: earc/di - base functionality - di_static

    Background:
        Given earc-di is bootstrapped

    Scenario: Not decorated
        Then di_is_decorated with parameter SomeInterface returns false
        Then di_static with parameter SomeInterface returns SomeInterface

    Scenario: Is decorated
        Given di_decorate is called with parameter SomeInterface and SomeClass
        Then di_is_decorated with parameter SomeInterface returns true
        Then di_static with parameter SomeInterface returns SomeClass

    Scenario: Is decorated by chain
        Given di_decorate is called with parameter SomeInterface and SomeClass
        Given di_decorate is called with parameter SomeClass and SomeOtherClass
        Then di_is_decorated with parameter SomeInterface returns true
        Then di_static with parameter SomeInterface returns SomeOtherClass

    Scenario: Decoration by itself
        Given di_decorate is called with parameter SomeInterface and SomeClass
        Given di_decorate is called with parameter SomeInterface and SomeInterface
        Then di_is_decorated with parameter SomeInterface returns false
        Then di_static with parameter SomeInterface returns SomeInterface
