Feature: earc/di - base functionality - di_decorate, di_is_decorated, di_get_decorator

    Background:
        Given earc-di is bootstrapped

    Scenario: Not decorated
        Given di_decorate is called with parameter SomeInterface and SomeInterface
        Then di_is_decorated with parameter SomeInterface returns false
        Then di_get_decorator with parameter SomeInterface returns null
        Then di_make with parameter SomeInterface throws MakeClassException
        Then di_get with parameter SomeInterface throws MakeClassException

    Scenario: Is decorated
        Given di_decorate is called with parameter SomeInterface and SomeOtherClass
        Then di_is_decorated with parameter SomeInterface returns true
        Then di_get_decorator with parameter SomeInterface returns SomeOtherClass
        Then di_make with parameter SomeInterface returns SomeOtherClass object
        Then di_get with parameter SomeInterface returns SomeOtherClass object

    Scenario: Is decorated by chain
        Given di_decorate is called with parameter SomeInterface and SomeClass
        Given di_decorate is called with parameter SomeClass and SomeOtherClass
        Then di_is_decorated with parameter SomeInterface returns true
        Then di_get_decorator with parameter SomeInterface returns SomeClass
        Then di_make with parameter SomeInterface returns SomeOtherClass object
        Then di_get with parameter SomeInterface returns SomeOtherClass object

    Scenario: Decoration by itself
        Given di_decorate is called with parameter SomeInterface and SomeClass
        Given di_decorate is called with parameter SomeInterface and SomeInterface
        Then di_is_decorated with parameter SomeInterface returns false
        Then di_get_decorator with parameter SomeInterface returns null
        Then di_make with parameter SomeInterface throws MakeClassException
        Then di_get with parameter SomeInterface throws MakeClassException

    Scenario: Is batch decorated
        Given SomeInterface is batch decorated by SomeOtherClass
        Then di_is_decorated with parameter SomeInterface returns true
        Then di_get_decorator with parameter SomeInterface returns SomeOtherClass
        Then di_make with parameter SomeInterface returns SomeOtherClass object
        Then di_get with parameter SomeInterface returns SomeOtherClass object


    Scenario: Is namespace decorated
        Given namespace decoration is initialised
        Then di_is_decorated with parameter project_one\ProjectClass returns true
        Then di_get_decorator with parameter project_one\ProjectClass returns project_two\ProjectClass
        Then di_make with parameter project_one\ProjectClass returns project_two\ProjectClass object
        Then di_get with parameter project_one\ProjectClass returns project_two\ProjectClass object

        Then di_is_decorated with parameter project_two\ProjectClass returns false
        Then di_get_decorator with parameter project_two\ProjectClass returns null
        Then di_make with parameter project_two\ProjectClass returns project_two\ProjectClass object
        Then di_get with parameter project_two\ProjectClass returns project_two\ProjectClass object

        Then di_is_decorated with parameter project_one\SomeClass returns false
        Then di_get_decorator with parameter project_one\SomeClass returns null
        Then di_make with parameter project_one\SomeClass returns project_one\SomeClass object
        Then di_get with parameter project_one\SomeClass returns project_one\SomeClass object

        Then di_is_decorated with parameter project_two\SomeOtherClass returns false
        Then di_get_decorator with parameter project_two\SomeOtherClass returns null
        Then di_make with parameter project_two\SomeOtherClass returns project_two\SomeOtherClass object
        Then di_get with parameter project_two\SomeOtherClass returns project_two\SomeOtherClass object
