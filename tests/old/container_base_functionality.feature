Feature: earc/di container base functionality

    Background:
        Given earc-di is bootstrapped

    Scenario: get/has an not set item
        Then has 'itemNotSet' returns false
        Then get 'itemNotSet' throws NotFoundExceptionInterface

    Scenario Outline: set/has/get for string|int|float|bool|null|object values
        Given set <type> item <key> as <value>
        Then has <key> returns <has>
        Then get <key> returns <type> <value>

        Examples:
            | type      | key       | value                         | has   |
            | string    | key1      | stringValue                   | true  |
            | int       | key2      | 1234567                       | true  |
            | float     | key3      | 2.78                          | true  |
            | bool      | key4      | true                          | true  |
            | null      | key5      | arbitraryValue                | true  |
            | object    | key6      | eArc\DITests\env\BasicClass   | true  |
