Feature: earc/di - base functionality - di_tag, di_get_tagged, di_clear_tags

    Background:
        Given earc-di is bootstrapped
        Given di_clear_tags with parameter some.tag and null is called

    Scenario: Not tagged
        Then di_get_tagged with parameter some.tag returns []

    Scenario: One is tagged
        Given di_tag with parameter SomeClass and some.tag is called
        Then di_get_tagged with parameter some.tag returns [SomeClass]

    Scenario: three are tagged
        Given di_tag with parameter SomeClass and some.tag is called
        Given di_tag with parameter NotExistingClass and some.tag is called
        Given di_tag with parameter SomeInterface and some.tag is called
        Then di_get_tagged with parameter some.tag returns [SomeClass, NotExistingClass, SomeInterface]

    Scenario: three are tagged and one is cleared
        Given di_tag with parameter SomeClass and some.tag is called
        Given di_tag with parameter NotExistingClass and some.tag is called
        Given di_tag with parameter SomeInterface and some.tag is called
        Given di_clear_tags with parameter some.tag and NotExistingClass is called
        Then di_get_tagged with parameter some.tag returns [SomeClass, SomeInterface]

    Scenario: three are tagged and all are cleared
        Given di_tag with parameter SomeClass and some.tag is called
        Given di_tag with parameter NotExistingClass and some.tag is called
        Given di_tag with parameter SomeInterface and some.tag is called
        Given di_clear_tags with parameter some.tag and null is called
        Then di_get_tagged with parameter some.tag returns []
