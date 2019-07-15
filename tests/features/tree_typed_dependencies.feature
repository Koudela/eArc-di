Feature: earc/di tree typed dependencies

    Background:

    Scenario: Dependencies are accessible from beneath or beside
        Given earc-di is bootstrapped
        Then dependencies are accessible from beneath
        Given earc-di is bootstrapped
        Then dependencies are accessible from beside

    Scenario: Dependencies are not accessible from above or from another sub-tree
        Given earc-di is bootstrapped
        Then dependencies are not accessible from above
        Given earc-di is bootstrapped
        Then dependencies are not accessible from another sub-tree

    Scenario: Dependencies defined in a near level are preferred over dependencies in a far level
        Given earc-di is bootstrapped
        Then dependencies defined in a near level are preferred over dependencies in a far level
