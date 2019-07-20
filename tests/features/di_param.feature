Feature: earc/di - base functionality - di_param, di_has_param, di_set_param, di_import_param

    Background:
        Given earc-di is bootstrapped

    Scenario: not set
        Then di_has_param with parameter some_parameter returns false
        Then di_param with parameter some_parameter throws NotFoundException

    Scenario: not set dotted
        Then di_has_param with parameter some.nested.parameter returns false
        Then di_param with parameter some.nested.parameter throws NotFoundException

    Scenario: set
        Given di_set_param with parameter some_parameter and all 42 hero is called
        Then di_has_param with parameter some_parameter returns true
        Then di_param with parameter some_parameter returns all 42 hero

    Scenario: set dotted
        Given di_set_param with parameter some.nested.parameter and all 42 hero is called
        Then di_has_param with parameter some.nested.parameter returns true
        Then di_param with parameter some.nested.parameter returns all 42 hero

    Scenario: set set
        Given di_set_param with parameter some.other.parameter and all 42 hero is called
        Then di_has_param with parameter some.other.parameter returns true
        Then di_set_param with parameter some.other.parameter and all 42 hero throws an InvalidArgumentException

    Scenario: set by import
        Given di_import_param with plain parameter is called
        Then di_has_param with parameter plain_parameter returns true
        Then di_param with parameter plain_parameter returns My name is bunny. I do not know a thing.


    Scenario: set dotted by import
        Given di_import_param with nested parameter is called
        Then di_has_param with parameter this returns true
        Then di_has_param with parameter this.parameter returns true
        Then di_has_param with parameter this.parameter.is returns true
        Then di_has_param with parameter this.parameter.is.nested returns false
        Then di_has_param with parameter parameter.is returns false
        Then di_param with parameter this.parameter.is returns nested

    Scenario: set set by import
        Given di_import_param with plain parameter is called
        Given di_import_param with plain parameter is called
        Then di_has_param with parameter plain_parameter returns true
        Then di_param with parameter plain_parameter returns My name is bunny. I do not know a thing.
