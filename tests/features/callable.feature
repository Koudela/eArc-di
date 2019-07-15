Feature: earc/di callable

    Background:
        Given earc-di is bootstrapped
        Given there exists a function callable named A returning string_a
        Given there exists a closure callable named B returning string_b
        Given there exists a static callable named C returning string_c
        Given there exists a object callable named D returning string_d

    Scenario Outline: set/has/get with a callable value
        Given <key> is set with callable <name>
        Then has <key> returns true
        Then get <key> returns string <value>

        Examples:
            | key   | name  | value     |
            | key1  | A     | string_a  |
            | key2  | B     | string_b  |
            | key3  | C     | string_c  |
            | key4  | D     | string_d  |
